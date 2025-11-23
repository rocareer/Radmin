<?php
/**
 * 角色管理器接口
 * 职责：角色检测、验证、配置管理、访问控制
 * 不包含：认证逻辑、Token管理、状态管理
 */

namespace support\member\interface;

interface InterfaceRole
{
    /**
     * 检测当前用户角色
     * @return string 当前角色
     */
    public function detectCurrentRole(): string;

    /**
     * 验证用户角色权限
     * @param string $role 角色名称
     * @param string $permission 权限标识
     * @return bool 是否有权限
     */
    public function verifyRolePermission(string $role, string $permission): bool;

    /**
     * 获取角色配置
     * @param string $role 角色名称
     * @return array 角色配置
     */
    public function getRoleConfig(string $role): array;

    /**
     * 检查角色是否存在
     * @param string $role 角色名称
     * @return bool 是否存在
     */
    public function roleExists(string $role): bool;

    /**
     * 获取所有可用角色
     * @return array 角色列表
     */
    public function getAllRoles(): array;

    /**
     * 切换当前角色
     * @param string $role 目标角色
     * @return bool 切换结果
     */
    public function switchRole(string $role): bool;

    /**
     * 检查角色一致性
     * @param string $expectedRole 期望角色
     * @param string $actualRole 实际角色
     * @return bool 是否一致
     */
    public function checkRoleConsistency(string $expectedRole, string $actualRole): bool;

    /**
     * 清理角色上下文
     * @param string $role 角色名称
     * @return bool 清理结果
     */
    public function clearRoleContext(string $role): bool;
}