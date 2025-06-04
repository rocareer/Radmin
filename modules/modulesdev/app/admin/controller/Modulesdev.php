<?php

namespace app\admin\controller;

use extend\ra\FileUtil;
use support\member\Member;
use support\Response;
use Throwable;
use app\common\controller\Backend;
use app\admin\library\module\Server;
use modules\modulesdev\library\Helper;

/**
 * 模块开发管理
 */
class Modulesdev extends Backend
{
    protected array $noNeedPermission = ['fileChange', 'delModuleFile', 'moduleBackup', 'modulePack'];

    protected string $modulesDir;

    protected string $backupsDir;

    public function initialize(): void
    {
        $this->modulesDir = root_path() . 'modules' . DIRECTORY_SEPARATOR;
        $this->backupsDir = runtime_path() . '/backups' . DIRECTORY_SEPARATOR;

        if (!is_dir($this->backupsDir)) {
            mkdir($this->backupsDir, 0755, true);
        }

        // 清理下载模块包时生成的临时文件
        $tempDir = FileUtil::fsFit(public_path() . "/storage/modules-temp/");
        FileUtil::delDir($tempDir);

        parent::initialize();
    }


    /**
     * 查看
     * @throws Throwable
     */
    public function index(): ?Response
    {
        $quickSearch = $this->request->input("quickSearch/s", '');
        $modules     = Server::installedList($this->modulesDir);
        foreach ($modules as $key => $module) {
            if ($quickSearch && !str_contains($module['title'], $quickSearch) && !str_contains($module['uid'], $quickSearch)) {
                unset($modules[$key]);
                continue;
            }
            $pure                          = Server::getRuntime($this->modulesDir . $module['uid'] . DIRECTORY_SEPARATOR, 'pure');
            $modules[$key]['install_mode'] = $pure ? 'pure' : 'full';
        }

        return $this->success('', [
            'list' => array_values($modules),
        ]);
    }

    public function dir(): Response
    {
        $res  = [];
        $uid  = $this->request->input('uid', '');
        $path = $this->request->input('path', '');
        $type = $this->request->input('type', 'root');

        if (!$uid) {
            return $this->error('模块找不到啦！');
        }

        $modulesDir = $this->modulesDir . $uid . DIRECTORY_SEPARATOR;

        if (!$path) {
            if ($type == 'root') {
                // 根目录过滤一些文件
                $unsetLabel = [
                    '.env',
                    '.env-example',
                    '.git',
                    '.gitattributes',
                    '.gitignore',
                    'CHANGELOG.md',
                    'LICENSE',
                    'README.md',
                    'composer.json',
                    'composer.lock',
                    'think',
                ];
                $data       = Helper::dir(root_path());
                foreach ($data as $key => $datum) {
                    if (in_array($datum['label'], $unsetLabel)) {
                        unset($data[$key]);
                    }
                }
                $data = array_values($data);

                $backupDir          = $this->backupsDir . $uid;
                $res['backupDir']   = str_replace(root_path(), DIRECTORY_SEPARATOR, $backupDir);
                $res['needBackup']  = FileUtil::dirIsEmpty($backupDir);
                $res['moduleFiles'] = Helper::dirFiles($modulesDir);
                foreach ($res['moduleFiles'] as &$moduleFile) {
                    $moduleFile = root_path() . str_replace($modulesDir, '', $moduleFile);
                }
            } else {
                $data = Helper::dir($modulesDir);
            }
        } else {
            $path .= DIRECTORY_SEPARATOR;
            $data = Helper::dir($path);
        }

        $res['data'] = $data;
        return $this->success('', $res);
    }

    public function fileChange(): Response
    {
        if (!Member::check('modulesdev/dir')) {
            $this->error(__('You have no permission'), [], 401);
        }
        $path    = $this->request->input('path', '');
        $uid     = $this->request->input('uid', '');
        $checked = $this->request->input('checked/b', true);

        if (!$uid) {
            return $this->error('模块找不到啦！');
        }
        if (!file_exists($path)) {
            return $this->error('路径找不到了，请刷新重试！');
        }

        $path      = str_replace(root_path(), '', FileUtil::fsFit($path));
        $moduleDir = $this->modulesDir . $uid . DIRECTORY_SEPARATOR;

        $modulePath = $moduleDir . $path;
        $rootPath   = root_path() . $path;
        if (file_exists($modulePath) && !$checked) {
            // 从模块目录去除
            if (Helper::delModuleFileOrDir($modulePath)) {
                return $this->success();
            } else {
                return $this->error('删除失败，请重试！');
            }
        }

        if ($checked) {
            // 添加到模块目录
            if (is_file($rootPath)) {
                if (!is_dir(dirname($modulePath))) {
                    mkdir(dirname($modulePath), 0755, true);
                }
                copy($rootPath, $modulePath);
            } else {
                $dirFiles = Helper::dirFiles($rootPath);
                foreach ($dirFiles as $dirFile) {
                    $moduleDirFile = str_replace(root_path(), $moduleDir, $dirFile);
                    if (!is_dir(dirname($moduleDirFile))) {
                        mkdir(dirname($moduleDirFile), 0755, true);
                    }
                    copy($dirFile, $moduleDirFile);
                }
            }
        }
        return $this->success();
    }

    public function delModuleFile(): Response
    {
        if (!Member::check('modulesdev/dir')) {
            return $this->error(__('You have no permission'), [], 401);
        }
        $path = $this->request->input('path', '');
        if (Helper::delModuleFileOrDir($path)) {
            return $this->success();
        } else {
            return $this->error('删除失败，请重试！');
        }
    }

    public function moduleBackup(): Response
    {
        if (!Member::check('modulesdev/dir')) {
            $this->error(__('You have no permission'), [], 401);
        }
        $uid = $this->request->input('uid', '');

        if (!$uid) {
            return $this->error('模块找不到啦！');
        }

        $backupDir = $this->backupsDir . $uid . DIRECTORY_SEPARATOR;
        $moduleDir = $this->modulesDir . $uid . DIRECTORY_SEPARATOR;
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        if (Helper::pack($moduleDir, $backupDir . date('Y-m-d-H-i-s') . '.zip')) {
            return $this->success('模块文件备份成功~');
        } else {
            return $this->error('模块文件备份失败，请重试！');
        }
    }

    public function modulePack(): Response
    {
        if (!Member::check('modulesdev/dir')) {
            return $this->error(__('You have no permission'), [], 401);
        }
        $uid = $this->request->input('uid', '');
        if (!$uid) {
            return $this->error('模块找不到啦！');
        }

        $moduleDir  = $this->modulesDir . $uid . DIRECTORY_SEPARATOR;
        $moduleInfo = Server::getIni($moduleDir);
        $version    = $moduleInfo && !empty($moduleInfo['version']) ? $moduleInfo['version'] : '';
        if (!$version) {
            return $this->error('模块基本信息不完整');
        }

        $saveDir = FileUtil::fsFit(public_path() . "/storage/modules-temp/");
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $saveFile = "$saveDir$uid-v$version.zip";

        if (Helper::pack($moduleDir, $saveFile)) {
            $url = str_replace(public_path(), '', $saveFile);
            $url='//' . request()->host().$url;
            return $this->success('', [
                'url' =>$url,
            ]);
        } else {
            return $this->error('模块文件备份失败，请重试！');
        }
    }

    public function manage(): mixed
    {
        $data = $this->request->post();
        if (!$data['opt'] || !$data['uid']) {
            return $this->error('未知操作');
        }

        if ($data['opt'] == 'install_mode') {
           return $this->installModeChange();
        } elseif ($data['opt'] == 'install_sql') {
            Server::importSql($this->modulesDir . $data['uid'] . DIRECTORY_SEPARATOR);
            return $this->success();
        } elseif ($data['opt'] == 'exec_function') {
            if (!in_array($data['fun'], ['install', 'uninstall', 'enable', 'disable', 'update'])) {
                return $this->error('请选择正确的调用方法！');
            }
            Server::execEvent($data['uid'], $data['fun']);
            return $this->success();
        }
        return $this->error('未知操作');
    }

    /**
     * 安装模式切换
     */
    public function installModeChange(): ?Response
    {
        $data            = $this->request->post();
        $moduleDir       = $this->modulesDir . $data['uid'] . DIRECTORY_SEPARATOR;
        $runtime         = Server::getRuntime($moduleDir);
        $pure            = isset($runtime['pure']) && $runtime['pure'];
        $runtimeFilePath = $moduleDir . '.runtime';
        if ($pure && $data['mode'] == 'full') {
            // 纯净模式转完整，必然有.runtime文件
            $moduleFile = Server::getFileList($moduleDir);
            foreach ($moduleFile as $file) {
                $moduleFilePath = FileUtil::fsFit($moduleDir . $file);
                $file           = FileUtil::fsFit(root_path() . $file);
                if (!file_exists($file)) continue;
                if (!file_exists($moduleFilePath)) {
                    if (!is_dir(dirname($moduleFilePath))) {
                        mkdir(dirname($moduleFilePath), 0755, true);
                    }
                    copy($file, $moduleFilePath);
                }
            }

            $runtime['pure'] = false;
            file_put_contents($runtimeFilePath, json_encode($runtime));
            return $this->success();
        } elseif (!$pure && $data['mode'] == 'pure') {
            // 完整模式转纯净，不一定有.runtime文件
            if (!is_file($runtimeFilePath) || !$runtime) {
                Server::createRuntime($moduleDir);
                $runtime = Server::getRuntime($moduleDir);
            }
            $overwriteDir = Server::getOverwriteDir();
            foreach ($overwriteDir as $dirItem) {
                $baseDir = $moduleDir . $dirItem;
                if (!is_dir($baseDir)) {
                    continue;
                }
                FileUtil::delDir($baseDir);
            }


            $runtime['pure'] = true;
            file_put_contents($runtimeFilePath, json_encode($runtime));
            return $this->success();
        }

        return $this->error('参数异常，请刷新重试！');
    }
}