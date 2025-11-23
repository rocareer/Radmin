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
    
    // 用户状态检查开始事件
    'member.status_check.start' => [
        [Event::class, 'eventStatusCheckStart'],
    ],
    // 用户状态检查成功事件
    'member.status_check.success' => [
        [Event::class, 'eventStatusCheckSuccess'],
    ],
    // 用户状态检查失败事件
    'member.status_check.failure' => [
        [Event::class, 'eventStatusCheckFailure'],
    ],

];
