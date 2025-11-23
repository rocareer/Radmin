<?php
/**
 * File:        authentication.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/23 00:00
 * Description: 鉴权配置管理 - 优化版，专注于Token驱动和字段配置
 *
 * 支持的配置选项：
 * - driver: 默认驱动类型 (jwt, cache, mysql, redis)
 * - expire_time: Token有效期(秒)
 * - keep_time: 保持会话时间(秒)
 * - algo: 通用加密算法
 * - jwt_algo: JWT签名算法
 * - secret: 通用加密密钥
 * - jwt_secret: JWT专用密钥
 * - iss: 签发者标识
 * - allow_keys: 允许的Token字段列表
 * - drivers: 支持的驱动配置
 * - algorithms: 支持的加密算法
 * - jwt_algorithms: 支持的JWT算法
 * - token_refresh: Token刷新配置
 * - security: 安全相关配置
 * - validation: 验证规则配置
 * - logging: 日志记录配置
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

return [
    // ==================== Token基础配置 ====================

    // 默认驱动类型
    'driver'      => 'jwt',

    // Token有效期(秒)
    'expire_time' => 3600,

    // 保持会话时间(秒)
    'keep_time'   => 604800,

    // 加密方式
    'algo'        => 'sha256',

    // JWT签名算法
    'jwt_algo'    => 'HS256',

    // 加密密钥
    'secret'      => 'jp9S^mtu^!6)(iGr_Xqwe^PstooaJRyMcPAYgyfo+bDKg%z*$JivrY0vz_waCrV*Arx@0+60zBU8L50tacPG1zTq12mGalZ9qa%tktUPj)%EAv2fjCBsWgSl*Pz&@9!dpR0hXl1e2El*%DwJS#xeOIkyOUv*6G@OI9XCumlyBxtwYn8E^pyVP9IJHTzq^#E8p#SS%tPRNsiF1IE@I$hnCbRSd5AjERg#++^palDcyjav8qKh*!GXUWrtuH@W(4)S',

    // JWT加密密钥
    'jwt_secret'  => 'P3nP5cLURe!RQeOTzjNPAVNEs&8a4(2o$Eh(nsT3IAOtyLf9te1tAlyLx45gWLMaN5kxnx1C&wrWcf)xTxi97bGhrBADum0EF$EP_E$F503bT^Srq8vP6Vuh%4&wgHohI8LGX@dksEKnOQW#Gv181_Tp@dwXtO*5HNlv)RdS06k2DGI+V24$jA%i!gL$EGw16%i_szlLDyP!CLyzkr(ygy8mXxLr)aXXurMln5H)W4___EfNYOIoSH7W1HNor&lS',

    // 签发者标识
    'iss'         => 'Radmin',

    // 允许的字段 (一维数组，简化配置)
    'allow_keys'  => ['iss', 'sub', 'exp', 'iat', 'jti', 'roles', 'type', 'role'],

    // ==================== 驱动配置 ====================
    // 支持的驱动类型：jwt, cache, mysql, redis
    // 每个驱动对应 support\\token\\driver\\ 命名空间下的类

    'drivers' => [
        'jwt'   => 'support\\token\\driver\\Jwt',
        'cache' => 'support\\token\\driver\\Cache',
        'mysql' => 'support\\token\\driver\\Mysql',
        'redis' => 'support\\token\\driver\\Redis'
    ],

    // ==================== 算法配置 ====================
    // 支持的通用加密算法：md5, sha1, sha256, sha512, whirlpool, ripemd256, gost

    'algorithms' => ['md5', 'sha1', 'sha256', 'sha512', 'whirlpool',
                     'ripemd256', 'gost'],

    // 支持的JWT签名算法：HS256, HS384, HS512, RS256, RS384, RS512

    'jwt_algorithms' => ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'],

    // ==================== Token刷新配置 ====================

    'token_refresh' => [
        'enabled'           => true,
        'threshold'         => 300, // 5分钟内可刷新
        'max_refresh_count' => 5,
        'grace_period'      => 30 // 宽限期(秒)
    ],

    // ==================== 安全配置 ====================

    'security' => [
        'max_login_attempts' => 5, // 最大登录尝试次数
        'lockout_duration'   => 900, // 锁定时间(秒)
        'require_https'      => false, // 是否要求HTTPS
        'same_site'          => 'Lax', // SameSite策略
        'http_only'          => true, // HTTP Only
        'secure'             => false // 仅HTTPS传输
    ],

    // ==================== 验证配置 ====================

    'validation' => [
        'min_token_length'  => 32, // 最小Token长度
        'max_token_length'  => 512, // 最大Token长度
        'require_nonce'     => true, // 是否需要随机数
        'validate_issuer'   => true, // 验证签发者
        'validate_audience' => false, // 验证受众
        'leeway'            => 60 // 时间容差(秒)
    ],

    // ==================== 日志配置 ====================

    'logging' => [
        'enabled'                => true,
        'level'                  => 'info', // 日志级别
        'log_token_creation'     => true, // 记录Token创建
        'log_token_verification' => true, // 记录Token验证
        'log_token_refresh'      => true, // 记录Token刷新
        'log_security_events'    => true // 记录安全事件
    ]
];