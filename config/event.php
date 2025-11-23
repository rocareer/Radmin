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
use support\member\Event;

return [

    
    // 登录成功事件
    'member.login_success' => [
        [Event::class, 'eventLoginSuccess'],
    ],
    // 登录失败事件
    'member.login_failure' => [
        [Event::class, 'eventLoginFailure'],
    ],
    
    // 用户注册成功事件
    'member.register_success' => [
        [Event::class, 'eventRegisterSuccess'],
    ],
    // 用户注册失败事件
    'member.register_failure' => [
        [Event::class, 'eventRegisterFailure'],
    ],
    
    // 用户注销成功事件
    'member.logout_success' => [
        [Event::class, 'eventLogoutSuccess'],
    ],
    // 用户注销失败事件
    'member.logout_failure' => [
        [Event::class, 'eventLogoutFailure'],
    ],
    
    // 强制注销事件
    'member.force_logout' => [
        [Event::class, 'eventForceLogout'],
    ],
    
    // 角色切换事件
    'member.context.role_switched' => [
        [Event::class, 'eventRoleSwitched'],
    ],
    
    // 角色一致性检查失败事件
    'member.role_consistency.mismatch' => [
        [Event::class, 'eventRoleConsistencyMismatch'],
    ],
    
    // 用户菜单获取错误事件
    'user.menu.get.error' => [
        [Event::class, 'eventUserMenuGetError'],
    ],
    
    // 状态检查事件（已废弃，使用更具体的事件替代）
    // 'state.check_status' => [
    //     [Member::class, 'eventCheckStatus'],
    // ],
    
    // 登录更新事件（已废弃，使用 state.login_success 和 state.login_failure 替代）
    // 'state.update_login.*' => [
    //     [Member::class, 'eventUpdateLogin'],
    // ],
    
    // 通配符事件（已废弃，从未被使用）
    // 'state.check.*' => [
    //     [Member::class, 'eventHandleWildcard'],
    // ],
];
