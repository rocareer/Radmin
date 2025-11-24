<?php

namespace modules\interaction;

use support\cache\Cache;
use Throwable;
use app\common\model\Config;
use app\common\library\Menu;
use app\admin\model\AdminRule;
use app\admin\library\module\Server;

class Interaction
{
    private string $uid = 'interaction';

    /**
     * 安装
     * @throws Throwable
     */
    public function install(): void
    {
        // 向后台会员规则添加
        $pMenu = AdminRule::where('name', 'user')->value('id');
        $menu  = [
            [
                'type'      => 'menu',
                'title'     => '会员消息管理',
                'name'      => 'user/message',
                'path'      => 'user/message',
                'icon'      => 'fa fa-commenting-o',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/user/message/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu ? $pMenu : 0,
                'weigh'     => 10,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'user/message/index'],
                    ['type' => 'button', 'title' => '添加', 'name' => 'user/message/add'],
                    ['type' => 'button', 'title' => '编辑', 'name' => 'user/message/edit'],
                    ['type' => 'button', 'title' => '删除', 'name' => 'user/message/del'],
                    ['type' => 'button', 'title' => '快速排序', 'name' => 'user/message/sortable'],
                ],
            ],
            [
                'type'      => 'menu',
                'title'     => '会员名片组件管理',
                'name'      => 'user/cardComponent',
                'path'      => 'user/cardComponent',
                'icon'      => 'fa fa-drivers-license-o',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/user/cardComponent/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu ? $pMenu : 0,
                'weigh'     => 9,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'user/cardComponent/index'],
                    ['type' => 'button', 'title' => '添加', 'name' => 'user/cardComponent/add'],
                    ['type' => 'button', 'title' => '编辑', 'name' => 'user/cardComponent/edit'],
                    ['type' => 'button', 'title' => '删除', 'name' => 'user/cardComponent/del'],
                    ['type' => 'button', 'title' => '快速排序', 'name' => 'user/cardComponent/sortable'],
                ],
            ],
            [
                'type'      => 'menu',
                'title'     => '会员最近动态管理',
                'name'      => 'user/recent',
                'path'      => 'user/recent',
                'icon'      => 'fa fa-podcast',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/user/recent/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu ? $pMenu : 0,
                'weigh'     => 8,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'user/recent/index'],
                    ['type' => 'button', 'title' => '添加', 'name' => 'user/recent/add'],
                    ['type' => 'button', 'title' => '编辑', 'name' => 'user/recent/edit'],
                    ['type' => 'button', 'title' => '删除', 'name' => 'user/recent/del'],
                    ['type' => 'button', 'title' => '快速排序', 'name' => 'user/recent/sortable'],
                ],
            ]
        ];
        Menu::create($menu);

        // 向前台菜单规则添加菜单
        $menu = [
            [
                'pid'            => 0,
                'type'           => 'nav_user_menu',
                'title'          => '消息中心',
                'name'           => 'messageCenter',
                'path'           => 'user/messageCenter',
                'icon'           => 'el-icon-Minus',
                'menu_type'      => 'tab',
                'no_login_valid' => '0',
                'component'      => '',
            ],
            [
                'pid'            => 0,
                'type'           => 'route',
                'title'          => '会员名片',
                'name'           => 'user/card',
                'path'           => 'user/card/:id',
                'icon'           => 'el-icon-Minus',
                'menu_type'      => 'tab',
                'no_login_valid' => '1',
                'component'      => '',
            ]
        ];
        Menu::create($menu, 0, 'cover', 'frontend');
    }

    /**
     * 卸载
     * @throws Throwable
     */
    public function uninstall(): void
    {
        Menu::delete('user/message', true);
        Menu::delete('user/cardComponent', true);
        Menu::delete('user/recent', true);
        Menu::delete('messageCenter', true, 'frontend');
        Menu::delete('user/card', true, 'frontend');
    }

    /**
     * 启用
     * @throws Throwable
     */
    public function enable(): void
    {
        Menu::enable('user/message');
        Menu::enable('user/cardComponent');
        Menu::enable('user/recent');
        Menu::enable('messageCenter', 'frontend');
        Menu::enable('user/card', 'frontend');

        Config::addConfigGroup('user', '会员配置');

        if (!Config::where('name', 'official_account')->value('id')) {
            // 配置数据曾在禁用时被删除
            Server::importSql(root_path() . 'modules' . DIRECTORY_SEPARATOR . $this->uid . DIRECTORY_SEPARATOR);
        }

        // 恢复缓存中的配置数据
        $config = Cache::pull($this->uid . '-module-config');
        if ($config) {
            $config = json_decode($config, true);
            foreach ($config as $item) {
                Config::where('name', $item['name'])->update([
                    'value' => $item['value']
                ]);
            }
        }
    }

    /**
     * 禁用
     * @throws Throwable
     */
    public function disable(): void
    {
        Menu::disable('user/message');
        Menu::disable('user/cardComponent');
        Menu::disable('user/recent');
        Menu::disable('messageCenter', 'frontend');
        Menu::disable('user/card', 'frontend');

        $config = Config::whereIn('name', ['official_account', 'system_message_account', 'polling_interval'])->select();

        // 备份配置到缓存
        if (!$config->isEmpty()) {
            $configData = $config->toArray();
            Cache::set($this->uid . '-module-config', json_encode($configData), 3600);
        }

        foreach ($config as $item) {
            $item->delete();
        }
        Config::removeConfigGroup('user');
    }
}