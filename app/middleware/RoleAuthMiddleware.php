<?php
/**
 * 角色鉴权中间件
 * 基于配置的角色系统，支持快速新增角色和灵活的鉴权策略
 */

namespace app\middleware;

use app\exception\UnauthorizedHttpException;
use support\Request;
use support\RequestContext;
use support\StatusCode;
use support\member\Role;
use support\member\Context;
use Webman\Event\Event;

class RoleAuthMiddleware implements MiddlewareInterface
{
    protected Role $roleManager;
    protected array $config;

    public function __construct()
    {
        $this->roleManager = Role::getInstance();
        $this->config = config('roles', []);
    }

    /**
     * 处理请求
     * 统一处理角色鉴权、上下文管理，合并RoleIsolationMiddleWare和AuthMiddleware功能
     */
    public function process(Request $request, callable $handler)
    {
        try {
            // 1. 获取已在RequestMiddleWare中设置的角色
            $role = $request->role;
            if (empty($role)) {
                throw new UnauthorizedHttpException('角色未设置', StatusCode::ACCESS_DENIED);
            }
            
            // 2. 验证角色有效性
            if (!$this->roleManager->roleExists($role)) {
                throw new UnauthorizedHttpException('无效的角色类型', StatusCode::ACCESS_DENIED);
            }
            
            // 3. 检查是否为免认证路径/方法
            if ($this->isExcludedRequest($request)) {
                return $handler($request);
            }
            
            // 4. 初始化角色上下文
            $this->initializeRoleContext($role);
            
            // 5. 验证角色访问权限
            $this->validateRoleAccess($role, $request);
            
            // 6. 处理鉴权逻辑（合并AuthMiddleware功能）
            $this->handleAuthentication($role, $request);
            
            // 7. 处理请求
            $response = $handler($request);
            
            // 8. 请求完成后清理
            $this->cleanupAfterRequest($role);
            
            return $response;
            
        } catch (UnauthorizedHttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Event::emit('role.auth.error', [
                'exception' => $e->getMessage(),
                'request_path' => $request->path(),
                'role' => $request->role ?? 'unknown'
            ]);
            
            throw new UnauthorizedHttpException(
                '角色鉴权处理失败: ' . $e->getMessage(), 
                StatusCode::TOKEN_INVALID
            );
        }
    }
    
    /**
     * 检查是否为免认证请求
     */
    protected function isExcludedRequest(Request $request): bool
    {
        $roleConfig = $this->roleManager->getRoleConfig($request->role);
        
        // 不需要鉴权的角色直接通过
        if (!$roleConfig['auth_required']) {
            Event::emit('role.auth.skipped', [
                'role' => $request->role,
                'reason' => 'auth_not_required'
            ]);
            return true;
        }
        
        // 检查是否为免认证路径
        if ($this->isExcludedPath($request)) {
            Event::emit('role.auth.skipped', [
                'role' => $request->role,
                'reason' => 'path_excluded'
            ]);
            return true;
        }
        
        // 检查控制器方法是否需要登录
        if ($this->isNoNeedLoginMethod($request)) {
            Event::emit('role.auth.skipped', [
                'role' => $request->role,
                'reason' => 'method_excluded'
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * 初始化角色上下文
     */
    protected function initializeRoleContext(string $role): void
    {
        // 获取成员上下文实例
        $context = RequestContext::get('member_context');
        if (!$context) {
            $context = Context::getInstance();
        }
        
        // 先激活角色，确保角色状态正确
        $this->roleManager->activateRole($role);
        
        // 然后切换到指定角色上下文
        $context->switchRole($role);
        
        Event::emit('role.auth.context_initialized', [
            'role' => $role,
            'active_roles' => $this->roleManager->getActiveRoles()
        ]);
    }
    
    /**
     * 验证角色访问权限
     */
    protected function validateRoleAccess(string $role, Request $request): void
    {
        if (!$this->roleManager->validateRoleAccess($role, $request)) {
            throw new UnauthorizedHttpException(
                '当前角色无权访问该路径', 
                StatusCode::ACCESS_DENIED
            );
        }
        
        Event::emit('role.auth.access_validated', [
            'role' => $role,
            'request_path' => $request->path(),
            'access_granted' => true
        ]);
    }
    
    /**
     * 处理鉴权逻辑（合并AuthMiddleware功能）
     */
    protected function handleAuthentication(string $role, Request $request): void
    {
        // 获取Token（使用缓存机制）
        $token = $request->token();
        if (!$token) {
            throw new UnauthorizedHttpException('请先登录', StatusCode::NEED_LOGIN);
        }
        
        // 验证Token角色一致性（合并Token验证和角色一致性检查）
        $payload = $this->validateTokenAndRoleConsistency($token, $role, $request);
        
        // 初始化用户信息（使用已验证的payload）
        $this->initializeUserInfo($role, $token, $payload, $request);
        
        Event::emit('role.auth.authentication_success', [
            'role' => $role,
            'token_valid' => true,
            'user_initialized' => true
        ]);
    }
    
    /**
     * 验证Token和角色一致性（合并Token验证和角色检查）
     */
    protected function validateTokenAndRoleConsistency(string $token, string $expectedRole, Request $request): \stdClass
    {
        try {
            $payload = \support\token\Token::verify($token);
            
            // 验证payload完整性
            if (!$this->validatePayload($payload)) {
                throw new UnauthorizedHttpException('无效的Token', StatusCode::TOKEN_INVALID);
            }
            
            if ($payload->role !== $expectedRole) {
                // 检查是否是其他活跃角色的Token
                $activeRoles = $this->roleManager->getActiveRoles();
                if (in_array($payload->role, $activeRoles)) {
                    // Token角色与请求路径不匹配，但属于活跃角色，记录日志但不阻止访问
                    Event::emit('role.auth.token_role_mismatch', [
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
            
            return $payload;
            
        } catch (\support\token\TokenExpiredException $e) {
            // Token过期时清理相关上下文
            $this->cleanupExpiredTokenContext($expectedRole, $request);
            throw new UnauthorizedHttpException('Token已过期', StatusCode::TOKEN_SHOULD_REFRESH);
        } catch (\Throwable $e) {
            // 其他Token验证失败时也清理上下文
            $this->cleanupExpiredTokenContext($expectedRole, $request);
            throw new UnauthorizedHttpException('Token验证失败: ' . $e->getMessage(), StatusCode::NEED_LOGIN);
        }
    }
    
    /**
     * 初始化用户信息（使用已验证的payload）
     */
    protected function initializeUserInfo(string $role, string $token, \stdClass $payload, Request $request): void
    {
        try {
            // 设置当前角色
            \support\member\Member::setCurrentRole($role);
            
            // 设置payload到请求对象，避免重复验证
            $request->payload = $payload;
            
            // 初始化用户信息
            \support\member\Member::initialization();
            
        } catch (\Throwable $e) {
            throw new UnauthorizedHttpException('用户信息初始化失败: ' . $e->getMessage(), StatusCode::NEED_LOGIN);
        }
    }
    
    /**
     * 请求完成后清理
     */
    protected function cleanupAfterRequest(string $role): void
    {
        // 保存角色上下文到RequestContext，供后续请求使用
        $context = RequestContext::get('member_context');
        if ($context) {
            // 将当前上下文保存到RequestContext中，供后续请求使用
            RequestContext::set('member_context', $context);
        }
        
        // 根据配置决定是否清理角色上下文
        $autoCleanup = $this->config['context']['auto_cleanup'] ?? false; // 默认不自动清理
        if ($autoCleanup) {
            // 延迟清理，避免影响后续请求
            $this->scheduleContextCleanup($role);
        }
        
        Event::emit('role.auth.request_completed', [
            'role' => $role,
            'context_saved' => true,
            'auto_cleanup_scheduled' => $autoCleanup
        ]);
    }
    
    /**
     * 清理过期Token的上下文
     */
    protected function cleanupExpiredTokenContext(string $role, Request $request): void
    {
        try {
            // 清理RequestContext中的成员信息
            RequestContext::delete('member');
            RequestContext::delete('role');
            
            // 清理角色管理器中的角色状态
            $this->roleManager->deactivateRole($role);
            
            // 记录清理事件
            Event::emit('role.auth.expired_token_cleanup', [
                'role' => $role,
                'request_path' => $request->path(),
                'timestamp' => microtime(true)
            ]);
            
        } catch (\Throwable $e) {
            // 清理失败时记录日志，但不抛出异常
            Event::emit('role.auth.cleanup_error', [
                'role' => $role,
                'error' => $e->getMessage(),
                'request_path' => $request->path()
            ]);
        }
    }
    
    /**
     * 调度上下文清理
     */
    protected function scheduleContextCleanup(string $role): void
    {
        $cleanupTimeout = $this->config['context']['cleanup_timeout'] ?? 300;
        
        // 在实际项目中，这里可以使用定时任务或延迟队列
        // 这里简化处理，直接记录日志
        Event::emit('role.auth.context_cleanup_scheduled', [
            'role' => $role,
            'timeout' => $cleanupTimeout
        ]);
    }
    
    /**
     * 检查是否为免认证路径
     */
    protected function isExcludedPath(Request $request): bool
    {
        $path = $request->path();
        $excludePaths = $this->config['exclude'] ?? [];
        
        foreach ($excludePaths as $excludePath) {
            if (strpos($excludePath, '*') !== false) {
                // 通配符匹配
                $pattern = str_replace('*', '.*', $excludePath);
                if (preg_match("#^" . $pattern . "$#", $path)) {
                    return true;
                }
            } elseif ($path === $excludePath) {
                // 精确匹配
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查控制器方法是否需要登录
     */
    protected function isNoNeedLoginMethod(Request $request): bool
    {
        try {
            if (!class_exists($request->controller)) {
                return false;
            }
            
            $reflection = new \ReflectionClass($request->controller);
            $noNeedLogin = $reflection->getDefaultProperties()['noNeedLogin'] ?? [];
            
            return in_array($request->action, $noNeedLogin);
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * 验证Token payload完整性
     */
    protected function validatePayload($payload): bool
    {
        return isset($payload->sub) && isset($payload->role) && 
               !empty($payload->sub) && !empty($payload->role);
    }
    
    /**
     * 获取当前活跃的角色列表
     */
    public static function getActiveRoles(): array
    {
        return Role::getInstance()->getActiveRoles();
    }
    
    /**
     * 强制清除指定角色的所有上下文
     */
    public static function forceClearRoleContext(string $role): bool
    {
        try {
            Role::getInstance()->cleanupRoleContext($role);
            return true;
        } catch (\Throwable $e) {
            Event::emit('role.auth.force_clear_failed', [
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 快速新增角色（运行时）
     */
    public static function addRole(string $roleKey, array $config): bool
    {
        return Role::getInstance()->addRole($roleKey, $config);
    }
    
    /**
     * 获取角色调试信息
     */
    public static function getDebugInfo(): array
    {
        return Role::getInstance()->getDebugInfo();
    }
}