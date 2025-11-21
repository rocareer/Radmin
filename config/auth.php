<?php
/*
 *
 *  * // +----------------------------------------------------------------------
 *  * // | Rocareer [ ROC YOUR CAREER ]
 *  * // +----------------------------------------------------------------------
 *  * // | Copyright (c) 2014~2025 Albert@rocareer.com All rights reserved.
 *  * // +----------------------------------------------------------------------
 *  * // | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  * // +----------------------------------------------------------------------
 *  * // | Author: albert <Albert@rocareer.com>
 *  * // +----------------------------------------------------------------------
 *  
 */

/**
 * File      auth.php
 * Author    albert@rocareer.com
 * Time      2025-04-27 23:36:45
 * Describe  auth.php
 */


return [

    'state'   => [
        'cache_time' => 86400,
        'store'      => 'member-cache',
        'prefix'     => 'state-'
    ],

    // 登录限制配置
    'login'   => [
        // 管理员配置
        'admin' => [
            'login_failure_retry' => 10,
            'login_lock_time'     => 3600,
            'sso'                 => false,            // 是否单点登录
            'token_keep_time'     => 259200,           // token保持时间(秒)
            'captcha'             => false,              //

        ],
        // 用户配置
        'user'  => [
            'maxRetry' => 10,
            'sso'      => false,
            'keepTime' => 259200,
            'captcha'  => false              //
        ]
    ],

    // 免认证路径配置
    'exclude' => [
        //
        '/install',
        // 后台用户登录
        '/admin/Index/login',
        // 前台用户登录
        '/user/Index/login',
        '/api/install/*',
        '/api/install/envBaseCheck',
        '/api/install/envNpmCheck',
        //点击验证码
        '/api/common/clickCaptcha',
        // 检查点击验证码
        '/api/common/checkClickCaptcha',
        // API免认证路径

        '/api/index/index',
        '/api/common/refreshToken',         // 刷新token

        // 终端免验证
        '/admin/ajax/terminal',

    ],


    'headers' => [
        'admin' => [
            'X-Token',
        ],
        'user'  => [
            'Authorization',
        ],
        'api'   => [
            'Authorization',
        ]
    ],

];