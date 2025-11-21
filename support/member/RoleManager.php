<?php
/**
 * 角色管理器
 * 统一管理角色配置、检测、验证和上下文管理
 */

namespace support\member;

use app\exception\UnauthorizedHttpException;
use support\Request;
use support\RequestContext;
use support\StatusCode;
use Webman\Event\Event;

class RoleManager
{
    protected static ?RoleManager $instance = null;
    protected array $config;
    protected array $roleInstances = [];
    protected array $activeRoles = [];
    protected ?string $currentRole = null;

    private function __construct()
    {
        $this->config = config('roles', []);
        $this->initialize();
    }

    public static function getInstance(): RoleManager
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
        
        Event::emit('role.manager.initialized', [
            'config' => $this->config,
            'default_role' => $this->currentRole
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
    protected function validateRole(string $role): string
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
            'active_roles' => $this->activeRoles
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
                'active_roles' => $this->activeRoles
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
                'reason' => 'max_concurrent_reached'
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
                'reason' => 'manual_deactivation'
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
        
        Event::emit('role.manager.context_cleaned', [
            'role' => $role,
            'current_role' => $this->currentRole
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
            'config' => $config
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
}