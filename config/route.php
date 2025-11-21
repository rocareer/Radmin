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

use Webman\Route;

/**
 * Api 路由组 将原buildadmin 前台api路由改为单独user应用 方便后续扩展
 */

// 用户相关路由组 - 专门处理前台用户功能
Route::group('/user', function () {
    // 用户登录
    Route::post('/login', [app\user\controller\Index::class, 'login']);
    
    // 用户注销
    Route::post('/logout', [app\user\controller\Index::class, 'logout']);
    
    // 用户信息获取
    Route::get('/profile', [app\user\controller\Index::class, 'profile']);
    
    // 用户注册
    Route::post('/register', [app\user\controller\Index::class, 'register']);
});

// API通用路由组 - 处理通用API请求
Route::group('/api', function () {
    // 系统初始化
    Route::get('/index', [app\api\controller\Index::class, 'index']);
});