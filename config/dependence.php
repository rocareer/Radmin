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

use support\member\admin\AdminModel;
use support\member\admin\AdminService;
use support\member\user\UserModel;
use support\member\user\UserService;
use support\member\admin\AdminAuthenticator;
use support\member\user\UserAuthenticator;
use support\member\State;

return [
    // 成员服务工厂
    'member.service.factory' => function ($container) {
        return new class($container) {
            private $container;
            
            public function __construct($container)
            {
                $this->container = $container;
            }
            
            /**
             * 创建服务实例
             */
            public function createService(string $role): object
            {
                $serviceMap = [
                    'admin' => AdminService::class,
                    'user' => UserService::class,
                ];
                
                $serviceClass = $serviceMap[$role] ?? UserService::class;
                return $this->container->make($serviceClass);
            }
            
            /**
             * 根据请求上下文创建服务实例
             */
            public function createServiceFromRequest(): object
            {
                $role = request()->role ?? 'user';
                return $this->createService($role);
            }
        };
    },
    
    // 模型工厂
    'member.model.factory' => function ($container) {
        return new class {
            /**
             * 创建模型实例
             */
            public function createModel(string $role): object
            {
                $modelMap = [
                    'admin' => AdminModel::class,
                    'user' => UserModel::class,
                ];
                
                $modelClass = $modelMap[$role] ?? UserModel::class;
                return new $modelClass();
            }
        };
    },
    
    // 认证器工厂
    'member.authenticator.factory' => function ($container) {
        return new class {
            /**
             * 创建认证器实例
             */
            public function createAuthenticator(string $role): object
            {
                $authenticatorMap = [
                    'admin' => AdminAuthenticator::class,
                    'user' => UserAuthenticator::class,
                ];
                
                $authenticatorClass = $authenticatorMap[$role] ?? UserAuthenticator::class;
                return new $authenticatorClass();
            }
        };
    },
    
    // 状态管理器工厂
    'member.state.factory' => function ($container) {
        return new class {
            /**
             * 创建状态管理器实例
             */
            public function createState(string $role): object
            {
                return new State();
            }
        };
    },
    
    // 统一成员服务（保持向后兼容）
    'member.service' => function ($container) {
        return $container->get('member.service.factory')->createServiceFromRequest();
    },
];
