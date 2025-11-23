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
use function request;

class SystemUtil
{
    /**
     * 获取系统配置
     * @param string $name 配置项名称
     * @param string $group 配置分组
     * @param bool $concise 是否返回简洁格式
     * @return mixed
     */
    public static function get_sys_config(string $name = '', string $group = '', bool $concise = true): mixed
    {
        if (!self::installed()) {
            return $name ? '' : [];
        }
        
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
        
        if ($config === null) {
            $configModel = ConfigModel::where('name', $name)->find();
            if ($configModel) {
                // 通过模型的访问器获取处理后的value值
                $config = $configModel->value;
                Cache::set($cacheKey, $config, 3600); // 缓存1小时
            } else {
                $config = '';
                Cache::set($cacheKey, $config, 3600);
            }
        }
        
        return $config;
    }

    /**
     * 获取分组配置
     */
    private static function getGroupConfig(string $group, bool $concise = true): array
    {
        $cacheKey = 'sys_config_group_' . $group;
        $config = Cache::get($cacheKey);
        
        if ($config === null) {
            $configModels = ConfigModel::where('group', $group)->order('weigh desc')->select();
            $config = [];
            
            foreach ($configModels as $model) {
                if ($concise) {
                    // 简洁格式：name => value
                    $config[$model->name] = $model->value;
                } else {
                    // 完整格式：包含所有字段
                    $config[] = $model->toArray();
                }
            }
            
            Cache::set($cacheKey, $config, 3600); // 缓存1小时
        }
        
        return $config;
    }

    /**
     * 获取配置列表
     */
    private static function getConfigList(bool $concise = true): array
    {
        $cacheKey = 'sys_config_all';
        $config = Cache::get($cacheKey);
        
        if ($config === null) {
            $configModels = ConfigModel::order('weigh desc')->select();
            $config = [];
            
            foreach ($configModels as $model) {
                if ($concise) {
                    // 简洁格式：name => value
                    $config[$model->name] = $model->value;
                } else {
                    // 完整格式：包含所有字段
                    $config[] = $model->toArray();
                }
            }
            
            Cache::set($cacheKey, $config, 3600); // 缓存1小时
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