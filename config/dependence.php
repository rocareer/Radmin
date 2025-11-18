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

use support\member\admin\AdminAuthenticator;
use support\member\admin\AdminModel;
use support\member\admin\AdminService;
use support\member\admin\AdminState;
use support\member\InterfaceAuthenticator;
use support\member\InterfaceModel;
use support\member\InterfaceService;
use support\member\InterfaceState;
use support\member\user\UserAuthenticator;
use support\member\user\UserModel;
use support\member\user\UserService;
use support\member\user\UserState;

return [
    // request

    // member
    'member.roles'             => [
        'admin', 'user'
    ],

    // member map
    'member.service.map'       => function ($container) {
        return [
            'admin' => AdminService::class,
            'user'  => UserService::class,
        ];
    },
    'member.state.map'         => function ($container) {
        return [
            'admin' => AdminState::class,
            'user'  => UserState::class,
        ];
    },
    'member.model.map'         => function ($container) {
        return [
            'admin' => AdminModel::class,
            'user'  => UserModel::class,
        ];
    },
    'member.authenticator.map' => function ($container) {
        return [
            'admin' => AdminAuthenticator::class,
            'user'  => UserAuthenticator::class,
        ];
    },

    // 别名
    'member.service'           => function ($container) {
        return $container->get(InterfaceService::class);
    },
    'member.model'             => function ($container) {
        return $container->get(InterfaceModel::class);
    },
    'member.state'             => function ($container) {
        return $container->get(InterfaceState::class);
    },
    'member.authenticator'     => function ($container) {
        return $container->get(InterfaceAuthenticator::class);
    },

    InterfaceService::class       => function($container) {
        return resolveByRole($container, 'member.service.map');
    },
    InterfaceState::class         => function($container) {
        return resolveByRole($container, 'member.state.map');
    },
    InterfaceModel::class         => function($container) {
        return resolveByRole($container, 'member.model.map');
    },
    InterfaceAuthenticator::class => function($container) {
        return resolveByRole($container, 'member.authenticator.map');
    },
];
