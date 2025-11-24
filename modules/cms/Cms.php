<?php

namespace modules\cms;

use Throwable;
use app\common\model\Config;
use app\common\library\Menu;
use app\admin\model\UserRule;

class Cms
{
    /**
     * 安装
     * @throws Throwable
     */
    public function install(): void
    {
        self::createBackendMenu();
        self::createFrontendMenu();
    }

    /**
     * 卸载
     * @throws Throwable
     */
    public function uninstall(): void
    {
        Menu::delete('cms', true);
        Menu::delete('cms', true, 'frontend');
        Menu::delete('cmsNews', true, 'frontend');
        Menu::delete('cmsDownloads', true, 'frontend');
        Menu::delete('cmsProducts', true, 'frontend');
        Menu::delete('cmsSearch', true, 'frontend');
    }

    /**
     * 启用
     * @throws Throwable
     */
    public function enable(): void
    {
        Menu::enable('cms');
        Menu::enable('cms', 'frontend');
        Menu::enable('cmsNews', 'frontend');
        Menu::enable('cmsDownloads', 'frontend');
        Menu::enable('cmsProducts', 'frontend');
        Menu::enable('cmsSearch', 'frontend');

        Config::addQuickEntrance('CMS配置', 'cms/config');
    }

    /**
     * 禁用
     * @throws Throwable
     */
    public function disable(): void
    {
        Menu::disable('cms');
        Menu::disable('cms', 'frontend');
        Menu::disable('cmsNews', 'frontend');
        Menu::disable('cmsDownloads', 'frontend');
        Menu::disable('cmsProducts', 'frontend');
        Menu::disable('cmsSearch', 'frontend');

        Config::removeQuickEntrance('CMS配置');
    }

    public function update(): void
    {
        UserRule::where('name', 'cms/collect')->update([
            'name'  => 'cms/operateRecord',
            'path'  => 'cms/collect',
            'weigh' => 5,
        ]);
    }

    /**
     * 创建后台菜单
     * @throws Throwable
     */
    public static function createBackendMenu(): void
    {
        $menu = [
            [
                'type'      => 'menu_dir',
                'title'     => 'CMS管理',
                'name'      => 'cms',
                'path'      => 'cms',
                'icon'      => 'fa fa-newspaper-o',
                'menu_type' => 'tab',
                'component' => '',
                'keepalive' => '1',
                'pid'       => 0,
                'weigh'     => 0,
                'children'  => [
                    [
                        'type'      => 'menu_dir',
                        'title'     => '内容管理',
                        'name'      => 'cms/content',
                        'path'      => 'cms/content',
                        'icon'      => 'fa fa-file-text-o',
                        'menu_type' => 'tab',
                        'component' => '',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            // 模型规则-s
                            [
                                'type'      => 'menu',
                                'title'     => '新闻管理',
                                'name'      => 'cms/content/cms_news',
                                'path'      => 'cms/content/cms_news',
                                'icon'      => 'fa fa-circle-o',
                                'menu_type' => 'tab',
                                'component' => '/src/views/backend/cms/content/index.vue',
                                'keepalive' => '1',
                                'weigh'     => 1,
                                'children'  => [
                                    ['type' => 'button', 'title' => '查看', 'name' => 'cms/content/cms_news/index'],
                                    ['type' => 'button', 'title' => '添加', 'name' => 'cms/content/cms_news/add'],
                                    ['type' => 'button', 'title' => '编辑', 'name' => 'cms/content/cms_news/edit'],
                                    ['type' => 'button', 'title' => '删除', 'name' => 'cms/content/cms_news/del'],
                                ],
                            ],
                            [
                                'type'      => 'menu',
                                'title'     => '产品管理',
                                'name'      => 'cms/content/cms_products',
                                'path'      => 'cms/content/cms_products',
                                'icon'      => 'fa fa-circle-o',
                                'menu_type' => 'tab',
                                'component' => '/src/views/backend/cms/content/index.vue',
                                'keepalive' => '1',
                                'weigh'     => 1,
                                'children'  => [
                                    ['type' => 'button', 'title' => '查看', 'name' => 'cms/content/cms_products/index'],
                                    ['type' => 'button', 'title' => '添加', 'name' => 'cms/content/cms_products/add'],
                                    ['type' => 'button', 'title' => '编辑', 'name' => 'cms/content/cms_products/edit'],
                                    ['type' => 'button', 'title' => '删除', 'name' => 'cms/content/cms_products/del'],
                                ],
                            ],
                            [
                                'type'      => 'menu',
                                'title'     => '下载管理',
                                'name'      => 'cms/content/cms_downloads',
                                'path'      => 'cms/content/cms_downloads',
                                'icon'      => 'fa fa-circle-o',
                                'menu_type' => 'tab',
                                'component' => '/src/views/backend/cms/content/index.vue',
                                'keepalive' => '1',
                                'weigh'     => 1,
                                'children'  => [
                                    ['type' => 'button', 'title' => '查看', 'name' => 'cms/content/cms_downloads/index'],
                                    ['type' => 'button', 'title' => '添加', 'name' => 'cms/content/cms_downloads/add'],
                                    ['type' => 'button', 'title' => '编辑', 'name' => 'cms/content/cms_downloads/edit'],
                                    ['type' => 'button', 'title' => '删除', 'name' => 'cms/content/cms_downloads/del'],
                                ],
                            ],
                            // 模型规则-e
                            [
                                'type'      => 'menu',
                                'title'     => '内容模型管理',
                                'name'      => 'cms/contentModel',
                                'path'      => 'cms/contentModel',
                                'icon'      => 'fa fa-cubes',
                                'menu_type' => 'tab',
                                'component' => '/src/views/backend/cms/contentModel/index.vue',
                                'keepalive' => '1',
                                'weigh'     => 0,
                                'children'  => [
                                    ['type' => 'button', 'title' => '查看', 'name' => 'cms/contentModel/index'],
                                    ['type' => 'button', 'title' => '添加', 'name' => 'cms/contentModel/add'],
                                    ['type' => 'button', 'title' => '编辑', 'name' => 'cms/contentModel/edit'],
                                    ['type' => 'button', 'title' => '删除', 'name' => 'cms/contentModel/del'],
                                ],
                            ],
                            [
                                'type'      => 'menu',
                                'title'     => '内容模型字段管理',
                                'name'      => 'cms/contentModelFieldConfig',
                                'path'      => 'cms/contentModelFieldConfig',
                                'icon'      => 'fa fa-cube',
                                'menu_type' => 'tab',
                                'component' => '/src/views/backend/cms/contentModelFieldConfig/index.vue',
                                'keepalive' => '1',
                                'weigh'     => -1,
                                'remark'    => '一个模型对应一个数据表，然后您可以在此管理该表的字段，同时可配置对应模型的管理功能本身',
                                'children'  => [
                                    ['type' => 'button', 'title' => '查看', 'name' => 'cms/contentModelFieldConfig/index'],
                                    ['type' => 'button', 'title' => '添加', 'name' => 'cms/contentModelFieldConfig/add'],
                                    ['type' => 'button', 'title' => '编辑', 'name' => 'cms/contentModelFieldConfig/edit'],
                                    ['type' => 'button', 'title' => '删除', 'name' => 'cms/contentModelFieldConfig/del'],
                                    ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/contentModelFieldConfig/sortable'],
                                ],
                            ]
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '标签管理',
                        'name'      => 'cms/tags',
                        'path'      => 'cms/tags',
                        'icon'      => 'fa fa-tags',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/tags/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/tags/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'cms/tags/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'cms/tags/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'cms/tags/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/tags/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '频道管理',
                        'name'      => 'cms/channel',
                        'path'      => 'cms/channel',
                        'icon'      => 'fa fa-th-large',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/channel/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/channel/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'cms/channel/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'cms/channel/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'cms/channel/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/channel/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '区块管理',
                        'name'      => 'cms/block',
                        'path'      => 'cms/block',
                        'icon'      => 'fa fa-columns',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/block/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/block/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'cms/block/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'cms/block/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'cms/block/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/block/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => 'CMS配置',
                        'name'      => 'cms/config',
                        'path'      => 'cms/config',
                        'icon'      => 'fa fa-gear',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/config/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/config/index'],
                            ['type' => 'button', 'title' => '保存', 'name' => 'cms/config/save'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '友情链接',
                        'name'      => 'cms/friendlyLink',
                        'path'      => 'cms/friendlyLink',
                        'icon'      => 'fa fa-unlink',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/friendlyLink/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/friendlyLink/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'cms/friendlyLink/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'cms/friendlyLink/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'cms/friendlyLink/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/friendlyLink/sortable'],
                        ],
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '评论管理',
                        'name'      => 'cms/comment',
                        'path'      => 'cms/comment',
                        'icon'      => 'fa fa-comment-o',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/comment/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/comment/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'cms/comment/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'cms/comment/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'cms/comment/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/comment/sortable'],
                        ]
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '用户支付记录',
                        'name'      => 'cms/payLog',
                        'path'      => 'cms/payLog',
                        'icon'      => 'fa fa-file-text-o',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/payLog/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/payLog/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'cms/payLog/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'cms/payLog/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'cms/payLog/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/payLog/sortable'],
                        ]
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '用户搜索记录',
                        'name'      => 'cms/searchLog',
                        'path'      => 'cms/searchLog',
                        'icon'      => 'fa fa-file-text-o',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/searchLog/index.vue',
                        'keepalive' => '1',
                        'weigh'     => 0,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => 'cms/searchLog/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => 'cms/searchLog/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => 'cms/searchLog/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => 'cms/searchLog/del'],
                            ['type' => 'button', 'title' => '快速排序', 'name' => 'cms/searchLog/sortable'],
                        ]
                    ]
                ],
            ]
        ];
        Menu::create($menu);
    }

    /**
     * 创建前台菜单
     * @throws Throwable
     */
    public static function createFrontendMenu(): void
    {
        $menu = [
            [
                'type'      => 'menu_dir',
                'title'     => '内容管理',
                'name'      => 'cms',
                'path'      => 'cms',
                'icon'      => 'el-icon-Minus',
                'menu_type' => 'tab',
                'component' => '',
                'children'  => [
                    [
                        'type'      => 'menu',
                        'title'     => '我的收藏',
                        'name'      => 'cms/operateRecord',
                        'path'      => 'cms/collect',
                        'icon'      => 'fa fa-star',
                        'menu_type' => 'tab',
                        'weigh'     => 5,
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '我的评论',
                        'name'      => 'cms/comment',
                        'path'      => 'cms/comment',
                        'icon'      => 'fa fa-commenting',
                        'menu_type' => 'tab',
                        'weigh'     => 8,
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '我的内容',
                        'name'      => 'cms/content',
                        'path'      => 'cms/content',
                        'icon'      => 'fa fa-file',
                        'menu_type' => 'tab',
                        'weigh'     => 7,
                    ],
                    [
                        'type'      => 'menu',
                        'title'     => '我的订单',
                        'name'      => 'cms/order',
                        'path'      => 'cms/order',
                        'icon'      => 'fa fa-file-text',
                        'menu_type' => 'tab',
                        'weigh'     => 6,
                    ],
                ],
            ],
            [
                'type'           => 'nav',
                'title'          => '新闻中心',
                'name'           => 'cmsNews',
                'path'           => 'cms/channel/1',
                'icon'           => 'el-icon-Minus',
                'menu_type'      => 'tab',
                'no_login_valid' => '1',
                'component'      => '',
                'children'       => [
                    [
                        'type'           => 'nav',
                        'title'          => '人工智能',
                        'name'           => 'cmsChannel2',
                        'path'           => 'cms/channel/2',
                        'icon'           => 'el-icon-Minus',
                        'menu_type'      => 'tab',
                        'no_login_valid' => '1',
                        'component'      => '',
                    ],
                    [
                        'type'           => 'nav',
                        'title'          => '开源安全',
                        'name'           => 'cmsChannel3',
                        'path'           => 'cms/channel/3',
                        'icon'           => 'el-icon-Minus',
                        'menu_type'      => 'tab',
                        'no_login_valid' => '1',
                        'component'      => '',
                    ],
                    [
                        'type'           => 'nav',
                        'title'          => '官方动态',
                        'name'           => 'cmsChannel5',
                        'path'           => 'cms/channel/5',
                        'icon'           => 'el-icon-Minus',
                        'menu_type'      => 'tab',
                        'no_login_valid' => '1',
                        'component'      => '',
                    ]
                ],
            ],
            [
                'type'           => 'nav',
                'title'          => '下载中心',
                'name'           => 'cmsDownloads',
                'path'           => 'cms/channel/10',
                'icon'           => 'el-icon-Minus',
                'menu_type'      => 'tab',
                'no_login_valid' => '1',
                'component'      => '',
                'children'       => [
                    [
                        'type'           => 'nav',
                        'title'          => '编程开发',
                        'name'           => 'cmsChannel11',
                        'path'           => 'cms/channel/11',
                        'icon'           => 'el-icon-Minus',
                        'menu_type'      => 'tab',
                        'no_login_valid' => '1',
                        'component'      => '',
                    ],
                    [
                        'type'           => 'nav',
                        'title'          => '视频图片',
                        'name'           => 'cmsChannel12',
                        'path'           => 'cms/channel/12',
                        'icon'           => 'el-icon-Minus',
                        'menu_type'      => 'tab',
                        'no_login_valid' => '1',
                        'component'      => '',
                    ]
                ],
            ],
            [
                'type'           => 'nav',
                'title'          => '产品中心',
                'name'           => 'cmsProducts',
                'path'           => 'cms/channel/6',
                'icon'           => 'el-icon-Minus',
                'menu_type'      => 'tab',
                'no_login_valid' => '1',
                'component'      => '',
            ],
            [
                'type'           => 'nav',
                'title'          => '搜索',
                'name'           => 'cmsSearch',
                'path'           => 'cms/search',
                'icon'           => 'el-icon-Minus',
                'menu_type'      => 'tab',
                'no_login_valid' => '1',
                'component'      => '',
                'weigh'          => -1,
            ]
        ];
        Menu::create($menu, 0, 'cover', 'frontend');
    }
}