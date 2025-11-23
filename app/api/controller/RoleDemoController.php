<?php
/**
 * 角色系统演示控制器
 * 展示如何使用新的角色鉴权系统
 */

namespace app\api\controller;

use app\controller\BaseController;
use support\Request;
use support\Response;
use support\member\Role;
use app\middleware\RoleAuthMiddleware;

class RoleDemoController extends BaseController
{
    /**
     * 获取当前角色信息
     */
    public function getCurrentRoleInfo(Request $request): Response
    {
        $roleManager = Role::getInstance();
        $currentRole = $request->role;
        
        $data = [
            'current_role' => $currentRole,
            'role_config' => $roleManager->getRoleConfig($currentRole),
            'active_roles' => $roleManager->getActiveRoles(),
            'debug_info' => $roleManager->getDebugInfo(),
            'request_path' => $request->path(),
            'requires_auth' => $roleManager->requiresAuth($currentRole)
        ];
        
        return $this->success($data);
    }
    
    /**
     * 获取所有角色配置
     */
    public function getAllRoles(): Response
    {
        $roleManager = Role::getInstance();
        $allRoles = $roleManager->getAllRoles();
        
        return $this->success([
            'roles' => $allRoles,
            'total' => count($allRoles)
        ]);
    }
    
    /**
     * 动态新增角色（演示）
     */
    public function addRole(Request $request): Response
    {
        $roleKey = $request->input('role_key');
        $config = $request->input('config');
        
        if (!$roleKey || !$config) {
            return $this->error('参数错误：role_key和config不能为空');
        }
        
        try {
            $success = RoleAuthMiddleware::addRole($roleKey, $config);
            
            if ($success) {
                return $this->success([
                    'message' => '角色添加成功',
                    'role_key' => $roleKey,
                    'config' => $config
                ]);
            } else {
                return $this->error('角色已存在');
            }
        } catch (\Throwable $e) {
            return $this->error('角色添加失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 切换当前角色（演示）
     */
    public function switchRole(Request $request): Response
    {
        $newRole = $request->input('role');
        
        if (!$newRole) {
            return $this->error('参数错误：role不能为空');
        }
        
        try {
            $roleManager = Role::getInstance();
            $roleManager->setCurrentRole($newRole);
            
            return $this->success([
                'message' => '角色切换成功',
                'previous_role' => $request->role,
                'current_role' => $newRole,
                'active_roles' => $roleManager->getActiveRoles()
            ]);
        } catch (\Throwable $e) {
            return $this->error('角色切换失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 清理角色上下文
     */
    public function clearRoleContext(Request $request): Response
    {
        $role = $request->input('role', $request->role);
        
        try {
            $success = RoleAuthMiddleware::forceClearRoleContext($role);
            
            if ($success) {
                return $this->success([
                    'message' => '角色上下文清理成功',
                    'role' => $role
                ]);
            } else {
                return $this->error('角色上下文清理失败');
            }
        } catch (\Throwable $e) {
            return $this->error('清理失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 测试多角色登录（演示）
     */
    public function testMultiRoleLogin(): Response
    {
        $roleManager = Role::getInstance();
        
        // 模拟多角色登录场景
        $activeRoles = $roleManager->getActiveRoles();
        $currentRole = $roleManager->getCurrentRole();
        
        $data = [
            'current_role' => $currentRole,
            'active_roles' => $activeRoles,
            'multi_role_enabled' => $roleManager->getDebugInfo()['multi_role_enabled'],
            'max_concurrent' => config('roles.multi_role.max_concurrent', 3),
            'scenario' => '多角色同时登录演示'
        ];
        
        return $this->success($data);
    }
    
    /**
     * 获取角色中间件信息
     */
    public function getRoleMiddleware(Request $request): Response
    {
        $roleManager = Role::getInstance();
        $currentRole = $request->role;
        
        $middleware = $roleManager->getRoleMiddleware($currentRole);
        
        return $this->success([
            'role' => $currentRole,
            'middleware' => $middleware,
            'auth_required' => $roleManager->requiresAuth($currentRole)
        ]);
    }
    
    /**
     * 公共接口（guest角色可访问）
     */
    public function publicEndpoint(): Response
    {
        return $this->success([
            'message' => '这是公共接口，无需登录即可访问',
            'current_role' => request()->role,
            'auth_required' => false
        ]);
    }
    
    /**
     * 需要登录的接口
     */
    public function protectedEndpoint(): Response
    {
        $member = request()->member;
        
        return $this->success([
            'message' => '这是需要登录的接口',
            'current_role' => request()->role,
            'user_info' => $member ? [
                'id' => $member->id,
                'username' => $member->username
            ] : null,
            'auth_required' => true
        ]);
    }
}