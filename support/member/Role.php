<?php
/**
 * 角色管理类
 * 统一管理角色配置、检测、验证、多角色上下文和隔离机制
 * 合并RoleManager和MultiRoleManager功能，消除冗余
 */

namespace support\member;

use app\exception\UnauthorizedHttpException;
use support\Request;
use support\RequestContext;
use support\StatusCode;
use Webman\Event\Event;

class Role
{
    protected static ?Role $instance = null;
    protected array $config;
    protected array $roleInstances = [];
    protected array $activeRoles = [];
    protected ?string $currentRole = null;

    private function __construct()
    {
        $this->config = config('roles', []);
        $this->initialize();
    }

    public static function getInstance(): Role
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 初始化角色管理器
     */
    protected function initialize(): void
    {
        // 加载配置验证
        if (empty($this->config['roles'])) {
            throw new \RuntimeException('角色配置未找到');
        }
        
        // 初始化默认角色
        $this->currentRole = $this->config['default'] ?? 'guest';
        
        \Webman\Event\Event::emit('role.manager.initialized', [
            'config' => $this->config,
            'default_role' => $this->currentRole,
            'timestamp' => microtime(true),
            'event_type' => 'initialization'
        ]);
    }

    /**
     * 根据请求路径检测角色
     */
    public function detectRoleByRequest(Request $request): string
    {
        $path = $request->path();
        
        // 1. 精确匹配
        foreach ($this->config['path_rules']['exact'] ?? [] as $exactPath => $role) {
            if ($path === $exactPath) {
                return $this->validateRole($role);
            }
        }
        
        // 2. 前缀匹配
        foreach ($this->config['path_rules']['prefix'] ?? [] as $prefix => $role) {
            if (str_starts_with($path, $prefix)) {
                return $this->validateRole($role);
            }
        }
        
        // 3. 默认角色
        return $this->validateRole($this->config['path_rules']['default'] ?? 'guest');
    }

    /**
     * 验证角色有效性
     */
    public function validateRole(string $role): string
    {
        if (!isset($this->config['roles'][$role])) {
            throw new UnauthorizedHttpException("无效的角色类型: {$role}", StatusCode::ACCESS_DENIED);
        }
        return $role;
    }

    /**
     * 验证角色是否存在（公共方法）
     */
    public function roleExists(string $role): bool
    {
        return isset($this->config['roles'][$role]);
    }

    /**
     * 获取角色配置
     */
    public function getRoleConfig(string $role): array
    {
        return $this->config['roles'][$role] ?? [];
    }

    /**
     * 获取所有角色配置
     */
    public function getAllRoles(): array
    {
        return $this->config['roles'] ?? [];
    }

    /**
     * 检查角色是否需要鉴权
     */
    public function requiresAuth(string $role): bool
    {
        $config = $this->getRoleConfig($role);
        return $config['auth_required'] ?? false;
    }

    /**
     * 获取角色服务实例
     */
    public function getRoleService(string $role): ?object
    {
        if (!$this->requiresAuth($role)) {
            return null; // 不需要鉴权的角色无服务实例
        }
        
        if (!isset($this->roleInstances[$role])) {
            $config = $this->getRoleConfig($role);
            $serviceClass = $config['service_class'] ?? null;
            
            if (!$serviceClass || !class_exists($serviceClass)) {
                throw new \RuntimeException("角色服务类不存在: {$serviceClass}");
            }
            
            $this->roleInstances[$role] = new $serviceClass();
        }
        
        return $this->roleInstances[$role];
    }

    /**
     * 设置当前角色
     */
    public function setCurrentRole(string $role): void
    {
        $this->currentRole = $this->validateRole($role);
        
        // 激活角色
        $this->activateRole($role);
        
        \Webman\Event\Event::emit('role.manager.role_changed', [
            'new_role' => $role,
            'previous_role' => $this->currentRole,
            'active_roles' => $this->activeRoles,
            'timestamp' => microtime(true),
            'event_type' => 'role_change'
        ]);
    }

    /**
     * 获取当前角色
     */
    public function getCurrentRole(): string
    {
        return $this->currentRole ?? $this->config['default'] ?? 'guest';
    }

    /**
     * 激活角色
     */
    public function activateRole(string $role): void
    {
        $role = $this->validateRole($role);
        
        if (!in_array($role, $this->activeRoles)) {
            // 检查最大并发角色数
            $maxConcurrent = $this->config['multi_role']['max_concurrent'] ?? 3;
            if (count($this->activeRoles) >= $maxConcurrent) {
                // 移除最不活跃的角色
                $this->deactivateOldestRole();
            }
            
            $this->activeRoles[] = $role;
            
            Event::emit('role.manager.role_activated', [
                'role' => $role,
                'active_roles' => $this->activeRoles,
                'timestamp' => microtime(true),
                'event_type' => 'role_activation'
            ]);
        }
    }

    /**
     * 停用最不活跃的角色
     */
    protected function deactivateOldestRole(): void
    {
        if (!empty($this->activeRoles)) {
            $oldestRole = array_shift($this->activeRoles);
            
            Event::emit('role.manager.role_deactivated', [
                'role' => $oldestRole,
                'reason' => 'max_concurrent_reached',
                'timestamp' => microtime(true),
                'event_type' => 'role_deactivation'
            ]);
        }
    }

    /**
     * 停用角色
     */
    public function deactivateRole(string $role): void
    {
        $role = $this->validateRole($role);
        $key = array_search($role, $this->activeRoles);
        
        if ($key !== false) {
            unset($this->activeRoles[$key]);
            $this->activeRoles = array_values($this->activeRoles);
            
            Event::emit('role.manager.role_deactivated', [
                'role' => $role,
                'reason' => 'manual_deactivation',
                'timestamp' => microtime(true),
                'event_type' => 'role_deactivation'
            ]);
        }
    }

    /**
     * 获取活跃角色列表
     */
    public function getActiveRoles(): array
    {
        return $this->activeRoles;
    }

    /**
     * 检查角色是否活跃
     */
    public function isRoleActive(string $role): bool
    {
        return in_array($role, $this->activeRoles);
    }

    /**
     * 验证角色访问权限
     */
    public function validateRoleAccess(string $role, Request $request): bool
    {
        $config = $this->getRoleConfig($role);
        
        // 检查路径权限
        $path = $request->path();
        $allowedPrefixes = $config['path_prefix'] ?? [];
        
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 清理角色上下文
     */
    public function cleanupRoleContext(string $role): void
    {
        $role = $this->validateRole($role);
        
        // 记录清理前的状态
        $wasActive = in_array($role, $this->activeRoles);
        $wasCurrent = $this->currentRole === $role;
        
        // 清理服务实例
        if (isset($this->roleInstances[$role])) {
            unset($this->roleInstances[$role]);
        }
        
        // 停用角色
        $this->deactivateRole($role);
        
        // 如果清理的是当前角色，重置当前角色
        if ($this->currentRole === $role) {
            $this->currentRole = $this->config['default'] ?? 'guest';
        }
        
        // 清理RequestContext中与该角色相关的数据
        \support\RequestContext::delete("role_{$role}_data");
        \support\RequestContext::delete("role_{$role}_token");
        
        Event::emit('role.manager.context_cleaned', [
            'role' => $role,
            'current_role' => $this->currentRole,
            'timestamp' => microtime(true),
            'event_type' => 'context_cleanup'
        ]);
    }

    /**
     * 获取角色中间件
     */
    public function getRoleMiddleware(string $role): array
    {
        $config = $this->getRoleConfig($role);
        return $config['middleware'] ?? [];
    }

    /**
     * 快速新增角色（运行时动态添加）
     */
    public function addRole(string $roleKey, array $config): bool
    {
        if (isset($this->config['roles'][$roleKey])) {
            return false; // 角色已存在
        }
        
        // 验证必要配置
        $requiredFields = ['name', 'auth_required', 'path_prefix'];
        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                throw new \InvalidArgumentException("新增角色配置缺少必要字段: {$field}");
            }
        }
        
        $this->config['roles'][$roleKey] = $config;
        
        Event::emit('role.manager.role_added', [
            'role' => $roleKey,
            'config' => $config,
            'timestamp' => microtime(true),
            'event_type' => 'role_addition'
        ]);
        
        return true;
    }

    /**
     * 获取调试信息
     */
    public function getDebugInfo(): array
    {
        return [
            'current_role' => $this->currentRole,
            'active_roles' => $this->activeRoles,
            'role_instances' => array_keys($this->roleInstances),
            'config_roles' => array_keys($this->config['roles'] ?? []),
            'multi_role_enabled' => $this->config['multi_role']['enabled'] ?? false
        ];
    }

    // ==================== MultiRoleManager 功能整合 ====================

    /**
     * 检查指定角色是否已登录
     */
    public function isRoleLoggedIn(string $role): bool
    {
        // 首先检查角色是否活跃
        if ($this->isRoleActive($role)) {
            return true;
        }
        
        // 检查上下文中是否有该角色的数据
        $context = RequestContext::get('member_context');
        if ($context && $context->hasRoleContext($role)) {
            $roleContext = $context->roleContexts[$role] ?? [];
            return !empty($roleContext['model']);
        }
        
        // 尝试从Token验证
        try {
            $token = request()->token();
            if ($token) {
                $payload = \support\token\Token::verify($token);
                return $payload->role === $role;
            }
        } catch (\Throwable $e) {
            // Token验证失败，继续其他检查
        }
        
        return false;
    }
    
    /**
     * 获取指定角色的成员信息
     */
    public function getRoleMember(string $role): ?object
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
    public function logoutRole(string $role): bool
    {
        try {
            $context = RequestContext::get('member_context');
            if (!$context || !$context->hasRoleContext($role)) {
                return false;
            }
            
            // 清理角色上下文
            $context->clearRoleContext($role);
            
            // 记录注销日志 - 规范化事件格式
            Event::emit("log.authentication.{$role}.logout.by_manager", [
                'role' => $role,
                'active_roles' => $context->getActiveRoles(),
                'timestamp' => microtime(true),
                'event_type' => 'logout',
                'reason' => 'manager_action'
            ]);
            
            return true;
            
        } catch (\Throwable $e) {
            Event::emit("log.authentication.{$role}.logout.failure", [
                'role' => $role,
                'error' => $e->getMessage(),
                'timestamp' => microtime(true),
                'event_type' => 'logout',
                'reason' => 'error'
            ]);
            return false;
        }
    }
    
    /**
     * 强制注销所有角色的登录
     */
    public function logoutAllRoles(): bool
    {
        try {
            $context = RequestContext::get('member_context');
            if (!$context) {
                return true;
            }
            
            $activeRoles = $context->getActiveRoles();
            
            // 清理所有角色上下文
            $context->clearAllRoleContexts();
            
            // 清理成员上下文
            Context::getInstance()->clear();
            
            // 记录强制注销日志 - 规范化事件格式
            Event::emit('log.authentication.force_logout_all', [
                'active_roles' => $activeRoles,
                'timestamp' => microtime(true),
                'event_type' => 'logout',
                'reason' => 'force_all'
            ]);
            
            return true;
            
        } catch (\Throwable $e) {
            Event::emit('log.authentication.force_logout_all.failure', [
                'error' => $e->getMessage(),
                'timestamp' => microtime(true),
                'event_type' => 'logout',
                'reason' => 'force_all_error'
            ]);
            return false;
        }
    }
    
    /**
     * 获取多角色登录状态统计
     */
    public function getLoginStatus(): array
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
            if ($this->isRoleLoggedIn($role)) {
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
    public function validateTokenRoleConsistency(string $token, string $expectedRole): bool
    {
        try {
            $payload = \support\token\Token::verify($token);
            return $payload->role === $expectedRole;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ==================== 静态方法兼容层 ====================
    
    /**
     * 静态方法：获取当前活跃的所有角色
     */
    public static function getActiveRolesStatic(): array
    {
        return self::getInstance()->getActiveRoles();
    }
    
    /**
     * 静态方法：检查指定角色是否已登录
     */
    public static function isRoleLoggedInStatic(string $role): bool
    {
        return self::getInstance()->isRoleLoggedIn($role);
    }
    
    /**
     * 静态方法：获取指定角色的成员信息
     */
    public static function getRoleMemberStatic(string $role): ?object
    {
        return self::getInstance()->getRoleMember($role);
    }
    
    /**
     * 静态方法：注销指定角色的登录
     */
    public static function logoutRoleStatic(string $role): bool
    {
        return self::getInstance()->logoutRole($role);
    }
    
    /**
     * 静态方法：强制注销所有角色的登录
     */
    public static function logoutAllRolesStatic(): bool
    {
        return self::getInstance()->logoutAllRoles();
    }
    
    /**
     * 静态方法：获取多角色登录状态统计
     */
    public static function getLoginStatusStatic(): array
    {
        return self::getInstance()->getLoginStatus();
    }
    
    /**
     * 静态方法：验证Token角色一致性
     */
    public static function validateTokenRoleConsistencyStatic(string $token, string $expectedRole): bool
    {
        return self::getInstance()->validateTokenRoleConsistency($token, $expectedRole);
    }
}