<?php

namespace modules\questionnaire\library;

use support\cache\Cache;
use think\facade\Db;

class Config
{
    public static string $modules  = 'questionnaire';
    public static string $cacheTag = 'questionnaire_modules';

    /**
     * 获取配置
     * @return array
     */
    public static function getConfig(): array
    {
        $key    = 'questionnaire_modules_config';
        $config = Cache::get($key);
        if (!$config) {
            $config = Db::name('config')
                ->where('group', self::$modules)
                ->column('value', 'name');

            foreach ($config as $k => $v) {
                $array = [];
                if ($v) {
                    $array = json_decode($v, true);
                }
                $config[$k] = $array;
            }
            Cache::set('questionnaire_modules_config', $config);

            Cache::tag(self::$cacheTag)->set($key, $config);
        }

        return $config;
    }
}