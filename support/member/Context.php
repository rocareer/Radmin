<?php

namespace support\member;

use plugin\admin\app\model\Role;
use support\RequestContext;

/**
 * 成员上下文管理器
 *
 * 用于管理成员实例的生命周期，支持多角色隔离
 */
class Context
{
    protected array $data = [];
    /**
     * @var mixed|null
     */
    public mixed $role = null;
    public mixed $model = null;
    public mixed $state = null;
    public mixed $authenticator = null;
    public mixed $service = null;
    
    /**
     * 按角色存储的上下文数据
     */
    protected array $roleContexts = [];

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function clear(): void
    {
        $this->data          = []; // 清空数据
        $this->role          = null;
        $this->service       = null;
        $this->model         = null;
        $this->state         = null;
        $this->authenticator = null;
        
        // 清除当前角色的上下文
        if ($this->role) {
            unset($this->roleContexts[$this->role]);
        }
    }

    public function role($role)
    {
        $this->role = $role;
        
        // 初始化角色上下文
        if (!isset($this->roleContexts[$role])) {
            $this->roleContexts[$role] = [
                'model' => null,
                'state' => null,
                'authenticator' => null,
                'service' => null,
                'data' => []
            ];
        }
        
        // 加载角色上下文
        $this->loadRoleContext($role);
    }
    
    /**
     * 设置角色特定的数据
     */
    public function setRoleData(string $key, $value): void
    {
        if ($this->role) {
            $this->roleContexts[$this->role]['data'][$key] = $value;
        }
    }
    
    /**
     * 获取角色特定的数据
     */
    public function getRoleData(string $key)
    {
        if ($this->role && isset($this->roleContexts[$this->role]['data'][$key])) {
            return $this->roleContexts[$this->role]['data'][$key];
        }
        return null;
    }
    
    /**
     * 加载角色上下文
     */
    protected function loadRoleContext(string $role): void
    {
        if (isset($this->roleContexts[$role])) {
            $context = $this->roleContexts[$role];
            $this->model = $context['model'];
            $this->state = $context['state'];
            $this->authenticator = $context['authenticator'];
            $this->service = $context['service'];
        }
    }
    
    /**
     * 保存角色上下文
     */
    public function saveRoleContext(): void
    {
        if ($this->role) {
            $this->roleContexts[$this->role] = [
                'model' => $this->model,
                'state' => $this->state,
                'authenticator' => $this->authenticator,
                'service' => $this->service,
                'data' => $this->roleContexts[$this->role]['data'] ?? []
            ];
        }
    }
    
    /**
     * 切换角色上下文
     */
    public function switchRole(string $newRole): void
    {
        // 保存当前角色上下文
        $this->saveRoleContext();
        
        // 切换到新角色
        $this->role($newRole);
        
        // 记录角色切换日志
        \Webman\Event\Event::emit('member.context.role_switched', [
            'from_role' => $this->role,
            'to_role' => $newRole,
            'active_roles' => $this->getActiveRoles()
        ]);
    }
    
    /**
     * 清除指定角色的上下文
     */
    public function clearRoleContext(string $role): void
    {
        // 使用 RoleManager 统一清理
        \support\member\RoleManager::getInstance()->cleanupRoleContext($role);
        
        // 如果当前角色是被清除的角色，重置当前上下文
        if ($this->role === $role) {
            $this->clear();
        }
    }
    
    /**
     * 清除所有角色上下文
     */
    public function clearAllRoleContexts(): void
    {
        // 清理所有活跃角色
        $activeRoles = $this->getActiveRoles();
        foreach ($activeRoles as $role) {
            \support\member\RoleManager::getInstance()->cleanupRoleContext($role);
        }
        
        $this->roleContexts = [];
        $this->clear();
    }
    
    /**
     * 获取当前上下文状态信息（用于调试）
     */
    public function getContextStatus(): array
    {
        return [
            'current_role' => $this->role,
            'active_roles' => $this->getActiveRoles(),
            'role_contexts_count' => count($this->roleContexts),
            'data_keys' => array_keys($this->data),
            'has_model' => !empty($this->model),
            'has_state' => !empty($this->state),
            'has_authenticator' => !empty($this->authenticator),
            'has_service' => !empty($this->service),
        ];
    }
    
    /**
     * 获取当前活跃的角色列表
     */
    public function getActiveRoles(): array
    {
        return \support\member\RoleManager::getInstance()->getActiveRoles();
    }
    
    /**
     * 检查角色上下文是否存在
     */
    public function hasRoleContext(string $role): bool
    {
        return \support\member\RoleManager::getInstance()->isRoleActive($role);
    }
}