<?php
/**
 * File:        SystemUtil.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/19 02:41
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace extend\ra;

use app\admin\model\Config as ConfigModel;
use support\cache\Cache;
use support\orm\Db;

class SystemUtil
{
    public static function get_sys_config(string $name = '', string $group = '', bool $concise = true): mixed
    {
        if (!self::installed()) {
            return [];
        }
        
        // 使用缓存键名而不是标签
        if ($name) {
            return self::getSingleConfig($name);
        } elseif ($group) {
            return self::getGroupConfig($group, $concise);
        } else {
            return self::getConfigList($concise);
        }
    }

    /**
     * 获取单个配置项
     */
    private static function getSingleConfig(string $name): mixed
    {
        $cacheKey = 'sys_config_' . $name;
        $config = Cache::get($cacheKey);
        if (!$config) {
            $config = ConfigModel::where('name', $name)->find();
            if ($config) {
                $config = $config['value'];
                Cache::set($cacheKey, $config, 3600); // 缓存1小时
            }
        }
        return $config;
    }

    /**
     * 获取分组配置
     */
    private static function getGroupConfig(string $group, bool $concise = true): array
    {
        // 分组配置缓存
        $cacheKey = 'sys_config_group_' . $group;
        $temp = Cache::get($cacheKey);
        if (!$temp) {
            $temp = ConfigModel::where('group', $group)->order('weigh desc')->select()->toArray();
            Cache::set($cacheKey, $temp, 3600); // 缓存1小时
        }
        
        return $concise ? self::formatConfigConcise($temp) : $temp;
    }

    /**
     * 获取配置列表
     */
    private static function getConfigList(bool $concise = true): array
    {
        // 全部配置缓存
        $cacheKey = 'sys_config_all';
        $temp = Cache::get($cacheKey);
        if (!$temp) {
            $temp = ConfigModel::order('weigh desc')->select()->toArray();
            Cache::set($cacheKey, $temp, 3600); // 缓存1小时
        }
        
        return $concise ? self::formatConfigConcise($temp) : $temp;
    }

    /**
     * 格式化简洁配置格式
     */
    private static function formatConfigConcise(array $temp): array
    {
        $config = [];
        foreach ($temp as $item) {
            $config[$item['name']] = $item['value'];
        }
        return $config;
    }

    public static function get_route_remark(): string
    {
        $controllerName = request()->controller();
        $actionName     = request()->action;
        $path           = str_replace('.', '/', $controllerName);
        $path           = str_replace('admin/', '', $path);
        $remark         = Db::name('admin_rule')
            ->where('name', $path)
            ->whereOr('name', $path . '/' . $actionName)
            ->value('remark');
        return __((string)$remark);
    }
    public static function installed(): bool
    {
        return file_exists(public_path('install.lock'));
    }
}