<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use support\Request;

return [
    'debug'             => env('APP_DEBUG', true),
    'error_reporting'   => E_ALL,
    'default_timezone'  => env('DEFAULT_TIMEZONE', 'Asia/Shanghai'),
    'controller_suffix' => '',
    'controller_reuse'  => false,

    'request_class'  => Request::class,
    'public_path'    => base_path() . DIRECTORY_SEPARATOR . 'public',
    'runtime_path'   => base_path() . DIRECTORY_SEPARATOR . 'runtime',
    'app_path'       => base_path() . DIRECTORY_SEPARATOR . 'app',

    // http cache 实验功能
    'http_cache'     => env('HTTP_CACHE', false),
    'http_cache_ttl' => env('HTTP_CACHE_TTL', 0),

    // request log 实验功能
    'request'    => [
        'log' => [
            'enable'  => true,
            'channel' => 'R-request'
        ]
    ]
];
