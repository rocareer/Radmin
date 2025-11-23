<?php
/**
 * File:        RoleIsolationMiddleware.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/21
 * Description: 角色隔离中间件 - 确保前台用户和后台用户完全隔离
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace app\middleware;

use support\Request;
use support\RequestContext;
use support\member\Context;
use support\member\Member;
use support\StatusCode;
use app\exception\UnauthorizedHttpException;

class RoleIsolationMiddleWare implements MiddlewareInterface
{
    /**
     * 处理请求
     */
    public function process(Request $request, callable $handler)
    {
        try {
            // 1. 获取已在RequestMiddleWare中设置的角色
            $role = $request->role;
            
            // 2. 验证角色有效性
            if (!$role || !in_array($role, ['admin', 'user'])) {
                throw new UnauthorizedHttpException('无效的角色类型', StatusCode::ACCESS_DENIED);
            }
            
            // 3. 初始化角色上下文
            $this->initializeRoleContext($role);
            
            // 4. 验证角色一致性（如果已登录）
            if ($request->token) {
                $this->validateRoleConsistency($request, $role);
            }
            
            // 5. 处理请求
            $response = $handler($request);
            
            // 6. 请求完成后清理角色上下文
            $this->cleanupRoleContext($role);
            
            return $response;
            
        } catch (UnauthorizedHttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new UnauthorizedHttpException('角色隔离处理失败: ' . $e->getMessage(), StatusCode::TOKEN_INVALID);
        }
    }
    

    
    /**
     * 初始化角色上下文
     */
    private function initializeRoleContext(string $role): void
    {
        // 获取成员上下文实例
        $context = RequestContext::get('member_context');
        if (!$context) {
            $context = new Context();
        }
        
        // 切换到指定角色上下文
        $context->switchRole($role);
    }
    
    /**
     * 验证角色一致性
     */
    private function validateRoleConsistency(Request $request, string $expectedRole): void
    {
        try {
            // 验证Token中的角色与请求路径一致
            $payload = \support\token\Token::verify($request->token);
            
            if ($payload->role !== $expectedRole) {
                // 检查是否是其他角色的Token，如果是，记录日志但不阻止访问
                $activeRoles = \support\member\Role::getInstance()->getActiveRoles();
                if (in_array($payload->role, $activeRoles)) {
                    // Token角色与请求路径不匹配，但属于活跃角色，记录日志
                    \Webman\Event\Event::emit('member.role_consistency.mismatch', [
                        'token_role' => $payload->role,
                        'expected_role' => $expectedRole,
                        'request_path' => $request->path(),
                        'active_roles' => $activeRoles
                    ]);
                } else {
                    throw new UnauthorizedHttpException(
                        '访问权限不足：当前Token角色与请求路径不匹配', 
                        StatusCode::ACCESS_DENIED
                    );
                }
            }
            
            // 验证用户状态
            Member::setCurrentRole($expectedRole);
            Member::initialization();
            
        } catch (\support\token\TokenExpiredException $e) {
            throw new UnauthorizedHttpException('Token已过期', StatusCode::TOKEN_SHOULD_REFRESH);
        } catch (\Throwable $e) {
            throw new UnauthorizedHttpException('Token验证失败: ' . $e->getMessage(), StatusCode::NEED_LOGIN);
        }
    }
    
    /**
     * 清理角色上下文
     */
    private function cleanupRoleContext(string $role): void
    {
        // 保存当前角色上下文
        $context = RequestContext::get('member_context');
        if ($context) {
            $context->saveRoleContext();
        }
        
        // 成员信息清理由 RoleManager 统一处理
    }
    
    /**
     * 获取当前活跃的角色列表
     */
    public static function getActiveRoles(): array
    {
        return \support\member\Role::getInstance()->getActiveRoles();
    }
    
    /**
     * 强制清除指定角色的所有上下文
     */
    public static function forceClearRoleContext(string $role): bool
    {
        return \support\member\Role::getInstance()->cleanupRoleContext($role);
    }
}