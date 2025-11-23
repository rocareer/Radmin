<?php

/**
 * File:        Helper.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/11 13:31
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

use extend\ra\FileUtil;
use support\RequestContext;
use think\helper\Str;

if (!function_exists('env')) {
    /**
     * 获取环境变量
     * @param $key
     * @param $default
     * @return   array|false|mixed|string|null
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/19 06:45
     */
    function env($key, $default = null): mixed
    {
        if (getenv($key)) {
            return getenv($key);
        }
        return $default;
    }
}




if (!function_exists('var_export_short')) {
    /**
     * // 自定义函数将 array() 替换为 []
     * @param      $content
     * @param bool $return
     * @param bool $format
     * @return   array|string|string[]|null
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/20 03:35
     */
    function var_export_short($content, bool $return = true, bool $format = true): array|string|null
    {
        // 使用 var_export 生成数组内容
        $exported = var_export($content, true);

        // 替换 array() 为短数组语法 []
        $exported = str_replace(["array (", ")"], ["[", "]"], $exported);

        // 使用正则表达式将 `=>` 和 `[` 放到同一行
        if ($format) $exported = preg_replace('/=>\s+\[/', '=> [', $exported);

        return $exported;
    }
}


if (!function_exists('modify_config')) {
    /**
     * 修改配置文件
     *
     * @param string $configFile 配置文件路径（相对于config目录）
     * @param array  $newConfig  要修改或添加的配置项
     * @return bool 是否修改成功
     */
    function modify_config(string $configFile, array $newConfig, ?string $plugin = null, bool $replace = false, bool $convertToClass = true): bool
    {
        return true;
        $configPath = config_path();
        if ($plugin) {
            if (strpos($plugin, '/') !== false) {
                $configPath = base_path() . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $configFile;
            } else {
                $configPath = config_path() . DIRECTORY_SEPARATOR . $plugin;
            }

        }
        $configPath = $configPath . DIRECTORY_SEPARATOR . $configFile;

        // 检查文件是否存在
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException("配置文件 {$configFile} 不存在");
        }

        // 获取当前配置
        $currentConfig = include $configPath;
        if (!is_array($currentConfig)) {
            throw new RuntimeException("配置文件 {$configFile} 必须返回数组");
        }

        // 处理配置
        if ($replace) {
            $mergedConfig = $newConfig;
        } else {
            if (array_keys($currentConfig) === range(0, count($currentConfig) - 1)) {
                // 简单数组处理
                $mergedConfig = array_unique(array_merge($currentConfig, $newConfig));

                // 格式转换
                if ($convertToClass) {
                    $mergedConfig = array_map(function ($item) {
                        if (is_string($item) && strpos($item, '\\') !== false && !str_ends_with($item, '::class')) {
                            return '\\' . trim(str_replace("'", "", $item), '\\') . '::class';
                        }
                        return $item;
                    }, $mergedConfig);
                }
            } else {
                // 关联数组处理
                $mergedConfig = array_merge_recursive($currentConfig, $newConfig);
            }
        }

        // 保留注释
        $originalContent = file_get_contents($configPath);
        preg_match('/<\?php(.*?)\nreturn\s*\[/s', $originalContent, $matches);
        $headerComment = $matches[1] ?? '';

        // 生成新的配置内容
        $newContent = "<?php{$headerComment}\nreturn " . var_export($mergedConfig, true) . ";\n";

        // 格式化数组缩进
        $newContent = preg_replace('/\s+array\s*\(/', ' [', $newContent);
        $newContent = preg_replace('/\s+\)/', ' ]', $newContent);
        $newContent = preg_replace('/array\s*\(/', '[', $newContent);
        $newContent = preg_replace('/\)/', ']', $newContent);
        $newContent = str_replace('  ', '    ', $newContent); // 统一缩进为4个空格

        // 写入文件
        return file_put_contents($configPath, $newContent) !== false;
    }

}


if (!function_exists('formatBytes')) {
    /**
     * 将字节大小转换为人类可读的格式
     * @param int $bytes     字节数
     * @param int $precision 精度
     * @return string 格式化后的大小
     */
    function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
if (!function_exists('unitToByte')) {

    /**
     * 单位转为字节
     * @param string $unit 将b、kb、m、mb、g、gb的单位转为 byte
     * @return   int
     *                     Author:   albert <albert@rocareer.com>
     *                     Time:     2025/5/18 22:42
     */
    function unitToByte(string $unit): int
    {
        preg_match('/([0-9.]+)(\w+)/', $unit, $matches);
        if (!$matches) {
            return 0;
        }
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        return (int)($matches[1] * pow(1024, $typeDict[strtolower($matches[2])] ?? 0));

    }
}

if (!function_exists('getDbPrefix')) {
    /**
     * 获取数据库表前缀
     * @return   array|false|mixed|string
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/17 02:34
     */
    function getDbPrefix(): mixed
    {
        return env('MYSQL_PREFIX') ?? config('think-orm.connections.mysql.prefix');
    }
}

if (!function_exists('parseClass')) {
    /**
     * 自动解析验证器类名称
     * @param string $layer
     * @param string $name
     * @return   string
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/16 15:10
     */
    function parseClass(string $layer, string $name): string
    {
        $name  = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        $path  = $array ? implode('\\', $array) . '\\' : '';

        return 'app\\' . request()->app . '\\' . $layer . '\\' . $path . $class;
    }
}
if (!function_exists('arrayStrictFilter')) {
    /**
     * 过滤数组
     * @param array $input
     * @param array $allowedKeys
     * @return   array
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 20:48
     */
    function arrayStrictFilter(array $input, array $allowedKeys): array
    {
        // 检查字段数量是否一致
        if (count($input) !== count($allowedKeys)) {
            throw new InvalidArgumentException('输入数组的字段数量与允许的字段数量不一致');
        }

        // 检查字段名称是否完全匹配
        $inputKeys = array_keys($input);
        if ($inputKeys !== $allowedKeys) {
            throw new InvalidArgumentException('输入数组的字段名称与允许的字段名称不匹配');
        }

        // 返回过滤后的数组
        return array_intersect_key($input, array_flip($allowedKeys));
    }
}

/**
 * 检查路径是否需要跳过认证
 * @param string|null $path
 * @return bool
 */

if (!function_exists('shouldExclude')) {
    function shouldExclude(?string $path = null): bool
    {
        $path          = $path ?? request()->path();
        $excludeRoutes = config('roles.exclude', []);

        foreach ($excludeRoutes as $route) {
            if (strpos($route, '*') !== false) {
                // 处理通配符路径
                $pattern = '#^' . str_replace('\*', '.*', preg_quote($route, '#')) . '$#';
                if (preg_match($pattern, $path)) {
                    return true;
                }
            } elseif ($path === $route) {
                return true;
            }
        }
        return false;
    }
}


if (!function_exists('getEncryptedToken')) {

    /**
     * 获取加密后的token
     * By albert  2025/05/06 17:32:42
     * @param string $token
     * @param string $algo
     * @param string $key
     * @return string
     */
    function getEncryptedToken(string $token, $algo = 'sha256', $key = 'rocareer'): string
    {
        return hash_hmac($algo, $token, $key);
    }
}

/**
 * 获取token - 统一认证头配置
 */
if (!function_exists('getTokenFromRequest')) {
    /**
     * 从请求中获取token
     * 统一前后台认证头配置：后台使用X-Token，前台使用Authorization
     * By albert  2025/05/06 17:30:58
     * @param null  $request
     * @param array $names
     * @return string|null
     * @throws Exception
     */
    function getTokenFromRequest($request = null, array $names = []): ?string
    {
        if (!empty($names)) {
            return get_ba_token($names);
        }

        $request = $request ?? request();

        // 根据请求路径区分前后台Token
        $path = $request->path();
        
        // 后台请求：优先从X-Token头获取
        if (str_starts_with($path, '/admin/')) {
            return getAdminToken($request);
        }
        
        // 前台请求：优先从Authorization头获取
        return getUserToken($request);
    }


    /**
     * 获取后台Token
     * By albert  2025/11/21 19:38:33
     * @param $request
     * @return string|null
     */
    function getAdminToken($request): ?string
    {
        // 1. 优先从X-Token头获取
        $token = $request->header('X-Token');
        if (!empty($token)) {
            return $token;
        }
        
        // 2. 从配置的后台Token头获取
        $adminHeaders = config("roles.roles.admin.headers", ['X-Admin-Token', 'Admin-Token']);
        foreach ($adminHeaders as $header) {
            $token = $request->header($header);
            if (!empty($token)) {
                return $token;
            }
        }
        
        // 3. 从配置的后台Token参数获取
        $adminParams = config("roles.roles.admin.params", ['batoken', 'admin_token']);
        foreach ($adminParams as $param) {
            $token = $request->input($param);
            if (!empty($token)) {
                return $token;
            }
        }
        
        // 4. 从通用的token参数获取（用于EventSource等场景）
        $token = $request->input('token');
        if (!empty($token)) {
            return $token;
        }
        
        return null;
    }


    /**
     * 获取前台Token
     * By albert  2025/11/21 19:38:19
     * @param $request
     * @return string|null
     */
    function getUserToken($request): ?string
    {
        // 1. 优先从Authorization头获取Bearer Token
        $token = $request->header('Authorization');
        if (!empty($token) && str_starts_with($token, 'Bearer ')) {
            return extractBearerToken($token);
        }
        
        // 2. 从配置的前台Token头获取
        $userHeaders = config("roles.roles.user.headers", ['User-Token', 'X-User-Token']);
        foreach ($userHeaders as $header) {
            $token = $request->header($header);
            if (!empty($token)) {
                return $token;
            }
        }
        
        // 3. 从配置的前台Token参数获取
        $userParams = config("roles.roles.user.params", ['api-token', 'user_token']);
        foreach ($userParams as $param) {
            $token = $request->input($param);
            if (!empty($token)) {
                return $token;
            }
        }
        
        return null;
    }

    /**
     * 从 Authorization 头中提取 Bearer Token
     * @param string $token
     * @return   string
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 13:30
     */
    function extractBearerToken(string $token): string
    {
        return trim(str_replace('Bearer ', '', $token));
    }
}


if (!function_exists('get_ba_token')) {
    /**
     * 获取auth token
     * By albert  2025/05/06 18:24:39
     * @param array $names
     * @return string
     */
    function get_ba_token(array $names = ['ba', 'token']): string
    {
        $separators = [
            'header' => ['', '-'], // batoken、ba-token【ba_token 不在 header 的接受列表内因为兼容性不高，改用 http_ba_token】
            'param'  => ['', '-', '_'], // batoken、ba-token、ba_token
            'server' => ['_'], // http_ba_token
        ];

        $tokens  = [];
        $request = request();
        foreach ($separators as $fun => $sps) {
            foreach ($sps as $sp) {
                $tokens[] = $request->$fun(($fun == 'server' ? 'http_' : '') . implode($sp, $names));
            }
        }
        $tokens = array_filter($tokens);
        return array_values($tokens)[0] ?? '';
    }
}


if (!function_exists('arrayToObject')) {
    /**
     * 将数组转换为对象
     * By albert  2025/11/21 19:37:52
     * @param array $array
     * @return object
     */
    function arrayToObject(array $array): object
    {
        return json_decode(json_encode($array));
    }
}


if (!function_exists('jsonToArray')) {
    /**
     * 将 JSON 字符串或数组转换为 PHP 数组
     * By albert  2025/04/19 20:19:21
     *
     * @param $data
     *
     * @return mixed|string
     */
    function jsonToArray($data): mixed
    {
        // 判断是否是数组
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // 递归处理嵌套数组
                $data[$key] = jsonToArray($value);
            }
        } elseif (is_string($data)) {
            // 判断是否是 JSON 字符串
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // 如果是有效的 JSON，则解析为数组
                return jsonToArray($decoded);
            }
        }
        // 如果不是 JSON 或数组，直接返回原值
        return $data;
    }
}


if (!function_exists('root_path')) {
    /**
     * 获取项目根目录
     *
     * @param string $path
     *
     * @return string
     */
    function root_path(string $path = ''): string
    {
        if (empty($path)) {
            return base_path() . DIRECTORY_SEPARATOR;
        }
        return base_path() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
    }
}


if (!function_exists('clean_xss')) {
    /**
     * 清除XSS 无依赖版本
     * By albert  2025/04/15 19:10:24
     *
     * @param string $string
     *
     * @return string
     */
    function clean_xss(string $string): string
    {
        // 移除不可见字符
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $string);

        // 移除危险的HTML属性
        $string = preg_replace_callback('/<[^>]+>/i', function ($matches) {
            $tags = 'img|a|div|p|span|table|tr|td|th|ul|ol|li|h1|h2|h3|h4|h5|h6|pre|code|strong|em|b|i|u|strike|br|hr';

            $tag = preg_replace('/<([a-z]+)([^>]*)>/i', '$1', $matches[0]);
            if (!preg_match("/^($tags)$/i", $tag)) {
                return '';
            }

            return preg_replace_callback('/\s([a-z_-]+)\s*=\s*(["\'])(.*?)\2/i', function ($attrMatches) {
                $safeAttributes = 'href|src|alt|title|class|style|width|height|align|border|cellpadding|cellspacing|colspan|rowspan|valign';
                if (preg_match("/^($safeAttributes)$/i", $attrMatches[1])) {
                    // 对URL类属性进行特殊处理
                    if (in_array(strtolower($attrMatches[1]), [
                        'href',
                        'src',
                    ])) {
                        $url = htmlspecialchars_decode($attrMatches[3]);
                        if (!preg_match('/^(https?:|mailto:|tel:|#)/i', $url)) {
                            return '';
                        }
                        return ' ' . $attrMatches[1] . '=' . $attrMatches[2] . htmlspecialchars($url) . $attrMatches[2];
                    }
                    return $attrMatches[0];
                }
                return '';
            }, $matches[0]);
        }, $string);

        // 移除危险的JavaScript协议
        $string = preg_replace('/(javascript|jscript|vbscript|data):/i', '', $string);

        // 转换特殊字符
        $string = htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 二次过滤，确保安全
        $string = strip_tags($string, '<p><a><img><div><span><br><hr><h1><h2><h3><h4><h5><h6><ul><ol><li><table><tr><td><th><pre><code><strong><em><b><i><u><strike>');

        return $string;
    }
}


if (!function_exists('get_controller_list')) {

    /**
     * @param string $app
     * @return array
     */
    function get_controller_list(string $app = 'admin'): array
    {
        $controllerDir = app_path() . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR;
        return FileUtil::getDirFiles($controllerDir);
    }
}

if (!function_exists('parse_name')) {
    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name    字符串
     * @param int    $type    转换类型
     * @param bool   $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    function parse_name(string $name, int $type = 0, bool $ucfirst = true): string
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}
