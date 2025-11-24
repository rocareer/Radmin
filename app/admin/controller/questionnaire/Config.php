<?php

namespace app\admin\controller\questionnaire;

use app\common\controller\Backend;
use think\facade\Cache;
use think\facade\Db;

class Config extends Backend
{

    /***
     * 获取配置
     */
    public function getConfig(): void
    {
        $config = \modules\questionnaire\library\Config::getConfig();

        $config['questionnaire_other']['size'] = intval($config['questionnaire_other']['size']);
        $config['questionnaire_other']['num']  = intval($config['questionnaire_other']['num']);

        $this->success('', $config);
    }

    /***
     * 保存配置
     */
    public function saveConfig(): void
    {
        $type = $this->request->get('type');
        $post = $this->request->post();

        if ($type == 'questionnaire_other') {
            $post['size'] = intval($post['size']);
            $post['num']  = intval($post['num']);
        }

        try {
            Db::name('config')
                ->where('name', '=', $type)
                ->where('group', \modules\questionnaire\library\Config::$modules)
                ->update([
                    'value' => json_encode($post),
                ]);
        } catch (\Throwable $e) {
            $this->error('操作失败:' . $e->getMessage());
        }
        Cache::tag(\modules\questionnaire\library\Config::$cacheTag)->clear();

        $this->success();
    }
}