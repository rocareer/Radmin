<?php
/**
 * File:        AdminRule.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/23 14:17
 * Description: 管理员权限规则管理器
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace support\member\role\admin;

class AdminRule extends \support\member\Rule
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
        
        // 管理员使用特定的配置
        $this->config = [
            'auth_group'        => 'admin_group', // 管理员组数据表名
            'auth_group_access' => 'admin_group_access', // 管理员-管理员组关系表
            'auth_rule'         => 'admin_rule', // 管理员权限规则表
        ];
    }
}