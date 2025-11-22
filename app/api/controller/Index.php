<?php


namespace app\api\controller;

use app\common\controller\Frontend;
use extend\ba\Tree;
use extend\ra\SystemUtil;
use support\orm\Db;
use support\member\Member;
use support\Response;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Throwable;
use Webman\Event\Event;

class Index extends Frontend
{
    protected array $noNeedLogin = ['index'];

    public function initialize(): void
    {
        parent::initialize();
    }


    /**
     * 初始化接口
     * @return   Response|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/11/21 17:51
     */
    public function index(): ?Response
    {
        // 尝试初始化用户信息（仅在api/index/index中检测）
        $this->tryInitializeMember();
        // 检查是否需要登录
        $requiredLogin = $this->request->input('requiredLogin', false);
        if ($requiredLogin && empty($this->member)) {
            return $this->error(__('Please login first'), [
                'type' => 'need login',
            ], 303);
        }

        // 获取用户菜单和规则
        list($menus, $rules) = $this->getUserMenusAndRules();

        // 构建响应数据
        $data = $this->buildResponseData($menus, $rules);

        return $this->success('Init', $data);
    }


    /**
     * 获取用户菜单和规则
     * @return   array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/11/21 17:51
     */
    protected function getUserMenusAndRules(): array
    {
        $menus = [];
        $rules = [];
        if (!empty($this->member)) {
            try {
                // 登录用户：获取用户菜单并过滤

                $userMenus = Member::getMenus($this->member->id);
                foreach ($userMenus as $item) {
                    if (in_array($item['type'], ['menu_dir', 'menu'])) {
                        // 菜单目录和菜单项都作为菜单返回
                        $menus[] = $item;
                    } else {
                        // 其他类型作为规则返回
                        $rules[] = $item;
                    }
                }
            } catch (\Throwable $e) {
                // 如果获取菜单失败，记录错误日志并返回空菜单
                Event::emit('api.menu.get.error', [
                    'uid' => $this->member->id,
                    'error' => $e->getMessage(),
                    'role' => $this->member->role ?? 'user'
                ]);
                
                // 返回空菜单，但确保用户信息正常返回
                $menus = [];
                $rules = [];
            }
        } else {
            // 未登录用户：获取公共规则
            $rules = Db::name('user_rule')
                ->where('status', '1')
                ->where('no_login_valid', 1)
                ->where('type', 'in', ['route', 'nav', 'button'])
                ->order('weigh', 'desc')
                ->select()
                ->toArray();

            $rules = Tree::instance()->assembleChild($rules);
        }

        return [$menus, array_values($rules)];
    }

    /**
     * 构建响应数据
     * @param array $menus
     * @param array $rules
     * @return   array
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/11/21 17:51
     */
    protected function buildResponseData(array $menus, array $rules): array
    {
        $uploadConfig = get_upload_config();
        
        return [
            'site' => [
                'siteName'     => SystemUtil::get_sys_config('site_name'),
                'recordNumber' => SystemUtil::get_sys_config('record_number'),
                'version'      => SystemUtil::get_sys_config('version'),
                'cdnUrl'       => full_url(),
                'upload'       => keys_to_camel_case($uploadConfig, [
                    'max_size',
                    'save_name',
                    'allowed_suffixes',
                    'allowed_mime_types',
                ]),
            ],
            'openMemberCenter' => config('buildadmin.open_member_center'),
            'userInfo'         => $this->member,
            'menus'            => $menus,
            'rules'            => $rules,
        ];
    }
}