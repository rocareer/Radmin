<?php
/**
 * Project:     radmin
 * File:        migrate.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/14 10:49
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
return [
    "paths"        => [
        "migrations" => "database/migrations",
        "seeds"      => "database/seeds"
    ],
    "table_prefix" => getenv('MYSQL_PREFIX','ra_') ,
    "environments" => [
        "default_migration_table" => getenv('MYSQL_PREFIX','ra_') . "migrations",
        "default_environment"     => "dev",
        "dev"                     => [
            "adapter" => 'mysql',
            "host"    => getenv('MYSQL_HOSTNAME', '127.0.0.1'),
            "name"    => getenv('MYSQL_DATABASE', 'r25111801'),
            "user"    => getenv('MYSQL_USERNAME', 'root'),
            "pass"    => getenv('MYSQL_PASSWORD', '123456'),
            "port"    => getenv('MYSQL_HOSTPORT', '3306'),
            "charset" => getenv('MYSQL_CHARSET', 'utf8'),
            "prefix"  => getenv('MYSQL_PREFIX', 'ra_'), // 确保这里有前缀


        ],
        'production'              => [
            'adapter' => 'mysql',
            'host'    => '127.0.0.1',
            'name'    => 'your_production_database_name',
            'user'    => 'root',
            'pass'    => 'your_password',
            'port'    => '3306',
            'charset' => 'utf8',
            "prefix"  => "rb_", // 确保这里有前缀
        ],
    ]
];