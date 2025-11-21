<?php
/**
 * File:        Jwt.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/11 11:36
 * Description: JWT 驱动实现类
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace support\token\driver;

use Exception;
use app\exception\TokenException;
use support\jwt\Jwt as JwtFacade;
use support\jwt\JwtService;
use support\token\Token;
use support\token\TokenInterface;
use stdClass;
use support\StatusCode;

class Jwt implements TokenInterface
{
    protected array      $config;
    protected JwtService $jwt;
    protected int        $expire_time = 7200;
    protected int        $keep_time   = 7200;

    public function __construct(array $config = [])
    {
        $this->config = $config;

        // 设置过期时间和保持时间
        $this->expire_time = $this->config['expire_time'] ?? $this->expire_time;
        $this->keep_time   = $this->config['keep_time'] ?? $this->keep_time;

        // 初始化 JWT 服务
        $this->jwt = JwtFacade::getInstance($config);
    }

    /**
     * 生成 JWT Token
     * @param array $payload
     * @return   string
     * @throws TokenException
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 21:30
     */
    public function encode(array $payload = []): string
    {
        // 默认载荷数据
        $payloadData = [
            'iss'   => $this->config['iss'],
            'sub'   => $payload['sub'] ?? '',
            'iat'   => time(),
            'jti'   => ($payload['type'] ?? 'access') . '-' . Token::getEncryptedToken(),
            'exp'   => $payload['exp'] ?? $this->expire_time + time(),
            'roles' => $payload['roles'] ?? [],
            'role'  => $payload['role'] ?? '',
            'type'  => $payload['type'] ?? 'access',
        ];

        // 如果是刷新 Token，设置过期时间和类型
        if ($payload['keep'] ?? false) {
            $payloadData['exp']  = $this->keep_time + time();
            $payloadData['type'] = 'refresh';
        }

        // 生成 Token
        return $this->jwt->encode($payloadData, $this->config['jwt_secret'], $this->config['jwt_algo']);
    }

    /**
     * 解码 JWT Token
     *
     * @param string $token Token 字符串
     * @return stdClass
     * @throws Exception
     */
    public function decode(string $token): stdClass
    {
        return $this->jwt->decode($token, $this->config['jwt_secret'], $this->config['jwt_algo']);
    }

    /**
     * 验证 JWT Token
     *
     * @param string $token Token 字符串
     * @return stdClass
     * @throws TokenException
     */
    public function verify(string $token): stdClass
    {
        return $this->jwt->Verify($token);
    }

    /**
     * 检查 Token 是否过期
     *
     * @param string        $token     Token 字符串
     * @param stdClass|null $tokenData 解码后的 Token 数据
     * @return bool
     * @throws Exception
     */
    public function expired(string $token, ?stdClass $tokenData = null): bool
    {
        $tokenData = $tokenData ?? $this->decode($token);
        return $this->ttl($token, $tokenData) <= 0;
    }

    /**
     * 获取 Token 的剩余有效时间
     *
     * @param string        $token     Token 字符串
     * @param stdClass|null $tokenData 解码后的 Token 数据
     * @return int
     * @throws Exception
     */
    public function ttl(string $token, ?stdClass $tokenData = null): int
    {
        $tokenData = $tokenData ?? $this->decode($token);
        return $tokenData->exp - time();
    }

    /**
     * 销毁 Token
     *
     * @param string $token Token 字符串
     * @return bool
     */
    public function destroy(string $token): bool
    {
        // JWT 是无状态的，无法单独删除，通常通过黑名单实现
        return $this->jwt->setBlacklist($token);
    }

    /**
     * 刷新 Token
     *
     * @param string $token 刷新凭证
     * @return string
     * @throws TokenException
     * @throws Exception
     */
    public function refresh(string $token): string
    {
        // 验证Token有效性
        $payload = $this->verify($token);
        
        // 检查Token类型
        if ($payload->type !== 'refresh') {
            throw new TokenException('Token 不能刷新', StatusCode::TOKEN_REFRESH_FAILED);
        }
        
        // 检查Token是否过期
        if ($this->expired($token, $payload)) {
            throw new TokenException('刷新Token已过期', StatusCode::TOKEN_EXPIRED);
        }
        
        // 构建新的Token载荷
        $newPayload = $this->buildRefreshPayload($payload);
        
        // 生成新的访问Token
        return $this->encode($newPayload);
    }

    /**
     * 构建刷新后的Token载荷
     * @param stdClass $payload 原始载荷
     * @return array
     */
    protected function buildRefreshPayload(stdClass $payload): array
    {
        $payloadArray = (array)$payload;
        
        // 移除时间相关字段，重新生成
        unset($payloadArray['exp'], $payloadArray['jti'], $payloadArray['type'], $payloadArray['iat']);
        
        // 确保生成访问Token而非刷新Token
        $payloadArray['type'] = 'access';
        $payloadArray['keep'] = false;
        
        return $payloadArray;
    }


    /**
     * 获取签名密钥
     *
     * @return string
     */
    protected function getSigningKey(): string
    {
        return in_array($this->config['algo'], ['RS256', 'RS384', 'RS512'])
            ? $this->config['private_key']
            : $this->config['secret'];
    }
}
