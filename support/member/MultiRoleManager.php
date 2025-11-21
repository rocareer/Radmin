<?php
/**
 * File:        MultiRoleManager.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/21
 * Description: 多角色管理器 - 管理多角色多账号同时登录的隔离机制
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace support\member;

use support\RequestContext;
use Webman\Event\Event;

/**
 * 多角色管理器
 */
class MultiRoleManager
{
    /**
     * 获取当前活跃的所有角色
     */
    public static function getActiveRoles(): array
    {
        $context = RequestContext::get('member_context');
        if ($context) {
            return $context->getActiveRoles();
        }
        return [];
    }
    
    /**
     * 检查指定角色是否已登录
     */
    public static function isRoleLoggedIn(string $role): bool
    {
        $context = RequestContext::get('member_context');
        if ($context && $context->hasRoleContext($role)) {
            $roleContext = $context->roleContexts[$role] ?? [];
            return !empty($roleContext['model']);
        }
        return false;
    }
    
    /**
     * 获取指定角色的成员信息
     */
    public static function getRoleMember(string $role): ?object
    {
        $context = RequestContext::get('member_context');
        if ($context && $context->hasRoleContext($role)) {
            $roleContext = $context->roleContexts[$role] ?? [];
            return $roleContext['model'] ?? null;
        }
        return null;
    }
    
    /**
     * 注销指定角色的登录
     */
    public static function logoutRole(string $role): bool
    {
        try {
            $context = RequestContext::get('member_context');
            if (!$context || !$context->hasRoleContext($role)) {
                return false;
            }
            
            // 清理角色上下文
            $context->clearRoleContext($role);
            
            // 记录注销日志
            Event::emit("log.authentication.{$role}.logout.by_manager", [
                'role' => $role,
                'active_roles' => $context->getActiveRoles()
            ]);
            
            return true;
            
        } catch (\Throwable $e) {
            Event::emit("log.authentication.{$role}.logout.failure", [
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 强制注销所有角色的登录
     */
    public static function logoutAllRoles(): bool
    {
        try {
            $context = RequestContext::get('member_context');
            if (!$context) {
                return true;
            }
            
            $activeRoles = $context->getActiveRoles();
            
            // 清理所有角色上下文
            $context->clearAllRoleContexts();
            
            // 清理请求上下文
            RequestContext::set('member', null);
            
            // 记录强制注销日志
            Event::emit('log.authentication.force_logout_all', [
                'active_roles' => $activeRoles
            ]);
            
            return true;
            
        } catch (\Throwable $e) {
            Event::emit('log.authentication.force_logout_all.failure', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取多角色登录状态统计
     */
    public static function getLoginStatus(): array
    {
        $context = RequestContext::get('member_context');
        if (!$context) {
            return [
                'total_roles' => 0,
                'logged_in_roles' => [],
                'active_roles' => []
            ];
        }
        
        $activeRoles = $context->getActiveRoles();
        $loggedInRoles = [];
        
        foreach ($activeRoles as $role) {
            if (self::isRoleLoggedIn($role)) {
                $loggedInRoles[] = $role;
            }
        }
        
        return [
            'total_roles' => count($activeRoles),
            'logged_in_roles' => $loggedInRoles,
            'active_roles' => $activeRoles,
            'context_status' => $context->getContextStatus()
        ];
    }
    
    /**
     * 验证Token角色一致性
     */
    public static function validateTokenRoleConsistency(string $token, string $expectedRole): bool
    {
        try {
            $payload = \support\token\Token::verify($token);
            return $payload->role === $expectedRole;
        } catch (\Throwable $e) {
            return false;
        }
    }
}