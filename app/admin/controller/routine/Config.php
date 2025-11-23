<?php

namespace app\admin\controller\routine;

use app\admin\model\Config as ConfigModel;
use app\common\controller\Backend;
use app\common\library\Email;
use extend\ra\SystemUtil;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use support\Response;
use extend\ra\FileUtil;
use Throwable;
use support\cache\Cache;
use support\Log;


class Config extends Backend
{
    /**
     * @var object
     * @phpstan-var ConfigModel
     */
    protected object $model;


    protected array $filePath = [
        'appConfig'           => 'config/app.php',
        'webAdminBase'        => 'web/src/router/static/adminBase.ts',
        'backendEntranceStub' => 'app/admin/library/stubs/backendEntrance.stub',
    ];

    public function initialize():void
    {
        parent::initialize();
        $this->model = new ConfigModel();
    }

    public function index(): Response
    {
        $configGroup = SystemUtil::get_sys_config('config_group');
        $config      = $this->model->order('weigh desc')->select()->toArray();

        $list           = [];
        $newConfigGroup = [];
        
        // 处理配置分组数据，确保是数组格式
        if (!empty($configGroup) && is_string($configGroup)) {
            // 如果是JSON字符串，尝试解析
            $decodedConfig = json_decode($configGroup, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedConfig)) {
                $configGroup = $decodedConfig;
            } else {
                // 如果不是JSON，尝试按逗号分隔解析
                $configGroup = explode(',', $configGroup);
                $configGroup = array_map('trim', $configGroup);
                // 转换为标准格式
                $tempGroup = [];
                foreach ($configGroup as $key => $value) {
                    $tempGroup[] = ['key' => $key, 'value' => $value];
                }
                $configGroup = $tempGroup;
            }
        }
        
        if (is_array($configGroup)) {
            foreach ($configGroup as $item) {
                if (is_array($item) && isset($item['key']) && isset($item['value'])) {
                    $list[$item['key']]['name']   = $item['key'];
                    $list[$item['key']]['title']  = __($item['value']);
                    $newConfigGroup[$item['key']] = $list[$item['key']]['title'];
                }
            }
        }
        foreach ($config as $item) {
            if (array_key_exists($item['group'], $newConfigGroup)) {
                $item['title']                  = __($item['title']);
                $list[$item['group']]['list'][] = $item;
            }
        }

     return $this->success('', [
            'list'          => $list,
            'remark'        => SystemUtil::get_route_remark(),
            'configGroup'   => $newConfigGroup ?? [],
            'quickEntrance' => SystemUtil::get_sys_config('config_quick_entrance'),
        ]);
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): Response
    {
        $all = $this->model->select();
        foreach ($all as $item) {
            if ($item['type'] == 'editor') {
                $this->request->filter('clean_xss');
                break;
            }
        }

        if ($this->request->isPost()) {
            $this->modelValidate = false;
            $data = $this->request->post();
            if (!$data) {
                return $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);

            $configValue = [];
            foreach ($all as $item) {
                if (array_key_exists($item->name, $data)) {
                    $configValue[] = [
                        'id'    => $item->id,
                        'type'  => $item->getData('type'),
                        'value' => $data[$item->name]
                    ];

                    // 自定义后台入口
                    if ($item->name == 'backend_entrance') {
                        $backendEntrance = SystemUtil::get_sys_config('backend_entrance');
                        if ($backendEntrance == $data[$item->name]) continue;

                        // 验证后台入口规则
                        $newEntrance = ltrim($data[$item->name], '/');
                        
                        // 检查是否为空
                        if (empty($newEntrance)) {
                            return $this->error(__('Backend entrance cannot be empty'));
                        }
                        
                        // 检查长度
                        if (strlen($newEntrance) < 3 || strlen($newEntrance) > 20) {
                            return $this->error(__('Backend entrance length must be between 3 and 20 characters'));
                        }
                        
                        // 检查格式
                        if (!preg_match("/^[a-zA-Z][a-zA-Z0-9_-]*$/", $newEntrance)) {
                            return $this->error(__('Backend entrance must start with a letter and can only contain letters, numbers, underscores and hyphens'));
                        }
                        
                        // 检查是否为保留词
                        $reservedWords = ['admin', 'api', 'www', 'mail', 'ftp', 'localhost', 'test', 'dev', 'staging', 'prod', 'production'];
                        if (in_array(strtolower($newEntrance), $reservedWords)) {
                            return $this->error(__('Backend entrance cannot be a reserved word'));
                        }
                        
                        // 检查是否与现有路由冲突
                        $conflictingRoutes = ['user', 'agent', 'install', 'public', 'index'];
                        if (in_array(strtolower($newEntrance), $conflictingRoutes)) {
                            return $this->error(__('Backend entrance conflicts with existing routes'));
                        }

                        // 修改 adminBaseRoutePath
                        $adminBaseFilePath = FileUtil::fsFit(root_path() . $this->filePath['webAdminBase']);
                        $adminBaseContent  = @file_get_contents($adminBaseFilePath);
                        if (!$adminBaseContent) {
                            return $this->error(__('Configuration write failed: %s', [$this->filePath['webAdminBase']]));
                        }

                        $adminBaseContent = str_replace("export const adminBaseRoutePath = '$backendEntrance'", "export const adminBaseRoutePath = '{$data[$item->name]}'", $adminBaseContent);
                        $result = @file_put_contents($adminBaseFilePath, $adminBaseContent);
                        if (!$result) {
                            return $this->error(__('Configuration write failed: %s', [$this->filePath['webAdminBase']]));
                        }

                        // 去除后台入口开头的斜杠
                        $oldBackendEntrance = ltrim($backendEntrance, '/');
                        $newBackendEntrance = ltrim($data[$item->name], '/');

                        // 记录修改日志
                        Log::info("Changing backend entrance from '{$oldBackendEntrance}' to '{$newBackendEntrance}'");

                        // 设置应用别名映射
                        $appMap = config('app.app_map') ?: [];
                        $adminMapKey = array_search('admin', $appMap);
                        if ($adminMapKey !== false) {
                            unset($appMap[$adminMapKey]);
                        }
                        if ($newBackendEntrance != 'admin') {
                            $appMap[$newBackendEntrance] = 'admin';
                        }
                        
                        // 备份原配置文件
                        $appConfigFilePath = FileUtil::fsFit(root_path() . $this->filePath['appConfig']);
                        $backupFilePath = $appConfigFilePath . '.bak.' . date('YmdHis');
                        if (!copy($appConfigFilePath, $backupFilePath)) {
                            Log::warning("Failed to backup app config file to {$backupFilePath}");
                        }
                        
                        $appConfigContent = file_get_contents($appConfigFilePath);
                        if (!$appConfigContent) {
                            return $this->error(__('Configuration read failed: %s', [$this->filePath['appConfig']]));
                        }

                        // 更新应用映射配置
                        $appMapStr = '';
                        foreach ($appMap as $newAppName => $oldAppName) {
                            $appMapStr .= "'$newAppName' => '$oldAppName', ";
                        }
                        $appMapStr = rtrim($appMapStr, ', ');
                        $appMapStr = "[$appMapStr]";

                        // 检查是否已存在 app_map 配置
                        if (strpos($appConfigContent, "'app_map'") !== false) {
                            $appConfigContent = preg_replace("/'app_map'(\s+)=>(\s+)(\[[^\]]*\])/s", "'app_map'\$1=>\$2$appMapStr", $appConfigContent);
                        } else {
                            // 如果没有 app_map 配置，在 request 配置前添加
                            $appConfigContent = preg_replace("/(\s+)(\/\/ request log)/", "\$1'app_map' => {$appMapStr},\n\n\$2", $appConfigContent);
                        }
                        
                        $result = file_put_contents($appConfigFilePath, $appConfigContent);
                        if (!$result) {
                            // 恢复备份
                            if (file_exists($backupFilePath)) {
                                copy($backupFilePath, $appConfigFilePath);
                            }
                            return $this->error(__('Configuration write failed: %s', [$this->filePath['appConfig']]));
                        }

                        // 处理入口文件
                        $oldBackendEntranceFile = FileUtil::fsFit(public_path() . $oldBackendEntrance . '.php');
                        $newBackendEntranceFile = FileUtil::fsFit(public_path() . $newBackendEntrance . '.php');
                        
                        // 删除旧入口文件
                        if (file_exists($oldBackendEntranceFile) && $oldBackendEntrance != 'admin') {
                            if (!unlink($oldBackendEntranceFile)) {
                                Log::warning("Failed to delete old backend entrance file: {$oldBackendEntranceFile}");
                            }
                        }

                        // 创建新入口文件
                        if ($newBackendEntrance != 'admin') {
                            $backendEntranceStub = file_get_contents(FileUtil::fsFit(root_path() . $this->filePath['backendEntranceStub']));
                            if (!$backendEntranceStub) {
                                // 恢复备份
                                if (file_exists($backupFilePath)) {
                                    copy($backupFilePath, $appConfigFilePath);
                                }
                                return $this->error(__('Configuration read failed: %s', [$this->filePath['backendEntranceStub']]));
                            }

                            $result = file_put_contents($newBackendEntranceFile, $backendEntranceStub);
                            if (!$result) {
                                // 恢复备份
                                if (file_exists($backupFilePath)) {
                                    copy($backupFilePath, $appConfigFilePath);
                                }
                                return $this->error(__('Configuration write failed: %s', [$newBackendEntranceFile]));
                            }
                            
                            // 设置文件权限
                            chmod($newBackendEntranceFile, 0644);
                        }
                        
                        Log::info("Backend entrance successfully changed to '{$newBackendEntrance}'");
                    }
                }

            }

            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) {
                            $validate->scene('edit');
                        }
                        $validate->check($data);
                    }
                }
                $result = $this->model->saveAll($configValue);
                
                // 强制更新系统配置缓存
                $this->forceUpdateSystemConfigCache();
                
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            }

            if ($result !== false) {
                return $this->success(__('The current page configuration item was updated successfully'));
            } else {
                return $this->error(__('No rows updated'));
            }
        }else{
            return $this->error('非法请求');
        }
    }

    public function add(): Response
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
             return $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                if (is_array($data['rule'])) {
                    $data['rule'] = implode(',', $data['rule']);
                }

                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
             return $this->error($e->getMessage());
            }
            if ($result !== false) {
             return $this->success(__('Added successfully'));
            } else {
             return $this->error(__('No rows were added'));
            }
        }

     return $this->error(__('Parameter error'));
    }

    /**
     * 发送邮件测试
     * @throws Throwable
     */
    public function sendTestMail(): Response
    {
        $data = $this->request->post();
        $mail = new Email();
        try {
            $mail->Host       = $data['smtp_server'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $data['smtp_user'];
            $mail->Password   = $data['smtp_pass'];
            $mail->SMTPSecure = $data['smtp_verification'] == 'SSL' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $data['smtp_port'];

            $mail->setFrom($data['smtp_sender_mail'], $data['smtp_user']);

            $mail->isSMTP();
            $mail->addAddress($data['testMail']);
            $mail->isHTML();
            $mail->setSubject(__('This is a test email') . '-' . SystemUtil::get_sys_config('site_name'));
            $mail->Body = __('Congratulations, receiving this email means that your email service has been configured correctly');
            $mail->send();
        } catch (PHPMailerException) {
         return $this->error($mail->ErrorInfo);
        }
     return $this->success(__('Test mail sent successfully~'));
    }

    /**
     * 强制更新系统配置缓存
     * 确保配置保存后立即生效
     */
    private function forceUpdateSystemConfigCache(): void
    {
        try {
            // 清理配置缓存 - 不使用标签方式
            // 删除全部配置缓存
            Cache::delete('sys_config_all');
            // 删除分组配置缓存（需要遍历所有分组）
            $groups = $this->model->distinct()->column('group');
            foreach ($groups as $group) {
                Cache::delete('sys_config_group_' . $group);
            }
            // 删除单个配置项缓存（需要遍历所有配置项）
            $configs = $this->model->column('name');
            foreach ($configs as $name) {
                Cache::delete('sys_config_' . $name);
            }
            
            // 重新加载所有配置到缓存
            $allConfigs = $this->model->order('weigh desc')->select()->toArray();
            
            // 按分组缓存配置
            $configGroups = [];
            foreach ($allConfigs as $config) {
                $configGroups[$config['group']][$config['name']] = $config['value'];
                
                // 缓存单个配置项（与SystemUtil::get_sys_config()保持一致）
                Cache::set('sys_config_' . $config['name'], $config['value'], 3600);
            }
            
            // 缓存分组配置（与SystemUtil::get_sys_config()保持一致）
            foreach ($configGroups as $group => $configs) {
                Cache::set('sys_config_group_' . $group, $configs, 3600);
            }
            
            // 缓存完整配置
            Cache::set('sys_config_all', $configGroups, 3600);
            
        } catch (\Throwable $e) {
            // 缓存更新失败不影响主流程，记录日志即可
            error_log('系统配置缓存更新失败: ' . $e->getMessage());
        }
    }
}