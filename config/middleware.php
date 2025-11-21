<?php
/**
 * File:        middleware.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/12 02:37
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

use app\middleware\AccessControlMiddleWare;
use app\middleware\AdminLog;
use app\middleware\AdminSecurity;
use app\middleware\AuthMiddleware;
use app\middleware\RequestContextMiddleWare;
use app\middleware\RequestMiddleWare;
use app\middleware\RoleIsolationMiddleWare;

return [
    ''      => [
        // 全局跨域
        AccessControlMiddleWare::class,
        // 请求预处理
        RequestMiddleWare::class,
        // 上下文初始化
        RequestContextMiddleWare::class,
        // 角色隔离
        RoleIsolationMiddleWare::class,
        // 鉴权
        AuthMiddleware::class,
        // 请求清理（确保在最后执行）
        \app\middleware\RequestCleanupMiddleWare::class,

    ],
    'api'   => [

    ],
    'admin' => [

        // 管理员操作日志
        AdminLog::class,
        // 数据安全
        AdminSecurity::class,
    ],
    'user'  => [

    ],

];
