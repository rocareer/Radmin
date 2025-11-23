<?php
/**
 * 前台用户状态管理
 * 简化状态检查，减少冗余逻辑
 */

namespace support\member\role\user;

use support\member\State;

class UserState extends State
{
    public string $role = 'user';

    /**
     * @var string 登录日志表名
     */
    protected static string $loginLogTable = 'login_log_user';

    /**
     * 检查扩展状态 - 简化实现
     */
    protected function checkExtendStatus(): void
    {
        // 前台用户无需复杂的扩展状态检查
    }
}
