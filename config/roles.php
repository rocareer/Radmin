<?php
/**
 * 统一认证和角色配置管理
 * 合并了原有的 auth.php 和 roles.php 配置，减少配置冗余
 */

return [
    // 默认角色配置
    'default' => 'guest',
    
    // 角色定义（合并认证配置）
    'roles' => [
        // 公共角色（不需要鉴权）
        'guest' => [
            'name' => '访客',
            'description' => '未登录的公共用户',
            'auth_required' => false,
            'path_prefix' => ['/public', '/api/public'],
            'middleware' => [],
            'service_class' => null,
            'model_class' => null,
            'authenticator_class' => null,
            'state_class' => null,
            'table' => null,
            // 认证相关配置
            'login' => [
                'captcha' => false,
            ],
            'headers' => [],
        ],
        
        // 前台用户
        'user' => [
            'name' => '前台用户',
            'description' => '前台登录用户',
            'auth_required' => true,
            'path_prefix' => ['/user', '/api/user'],
            'middleware' => ['auth'],
            'service_class' => 'support\\member\\user\\UserService',
            'model_class' => 'support\\member\\user\\UserModel',
            'authenticator_class' => 'support\\member\\user\\UserAuthenticator',
            'state_class' => 'support\\member\\user\\UserState',
            'table' => 'user',
            // 认证相关配置
            'login' => [
                'max_retry' => 10,
                'lock_time' => 3600,
                'sso' => false,
                'token_keep_time' => 259200,
                'captcha' => false,
            ],
            'headers' => ['Authorization'],
        ],
        
        // 后台管理员
        'admin' => [
            'name' => '后台管理员',
            'description' => '后台管理系统用户',
            'auth_required' => true,
            'path_prefix' => ['/admin', '/api/admin'],
            'middleware' => ['auth', 'admin'],
            'service_class' => 'support\\member\\admin\\AdminService',
            'model_class' => 'support\\member\\admin\\AdminModel',
            'authenticator_class' => 'support\\member\\admin\\AdminAuthenticator',
            'state_class' => 'support\\member\\admin\\AdminState',
            'table' => 'admin',
            // 认证相关配置
            'login' => [
                'max_retry' => 10,
                'lock_time' => 3600,
                'sso' => false,
                'token_keep_time' => 259200,
                'captcha' => false,
            ],
            'headers' => ['X-Token'],
        ],
        
        // 示例：新增角色（如代理商）
        'agent' => [
            'name' => '代理商',
            'description' => '代理商用户',
            'auth_required' => true,
            'path_prefix' => ['/agent', '/api/agent'],
            'middleware' => ['auth'],
            'service_class' => 'support\\member\\agent\\AgentService',
            'model_class' => 'support\\member\\agent\\AgentModel',
            'authenticator_class' => 'support\\member\\agent\\AgentAuthenticator',
            'state_class' => 'support\\member\\agent\\AgentState',
            'table' => 'agent',
            // 认证相关配置
            'login' => [
                'max_retry' => 10,
                'lock_time' => 3600,
                'sso' => false,
                'token_keep_time' => 259200,
                'captcha' => false,
            ],
            'headers' => ['Authorization'],
        ],
    ],
    
    // 路径匹配规则
    'path_rules' => [
        'exact' => [
            '/login' => 'guest',
            '/register' => 'guest',
        ],
        'prefix' => [
            '/admin' => 'admin',
            '/user' => 'user',
            '/agent' => 'agent',
            '/api/admin' => 'admin',
            '/api/user' => 'user',
            '/api/agent' => 'agent',
            '/api/public' => 'guest',
            '/public' => 'guest',
        ],
        'default' => 'guest',
    ],
    
    // 多角色支持配置
    'multi_role' => [
        'enabled' => true,
        'max_concurrent' => 3,
        'isolation_level' => 'strict',
    ],
    
    // Token配置
    'token' => [
        'driver' => 'jwt',
        'prefix' => 'token_',
        'expire' => 7200,
        'refresh_expire' => 2592000,
        'role_separator' => '_',
    ],
    
    // 上下文管理
    'context' => [
        'storage_driver' => 'request',
        'auto_cleanup' => true,
        'cleanup_timeout' => 300,
    ],
    
    // 状态管理配置（从原auth.php合并）
    'state' => [
        'cache_time' => 86400,
        'store' => 'member-cache',
        'prefix' => 'state-'
    ],
    
    // 免认证路径配置（从原auth.php合并）
    'exclude' => [
        '/install',
        '/admin/Index/login',
        '/user/Index/login',
        '/api/install/*',
        '/api/install/envBaseCheck',
        '/api/install/envNpmCheck',
        '/api/common/clickCaptcha',
        '/api/common/checkClickCaptcha',
        '/api/common/refreshToken',
        '/admin/ajax/terminal',
        '/api/index',
    ],
];