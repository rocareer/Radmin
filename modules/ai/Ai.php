<?php

namespace modules\ai;

use Throwable;
use support\cache\Cache;
use app\common\model\Config;
use app\common\library\Menu;

class Ai
{
    /**
     * 安装
     * @throws Throwable
     */
    public function install(): void
    {
        $this->createBackendMenu();
        $this->createFrontendMenu();
    }

    /**
     * 卸载
     * @throws Throwable
     */
    public function uninstall(): void
    {
        Menu::delete('ai', true);
        Menu::delete('ai/chat', true, 'frontend');
    }

    /**
     * 启用
     * @throws Throwable
     */
    public function enable(): void
    {
        Menu::enable('ai');
        Menu::enable('ai/chat', 'frontend');

        Config::addQuickEntrance('知识库配置', 'ai/config');
        Config::addQuickEntrance('AI对话模型配置', 'ai/chatModel');

        $config = Cache::pull('ai-module-config');
        if ($config) {
            @file_put_contents(config_path() . 'ai.php', $config);
        }
    }

    /**
     * 禁用
     * @throws Throwable
     */
    public function disable(): void
    {
        Menu::disable('ai');
        Menu::disable('ai/chat', 'frontend');

        Config::removeQuickEntrance('知识库配置');
        Config::removeQuickEntrance('AI对话模型配置');

        $config = @file_get_contents(config_path() . 'ai.php');
        if ($config) {
            Cache::set('ai-module-config', $config, 3600);
        }
    }

    /**
     * 创建后台菜单
     * @throws Throwable
     */
    public function createBackendMenu(): void
    {
        $menu = [
            [
                'type'      => 'menu_dir',
                'title'     => 'AI管理',
                'name'      => 'ai',
                'path'      => 'ai',
                'icon'      => 'fa fa-dropbox',
                'pid'       => 0,
                'keepalive' => '1',
                'children'  => [
                    [
                        'type'      => 'menu',
                        'title'     => '对话调试',
                        'name'      => 'ai/chat',
                        'path'      => 'ai/chat',
                        'icon'      => 'fa fa-twitch',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/chat/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 99,
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '向量搜索',
                        'name'      => 'ai/search',
                        'path'      => 'ai/search',
                        'icon'      => 'fa fa-search',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/search/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 98,
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '知识库配置',
                        'name'      => 'ai/config',
                        'path'      => 'ai/config',
                        'icon'      => 'fa fa-gear',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/config/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 97,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/config/index'],
                            ['type' => 'button', 'title' => '保存', 'name' => 'ai/config/save'],
                            ['type' => 'button', 'title' => '清理redis缓存和索引', 'name' => 'ai/config/clear'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '知识点管理',
                        'name'      => 'ai/kbsContent',
                        'path'      => 'ai/kbsContent',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/kbsContent/index.vue',
                        'weigh'     => 96,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/kbsContent/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'ai/kbsContent/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'ai/kbsContent/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'ai/kbsContent/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'ai/kbsContent/sortable'],
                            ['type' => 'button', 'title' => '重建索引', 'name' => 'ai/kbsContent/indexSet'],
                            ['type' => 'button', 'title' => '重建缓存', 'name' => 'ai/kbsContent/checkCache'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '知识库管理',
                        'name'      => 'ai/kbs',
                        'path'      => 'ai/kbs',
                        'icon'      => 'fa fa-square-o',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/kbs/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 95,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/kbs/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'ai/kbs/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'ai/kbs/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'ai/kbs/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'ai/kbs/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '对话模型管理',
                        'name'      => 'ai/chatModel',
                        'path'      => 'ai/chatModel',
                        'icon'      => 'fa fa-connectdevelop',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/chatModel/indexSuspense.vue',
                        'keepalive' => '1',
                        'weigh'     => 94,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/chatModel/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'ai/chatModel/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'ai/chatModel/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'ai/chatModel/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'ai/chatModel/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '会话管理',
                        'name'      => 'ai/session',
                        'path'      => 'ai/session',
                        'icon'      => 'fa fa-commenting-o',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/session/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 93,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/session/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'ai/session/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'ai/session/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'ai/session/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'ai/session/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '会话消息管理',
                        'name'      => 'ai/sessionMessage',
                        'path'      => 'ai/sessionMessage',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/sessionMessage/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 92,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/sessionMessage/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'ai/sessionMessage/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'ai/sessionMessage/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'ai/sessionMessage/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'ai/sessionMessage/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => 'AI会员管理',
                        'name'      => 'ai/aiUser',
                        'path'      => 'ai/aiUser',
                        'icon'      => 'fa fa-user-o',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/aiUser/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 91,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/aiUser/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'ai/aiUser/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'ai/aiUser/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'ai/aiUser/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'ai/aiUser/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => 'AI会员tokens记录',
                        'name'      => 'ai/userTokens',
                        'path'      => 'ai/userTokens',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/ai/userTokens/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 90,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'ai/userTokens/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'ai/userTokens/add'],
                        ],
                    ],
                ],
            ]
        ];
        Menu::create($menu);
    }

    /**
     * 创建前台菜单
     * @throws Throwable
     */
    public function createFrontendMenu(): void
    {
        $menu = [
            [
                'type'           => 'nav',
                'title'          => '知识库',
                'name'           => 'ai/chat',
                'path'           => 'chat',
                'icon'           => 'fa fa-twitch',
                'menu_type'      => 'tab',
                'no_login_valid' => '1',
                'component'      => '',
            ]
        ];
        Menu::create($menu, 0, 'cover', 'frontend');
    }
}