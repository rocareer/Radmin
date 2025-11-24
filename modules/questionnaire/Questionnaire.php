<?php

namespace modules\questionnaire;

use app\admin\model\AdminRule;
use app\common\library\Menu;
use modules\questionnaire\library\Config;
use support\cache\Cache;
use think\facade\Db;
use Throwable;

class Questionnaire
{
    /**
     * 安装
     * @throws Throwable
     */
    public function install(): void
    {
        $pMenu = AdminRule::where('name', 'questionnaire')->value('id');
        if (!$pMenu) {
            $menu = [
                [
                    'type'      => 'menu_dir',
                    'title'     => '问卷调查',
                    'name'      => 'questionnaire',
                    'path'      => 'questionnaire',
                    'icon'      => 'el-icon-Edit',
                    'menu_type' => 'tab',
                ]
            ];
            Menu::create($menu);
            $pMenu = AdminRule::where('name', 'questionnaire')->value('id');
        }

        $menu = [
            [
                'type'      => 'menu',
                'title'     => '问卷管理',
                'name'      => 'questionnaire/examination',
                'path'      => 'questionnaire/examination',
                'icon'      => 'el-icon-Files',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/questionnaire/examination/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'questionnaire/examination/index'],
                    ['type' => 'button', 'title' => '添加', 'name' => 'questionnaire/examination/add'],
                    ['type' => 'button', 'title' => '编辑', 'name' => 'questionnaire/examination/edit'],
                    ['type' => 'button', 'title' => '预览', 'name' => 'questionnaire/examination/preview'],
                    ['type' => 'button', 'title' => '二维码', 'name' => 'questionnaire/examination/getQrCode'],
                    ['type' => 'button', 'title' => '更新二维码', 'name' => 'questionnaire/examination/updateQrCode'],
                ],
            ],
            [
                'type'      => 'menu',
                'title'     => '问题管理',
                'name'      => 'questionnaire/question',
                'path'      => 'questionnaire/question',
                'icon'      => 'fa fa-navicon',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/questionnaire/question/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'questionnaire/question/index'],
                    ['type' => 'button', 'title' => '添加', 'name' => 'questionnaire/question/add'],
                    ['type' => 'button', 'title' => '编辑', 'name' => 'questionnaire/question/edit'],
                    ['type' => 'button', 'title' => '快速排序', 'name' => 'questionnaire/question/sortable'],
                    ['type' => 'button', 'title' => '获取题目', 'name' => 'questionnaire/question/getQuestions'],
                ],
            ],
            [
                'type'      => 'menu',
                'title'     => '答卷管理',
                'name'      => 'questionnaire/answerSheet',
                'path'      => 'questionnaire/answerSheet',
                'icon'      => 'el-icon-Document',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/questionnaire/answerSheet/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'questionnaire/answerSheet/index'],
                    ['type' => 'button', 'title' => '删除', 'name' => 'questionnaire/answerSheet/del'],
                    ['type' => 'button', 'title' => '阅卷', 'name' => 'questionnaire/answerSheet/look'],
                ],
            ],
            [
                'type'      => 'menu',
                'title'     => '答题分析',
                'name'      => 'questionnaire/answer',
                'path'      => 'questionnaire/answer',
                'icon'      => 'fa fa-bar-chart-o',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/questionnaire/answer/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'questionnaire/answer/index'],
                    ['type' => 'button', 'title' => '分析-填空', 'name' => 'questionnaire/answer/gap'],
                    ['type' => 'button', 'title' => '分析-单/多选', 'name' => 'questionnaire/answer/analyse'],
                ],
            ],
            [
                'type'      => 'menu',
                'title'     => '配置管理',
                'name'      => 'questionnaire/config',
                'path'      => 'questionnaire/config',
                'icon'      => 'fa fa-gears',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/questionnaire/config/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'questionnaire/config/getConfig'],
                    ['type' => 'button', 'title' => '修改', 'name' => 'questionnaire/config/saveConfig'],
                ],
            ],
        ];
        Menu::create($menu);
        //插入配置数据
        $config = [
            ['name' => 'questionnaire_h5', 'group' => 'questionnaire', 'title' => 'H5配置', 'type' => 'string', 'value' => '{"domain":""}'],
            ['name' => 'questionnaire_mini', 'group' => 'questionnaire', 'title' => '小程序配置', 'type' => 'string', 'value' => '{"appid":"","secret":""}'],
            ['name' => 'questionnaire_other', 'group' => 'questionnaire', 'title' => '其它配置', 'type' => 'string', 'value' => '{"picture":"jpg,png,jpeg,gif","video":"mp4,mov","file":"pdf,ppt,xls,xlsx,zip,doc,docx","size":10,"num":5}']
        ];
        
        // 检查配置是否已存在，避免重复插入
        foreach ($config as $item) {
            $exists = Db::name('config')->where('name', $item['name'])->find();
            if (!$exists) {
                Db::name('config')->insert($item);
            }
        }
    }

    /**
     * 卸载
     * @throws Throwable
     */
    public function uninstall(): void
    {
        Menu::delete('questionnaire', true);
        //删除配置数据
        Db::name('config')->where('group', Config::$modules)->delete();
        //删除缓存
        Cache::tag(Config::$cacheTag)->clear();
    }

    /**
     * 启用
     * @throws Throwable
     */
    public function enable(): void
    {
        Menu::enable('questionnaire');
        //删除缓存
        Cache::tag(Config::$cacheTag)->clear();
    }

    /**
     * 禁用
     * @throws Throwable
     */
    public function disable(): void
    {
        Menu::disable('questionnaire');
        //删除缓存
        Cache::tag(Config::$cacheTag)->clear();
    }
}