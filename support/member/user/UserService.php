<?php


/**
 * File      UserService.php
 * Author    albert@rocareer.com
 * Time      2025-05-06 06:05:12
 * Describe  UserService.php
 */

namespace support\member\user;

use support\member\Service;

class UserService extends Service
{
    protected string $role = 'user';

    protected array $config=[
        'auth_group'        => 'user_group', // 用户组数据表名
        'auth_group_access' => '', // 用户-用户组关系表
        'auth_rule'         => 'user_rule', // 权限规则表
    ];
    
    /**
     * 附加用户信息
     * By albert  2025/05/08 18:23:55
     * @return void
     */
    public function extendMemberInfo(): void
    {
        parent::extendMemberInfo();
    }

    /**
     * 获取前台用户菜单 - 重写基类方法，确保正确获取前台菜单
     * @param int|null $uid 用户ID
     * @return array
     */
    public function getMenus(?int $uid = null): array
    {
        try {
            // 确保用户已登录
            if (empty($this->memberModel) && !empty($uid)) {
                $this->memberModel = $this->createModel($this->role);
                $this->memberModel = $this->memberModel->findById($uid);
            }

            if (empty($this->memberModel)) {
                return [];
            }

            return parent::getMenus($uid);

        } catch (\Throwable $e) {
            // 记录错误日志但不抛出异常，避免影响用户体验
            \Webman\Event\Event::emit('user.menu.get.error', [
                'uid' => $uid,
                'error' => $e->getMessage(),
                'role' => $this->role
            ]);
            return [];
        }
    }

}