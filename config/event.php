<?php
/**
 * File:        event.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/12 05:37
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

use app\admin\model\data\Backup;
use app\admin\model\log\authentication\Admin;
use app\admin\model\log\data\Backup as BackupLog;
use app\admin\model\log\data\Restore as RestoreLog;
use support\member\State;
use support\member\StateEventListener;

return [
    // 状态检查事件
    'state.check.before' => [
        [StateEventListener::class, 'eventBeforeCheck'],
    ],
    // 检查账号状态
    'state.check.account_status' => [
        [StateEventListener::class, 'eventCheckAccountStatus'],
    ],
    // 检查登录失败次数
    'state.check.login_failures' => [
        [StateEventListener::class, 'eventCheckLoginFailures'],
    ],
    // 检查单点登录状态
    'state.check.sso_status' => [
        [StateEventListener::class, 'eventCheckSsoStatus'],
    ],
    // 状态检查后事件
    'state.check.after' => [
        [StateEventListener::class, 'eventAfterCheck'],
    ],
    
    // 登录成功事件
    'state.login_success' => [
        [StateEventListener::class, 'eventLoginSuccess'],
    ],
    // 登录失败事件
    'state.login_failure' => [
        [StateEventListener::class, 'eventLoginFailure'],
    ],
    
    // 通配符事件（兼容旧版本）
    'state.check.*' => [
        [StateEventListener::class, 'eventHandleWildcard'],
    ],
];
