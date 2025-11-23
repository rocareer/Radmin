<?php
/**
 * File:        event.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/12 05:37
 * Description: 会员事件监听器配置 - 优化版
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

use support\member\Event;

return [

    // ==================== 认证相关事件 ====================
    
    // 登录事件
    'member.login_success' => [
        [Event::class, 'eventLoginSuccess'],
    ],
    'member.login_failure' => [
        [Event::class, 'eventLoginFailure'],
    ],
    
    // 注册事件
    'member.register_success' => [
        [Event::class, 'eventRegisterSuccess'],
    ],
    'member.register_failure' => [
        [Event::class, 'eventRegisterFailure'],
    ],
    
    // 注销事件（合并普通注销和强制注销）
    'member.logout_success' => [
        [Event::class, 'eventLogoutSuccess'],
    ],
    'member.logout_failure' => [
        [Event::class, 'eventLogoutFailure'],
    ],
    
    // ==================== 状态管理事件 ====================
    
    // 状态检查事件（合并开始、成功、失败）
    'member.status_check' => [
        [Event::class, 'eventStatusCheck'],
    ],
    
    // 状态更新事件
    'member.state.update' => [
        [Event::class, 'eventStateUpdate'],
    ],
    
    // ==================== 角色管理事件 ====================
    
    // 角色切换事件（合并切换、跳过、失败）
    'member.role.switch' => [
        [Event::class, 'eventRoleSwitch'],
    ],
    
    // 角色一致性检查
    'member.role.consistency.mismatch' => [
        [Event::class, 'eventRoleConsistencyMismatch'],
    ],
    
    // 角色清理事件（合并单个清理、批量清理）
    'member.role.cleanup' => [
        [Event::class, 'eventRoleCleanup'],
    ],
    
    // ==================== 上下文管理事件 ====================
    
    // 上下文清理事件
    'member.context.cleared' => [
        [Event::class, 'eventContextCleared'],
    ],
    
    // ==================== 系统事件 ====================
    
    // 初始化失败事件
    'member.initialization.failure' => [
        [Event::class, 'eventInitializationFailure'],
    ],

];
