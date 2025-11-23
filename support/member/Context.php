<?php

namespace support\member;

use app\exception\UnauthorizedHttpException;
use support\Log;
use support\RequestContext;
use support\StatusCode;
use Webman\Event\Event;

/**
 * 成员上下文管理器
 * 
 * 职责：
 * 1. 管理成员认证状态和角色信息
 * 2. 提供多角色切换和隔离功能
 * 3. 处理成员相关的业务逻辑
 * 4. 不涉及请求级别的简单数据存储
 */
class Context
{
    /**
     * @var Context|null 单例实例
     */
    protected static ?Context $instance = null;
    
    /**
     * @var RoleManager 角色管理器
     */
    protected Role $roleManager;
    
    /**
     * @var bool 调试模式
     */
    protected bool $debugMode = false;
    
    /**
     * @var array 调试信息缓存
     */
    protected array $debugCache = [];
    
    /**
     * 私有构造函数
     */
    private function __construct()
    {
        $this->roleManager = Role::getInstance();
        $this->debugMode = config('app.debug', false);
        
        // 初始化调试事件监听
        $this->initializeDebugListeners();
    }
    
    /**
     * 获取单例实例
     */
    public static function getInstance(): Context
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 初始化调试事件监听器
     */
    protected function initializeDebugListeners(): void
    {
        if ($this->debugMode) {
            // 认证相关事件
            Event::on('member.login_success', function ($data) {
                $this->logDebugEvent('member.login_success', $data);
            });
            Event::on('member.login_failure', function ($data) {
                $this->logDebugEvent('member.login_failure', $data);
            });

            // 角色管理事件
            Event::on('member.context.role_switched', function ($data) {
                $this->logDebugEvent('member.context.role_switched', $data);
            });
        }
    }

    /**
     * 清空成员上下文
     */
    public function clear(): void
    {
        // 清除RequestContext中的成员信息
        RequestContext::delete('member');
        RequestContext::delete('role');
        RequestContext::delete('member_context');
        
        // 停用当前角色
        $currentRole = $this->getCurrentRole();
        if ($currentRole !== 'guest') {
            $this->roleManager->deactivateRole($currentRole);
        }
        
        // 清理角色管理器中的所有活跃角色
        $activeRoles = $this->getActiveRoles();
        foreach ($activeRoles as $role) {
            $this->roleManager->deactivateRole($role);
        }
        
        // 清空调试缓存
        $this->debugCache = [];
        
        // 记录清理事件
        Event::emit('member.context.cleared', [
            'previous_role' => $currentRole,
            'active_roles_cleared' => $activeRoles,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * 切换当前角色
     */
    public function switchRole(string $role): bool
    {
        try {
            $role = $this->roleManager->validateRole($role);
            
            // 记录调试信息
            $currentRole = $this->getCurrentRole();
            $activeRoles = $this->getActiveRoles();
            
            // 如果已经是当前角色，直接返回成功
            if ($currentRole === $role) {
                Event::emit('member.context.role_switch_skipped', [
                    'role' => $role,
                    'reason' => 'already_current',
                    'active_roles' => $activeRoles,
                    'timestamp' => microtime(true)
                ]);
                return true;
            }
            
            // 检查角色是否已登录
            if (!$this->isLoggedIn($role)) {
                // 记录详细的调试信息
                Event::emit('member.context.role_switch_failed', [
                    'attempted_role' => $role,
                    'current_role' => $currentRole,
                    'active_roles' => $activeRoles,
                    'is_role_active' => $this->roleManager->isRoleActive($role),
                    'token_exists' => !empty(request()->token()),
                    'timestamp' => microtime(true)
                ]);
                
                throw new UnauthorizedHttpException("角色未登录: {$role}", StatusCode::ACCESS_DENIED);
            }

            // 切换角色
            $this->roleManager->setCurrentRole($role);
            
            // 更新请求上下文中的角色信息
            RequestContext::set('role', $role);
            
            // 记录角色切换事件
            Event::emit('member.context.role_switched', [
                'from_role' => $currentRole,
                'to_role' => $role,
                'active_roles' => $this->getActiveRoles(),
                'timestamp' => microtime(true),
                'event_type' => 'role_switch'
            ]);
            
            return true;
        } catch (\Throwable $e) {
            $this->logError('switchRole', $e);
            return false;
        }
    }
    
    /**
     * 清除指定角色的上下文
     */
    public function clearRoleContext(string $role): void
    {
        // 记录清理前的状态
        $wasCurrentRole = $this->getCurrentRole() === $role;
        
        // 使用 Role 统一清理
        Role::getInstance()->cleanupRoleContext($role);
        
        // 清理RequestContext中与该角色相关的数据
        RequestContext::delete("role_{$role}_data");
        RequestContext::delete("role_{$role}_token");
        RequestContext::delete("current_{$role}_context");
        
        // 如果当前角色是被清除的角色，重置当前上下文
        if ($wasCurrentRole) {
            $this->clear();
        }
        
        // 记录清理事件
        Event::emit('member.context.role_cleared', [
            'role' => $role,
            'was_current_role' => $wasCurrentRole,
            'current_role_after' => $this->getCurrentRole(),
            'timestamp' => microtime(true)
        ]);
    }
    
    /**
     * 清除所有角色上下文
     */
    public function clearAllRoleContexts(): void
    {
        // 获取所有活跃角色
        $activeRoles = $this->getActiveRoles();
        
        // 记录清理前的状态
        Event::emit('member.context.all_roles_clearing', [
            'active_roles' => $activeRoles,
            'timestamp' => microtime(true)
        ]);
        
        // 清理每个角色的上下文
        foreach ($activeRoles as $role) {
            try {
                Role::getInstance()->cleanupRoleContext($role);
            } catch (\Throwable $e) {
                // 记录清理失败，但继续清理其他角色
                Event::emit('member.context.role_cleanup_failed', [
                    'role' => $role,
                    'error' => $e->getMessage(),
                    'timestamp' => microtime(true)
                ]);
            }
        }
        
        // 最后清理整个上下文
        $this->clear();
        
        // 记录清理完成事件
        Event::emit('member.context.all_roles_cleared', [
            'cleared_roles' => $activeRoles,
            'timestamp' => microtime(true)
        ]);
    }
    
    /**
     * 获取当前活跃的角色列表
     */
    public function getActiveRoles(): array
    {
        return $this->roleManager->getActiveRoles();
    }
    
    /**
     * 检查角色上下文是否存在
     */
    public function hasRoleContext(string $role): bool
    {
        return $this->roleManager->isRoleActive($role);
    }
    
    /**
     * 获取当前用户信息
     */
    public function getCurrentMember(): ?object
    {
        try {
            // 直接从RequestContext获取，避免多层包装
            return RequestContext::get('member');
        } catch (\Throwable $e) {
            $this->logError('getCurrentMember', $e);
            return null;
        }
    }

    /**
     * 获取指定角色的用户信息
     */
    public function getMemberByRole(string $role): ?object
    {
        try {
            $role = $this->roleManager->validateRole($role);
            
            // 检查当前角色是否匹配
            $currentRole = $this->getCurrentRole();
            if ($currentRole === $role) {
                return $this->getCurrentMember();
            }
            
            // 对于非当前角色，通过Role获取
            return Role::getInstance()->getRoleMember($role);
        } catch (\Throwable $e) {
            $this->logError('getMemberByRole', $e);
            return null;
        }
    }

    /**
     * 检查用户是否已登录
     */
    public function isLoggedIn(?string $role = null): bool
    {
        try {
            $checkRole = $role ?: $this->getCurrentRole();
            
            if (!$this->roleManager->requiresAuth($checkRole)) {
                return true; // 不需要鉴权的角色默认视为已登录
            }

            // 优先检查角色是否活跃
            if ($this->roleManager->isRoleActive($checkRole)) {
                return true;
            }
            
            // 检查当前角色或指定角色是否登录
            if ($checkRole === $this->getCurrentRole()) {
                // 尝试从RequestContext获取成员信息
                $member = RequestContext::get('member');
                if (!empty($member)) {
                    return true;
                }
                
                // 如果RequestContext中没有，尝试从Token验证
                try {
                    $token = request()->token();
                    if ($token) {
                        $payload = \support\token\Token::verify($token);
                        return $payload->role === $checkRole;
                    }
                } catch (\Throwable $e) {
                    // Token验证失败，继续其他检查
                }
            }
            
            // 最后检查Role管理器中的角色状态
            return Role::getInstance()->isRoleLoggedIn($checkRole);
        } catch (\Throwable $e) {
            $this->logError('isLoggedIn', $e);
            return false;
        }
    }

    /**
     * 获取用户ID
     */
    public function getId(?string $role = null): ?int
    {
        $member = $this->getMemberByRole($role ?: $this->getCurrentRole());
        return $member ? ($member->id ?? null) : null;
    }

    /**
     * 获取用户名
     */
    public function getUsername(?string $role = null): ?string
    {
        $member = $this->getMemberByRole($role ?: $this->getCurrentRole());
        return $member ? ($member->username ?? $member->name ?? null) : null;
    }

    /**
     * 获取用户邮箱
     */
    public function getEmail(?string $role = null): ?string
    {
        $member = $this->getMemberByRole($role ?: $this->getCurrentRole());
        return $member ? ($member->email ?? null) : null;
    }

    /**
     * 获取当前角色
     */
    public function getCurrentRole(): string
    {
        return $this->roleManager->getCurrentRole();
    }

    /**
     * 设置当前用户信息
     */
    public function setCurrentMember(object $member, string $role): void
    {
        try {
            // 直接存储到RequestContext，简化架构
            RequestContext::set('member', $member);
            RequestContext::set('role', $role);
            
            // 激活角色
            $this->roleManager->activateRole($role);
            $this->roleManager->setCurrentRole($role);
            
        } catch (\Throwable $e) {
            $this->logError('setCurrentMember', $e);
        }
    }

    /**
     * 获取成员上下文状态信息
     */
    public function getContextStatus(): array
    {
        return [
            'current_role' => $this->getCurrentRole(),
            'active_roles' => $this->getActiveRoles(),
            'current_member_exists' => !empty($this->getCurrentMember()),
            'debug_mode' => $this->debugMode,
            'debug_events_count' => array_sum(array_map('count', $this->debugCache)),
            'role_manager_status' => $this->roleManager->getDebugInfo(),
            'multi_role_status' => Role::getInstance()->getLoginStatus(),
            'request_info' => RequestContext::getRequestInfo()
        ];
    }

    /**
     * 获取调试信息
     */
    public function getDebugInfo(): array
    {
        return [
            'context_status' => $this->getContextStatus(),
            'debug_events' => $this->debugCache,
            'active_members' => $this->getActiveMembersInfo(),
            'performance_metrics' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * 记录调试事件
     */
    protected function logDebugEvent(string $event, array $data): void
    {
        // 规范化事件数据格式
        $normalizedData = $this->normalizeEventData($event, $data);
        
        $this->debugCache[$event][] = [
            'timestamp' => microtime(true),
            'data' => $normalizedData,
            'memory_usage' => memory_get_usage(true),
            'active_roles' => $this->getActiveRoles(),
            'current_role' => $this->getCurrentRole()
        ];

        // 限制调试缓存大小
        if (count($this->debugCache[$event]) > 100) {
            array_shift($this->debugCache[$event]);
        }

        // 根据事件类型选择日志级别
        $logLevel = $this->getEventLogLevel($event);
        Log::log($logLevel, "Context Event: {$event}", $normalizedData);
    }

    /**
     * 规范化事件数据
     */
    protected function normalizeEventData(string $event, array $data): array
    {
        $normalized = [];
        
        // 根据事件类型进行数据规范化
        switch ($event) {
            case 'member.login_success':
            case 'member.login_failure':
                $normalized = [
                    'member_id' => $data['member']->id ?? null,
                    'role' => $data['role'] ?? null,
                    'success' => $data['success'] ?? false,
                    'timestamp' => microtime(true)
                ];
                break;
                
            case 'member.context.role_switched':
                $normalized = [
                    'from_role' => $data['from_role'] ?? null,
                    'to_role' => $data['to_role'] ?? null,
                    'active_roles' => $data['active_roles'] ?? [],
                    'timestamp' => microtime(true)
                ];
                break;
                
            default:
                // 移除敏感信息，保留关键数据
                $normalized = array_filter($data, function($key) {
                    return !in_array($key, ['password', 'token', 'secret_key']);
                }, ARRAY_FILTER_USE_KEY);
                $normalized['timestamp'] = microtime(true);
        }
        
        return $normalized;
    }

    /**
     * 获取事件对应的日志级别
     */
    protected function getEventLogLevel(string $event): string
    {
        $errorEvents = ['member.login_failure'];
        $warningEvents = [];
        
        foreach ($errorEvents as $pattern) {
            if (fnmatch($pattern, $event)) {
                return 'error';
            }
        }
        
        foreach ($warningEvents as $pattern) {
            if (fnmatch($pattern, $event)) {
                return 'warning';
            }
        }
        
        return 'debug';
    }

    /**
     * 获取活跃成员信息
     */
    protected function getActiveMembersInfo(): array
    {
        $activeRoles = $this->getActiveRoles();
        $members = [];

        foreach ($activeRoles as $role) {
            $member = $this->getMemberByRole($role);
            if ($member) {
                $members[$role] = [
                    'id' => $member->id ?? null,
                    'username' => $member->username ?? $member->name ?? null,
                    'email' => $member->email ?? null,
                    'last_activity' => $member->last_login_time ?? null
                ];
            }
        }

        return $members;
    }

    /**
     * 获取性能指标
     */
    protected function getPerformanceMetrics(): array
    {
        return [
            'context_switches' => count($this->debugCache['member.context.role_switched'] ?? []),
            'authentication_events' => count($this->debugCache['member.login_success'] ?? []) + count($this->debugCache['member.login_failure'] ?? []),
        ];
    }

    /**
     * 记录错误日志
     */
    protected function logError(string $method, \Throwable $e): void
    {
        Log::error("Context Error in {$method}: " . $e->getMessage(), [
            'method' => $method,
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString(),
            'current_role' => $this->getCurrentRole(),
            'active_roles' => $this->getActiveRoles()
        ]);
    }

    /**
     * 启用调试模式
     */
    public function enableDebugMode(): void
    {
        $this->debugMode = true;
        $this->initializeDebugListeners();
    }

    /**
     * 禁用调试模式
     */
    public function disableDebugMode(): void
    {
        $this->debugMode = false;
        $this->debugCache = [];
    }

    /**
     * 清理调试缓存
     */
    public function clearDebugCache(): void
    {
        $this->debugCache = [];
    }

    /**
     * 魔术方法调用，支持链式操作
     */
    public function __call($method, $args)
    {
        // 支持直接调用角色管理器方法
        if (method_exists($this->roleManager, $method)) {
            return $this->roleManager->{$method}(...$args);
        }

        throw new \BadMethodCallException("Method {$method} not found in Context");
    }

    /**
     * 静态方法调用，提供便捷访问
     */
    public static function __callStatic($method, $args)
    {
        $instance = self::getInstance();
        
        if (method_exists($instance, $method)) {
            return $instance->{$method}(...$args);
        }

        throw new \BadMethodCallException("Static method {$method} not found in Context");
    }
}