<?php
/**
 * 认证器接口
 * 职责：用户认证、Token生成、登录信息管理
 * 不包含：角色管理、状态管理、Token驱动管理
 */

namespace support\member\interface;

use app\exception\BusinessException;

interface InterfaceAuthenticator
{
    /**
     * 认证用户凭证
     * @param array $credentials 用户凭证数据
     * @return object 认证成功的用户对象
     * @throws BusinessException
     */
    public function authenticate(array $credentials): object;

    /**
     * 扩展用户信息
     * @return void
     * @throws BusinessException
     */
    public function extendMemberInfo(): void;

    /**
     * 注销用户
     * @param object $member 用户对象
     * @param string $role 用户角色
     * @return bool 注销结果
     * @throws BusinessException
     */
    public function logout(object $member, string $role): bool;
}
