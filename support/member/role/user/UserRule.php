<?php
/**
 * File:        UserRule.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/23 14:17
 * Description: 用户权限规则管理器
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace support\member\role\user;

class UserRule extends \support\member\Rule
{
    /**
     * 构造函数
     * 
     * @param string $role 角色类型
     * @param object $memberModel 用户模型实例
     */
    public function __construct(string $role, object $memberModel)
    {
        parent::__construct($role, $memberModel);
        
        // 用户使用默认配置
        $this->config = [
            'auth_group'        => 'user_group', // 用户组数据表名
            'auth_group_access' => '', // 用户-用户组关系表
            'auth_rule'         => 'user_rule', // 用户权限规则表
        ];
    }
}