<?php
/**
 * 状态管理器接口
 * 职责：状态检查、缓存管理、登录日志记录
 * 不包含：认证逻辑、角色管理、Token管理
 */

namespace support\member\interface;

interface InterfaceState
{
    /**
     * 检查用户状态
     * @param object $member 用户对象
     * @param string $checkType 检查类型
     * @return bool 状态检查结果
     */
    public function checkStatus(object $member, string $checkType = 'login'): bool;


}