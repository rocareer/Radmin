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

use Psr\Container\ContainerInterface;
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

use function DI\create;
use function DI\factory;

return [
    // request

    // member
    'member.roles'             => [
        'admin', 'user'
    ],

    // member map
    'member.service.map'       => [
        'admin' => AdminService::class,
        'user'  => UserService::class,
    ],
    'member.state.map'         => [
        'admin' => AdminState::class,
        'user'  => UserState::class,
    ],
    'member.model.map'         => [
        'admin' => AdminModel::class,
        'user'  => UserModel::class,
    ],
    'member.authenticator.map' => [
        'admin' => AdminAuthenticator::class,
        'user'  => UserAuthenticator::class,
    ],

    // 别名
    'member.service'           => function (ContainerInterface $container) {
        return $container->make(InterfaceService::class);
    },
    'member.model'             => function (ContainerInterface $container) {
        return $container->make(InterfaceModel::class);
    },
    'member.state'             => function (ContainerInterface $container) {
        return $container->make(InterfaceState::class);
    },
    'member.authenticator'     => function (ContainerInterface $container) {
        return $container->make(InterfaceAuthenticator::class);
    },

    InterfaceService::class       => factory(fn($container) => resolveByRole($container, 'member.service.map')),
    InterfaceState::class         => factory(fn($container) => resolveByRole($container, 'member.state.map')),
    InterfaceModel::class         => factory(fn($container) => resolveByRole($container, 'member.model.map')),
    InterfaceAuthenticator::class => factory(fn($container) => resolveByRole($container, 'member.authenticator.map')),
];
