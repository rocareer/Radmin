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

use app\user\controller\Account;
use app\user\controller\Index;
use Webman\Route;


/**
 * Api 路由组 将原buildadmin 前台api路由改为单独user应用 方便后续扩展
 */
Route::group('/api', function () {

    Route::group('/user', function () {
        Route::any('/checkIn', [Index::class, 'login']);
        Route::any('/logout', [Index::class, 'logout']);
    });

    Route::group('/account', function () {
        Route::any('/balance', [Account::class, 'balance']);
        Route::any('/profile', [Account::class, 'profile']);
        Route::any('/overview', [Account::class, 'overview']);
        Route::any('/integral', [Account::class, 'integral']);
        Route::any('/changePassword', [Account::class, 'changePassword']);
    });
});



