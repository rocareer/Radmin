<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use support\member\role\admin\AdminService;
use support\member\role\user\UserService;

return [
    // 简化的成员服务（直接根据角色返回对应服务）
    'member.service' => function () {
        $role = request()->role ?? 'user';
        return $role === 'admin' ? new AdminService() : new UserService();
    },
];
