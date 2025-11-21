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
use app\middleware\RoleAuthMiddleware;

return [
    ''      => [
        // 1. 全局跨域处理
        AccessControlMiddleWare::class,
        // 2. 请求预处理（角色检测、Token提取）
        RequestMiddleWare::class,
        // 3. 请求上下文初始化
        RequestContextMiddleWare::class,
        // 4. 统一角色鉴权（合并RoleIsolationMiddleWare和AuthMiddleware功能）
        RoleAuthMiddleware::class,
        // 5. 请求清理（确保在最后执行）
        \app\middleware\RequestCleanupMiddleWare::class,

    ],
    'api'   => [
        // API专用中间件
    ],
    'admin' => [
        // 管理员操作日志
        AdminLog::class,
        // 数据安全
        AdminSecurity::class,
    ],
    'user'  => [
        // 用户专用中间件
    ],

];
