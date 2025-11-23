<?php
/**
 * File:        TokenManager.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/11 11:20
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace support\token;

use extend\ra\SystemUtil;
use InvalidArgumentException;
use RuntimeException;
use stdClass;
use think\helper\Arr;
use think\helper\Str;

class TokenManager
{
    protected array  $drivers       = [];
    protected string $defaultDriver = 'jwt';
    protected array  $config        = [];
    protected string $namespace     = '\\support\\token\\driver\\';
    protected mixed  $handler       = null;

    /**
     * @throws \Throwable
     */
    public function __construct(array $config = [])
    {
        // 从配置文件获取鉴权配置
        $tokenConfig         = config('authentication', []);
        $this->defaultDriver = $tokenConfig['driver'] ?? $this->defaultDriver;
        $this->config        = array_merge($config, $tokenConfig);
    }

    /**
     * 获取实例
     * @param array|null $config
     * @return   self
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:21
     */
    public static function getInstance(?array $config = []): self
    {
        return new self($config);
    }

    /**
     * 获取驱动
     * @param string|null $name
     * @return object
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:21
     */
    public function getDriver(?string $name = null): object
    {
        if ($this->handler !== null) {
            return $this->handler;
        }

        $name = $name ?: $this->defaultDriver;

        if ($name === null) {
            throw new InvalidArgumentException(sprintf('Unable to resolve NULL driver for [%s].', static::class));
        }

        return $this->createDriver($name);
    }

    /**
     * 解析参数
     * @param string $name
     * @return   array[]|string[]
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:21
     */
    protected function resolveParams(string $name): array
    {
        $config = $this->getDriverConfig($name);
        return [$config];
    }

    /**
     * 获取驱动配置
     * @param string      $driver
     * @param string|null $name
     * @param             $default
     * @return   array|string
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:21
     */
    protected function getDriverConfig(string $driver, ?string $name = null, $default = null): array|string
    {
        if (empty($this->config)) {
            throw new InvalidArgumentException("Driver [$driver] not found.");
        }
        
        // 优化配置获取逻辑
        if ($name === null) {
            return $this->config;
        }
        
        return Arr::get($this->config, $name, $default);
    }

    /**
     * 通过类型获取驱动
     * @param string $name
     * @return   string
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:22
     */
    protected function resolveType(string $name): string
    {
        return $this->getDriverConfig($name, 'type', 'jwt');
    }

    /**
     * 创建驱动
     * @param string $name
     * @return object
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:22
     */
    protected function createDriver(string $name): object
    {
        $type   = $this->resolveType($name);
        $method = 'create' . Str::studly($type) . 'Driver';
        $params = $this->resolveParams($name);

        if (method_exists($this, $method)) {
            return $this->$method(...$params);
        }

        $class = $this->resolveClass($type);
        return new $class(...$params);
    }

    /**
     * 解析类名
     * @param string $type
     * @return   string
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:22
     */
    protected function resolveClass(string $type): string
    {
        if ($this->namespace || str_contains($type, '\\')) {
            $class = str_contains($type, '\\') ? $type : $this->namespace . Str::studly($type);

            if (class_exists($class)) {
                return $class;
            }
        }

        throw new InvalidArgumentException("Driver [$type] not supported.");
    }

    /**
     * 通过类型创建驱动
     * @param string|null $name
     * @return   TokenInterface
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:23
     */
    public function driver(?string $name = null): TokenInterface
    {
        $name = $name ?: $this->defaultDriver;
        
        // 直接使用 getDriver 方法，避免重复逻辑
        return $this->getDriver($name);
    }

    /**
     * 编码
     * @param array $payload
     * @param bool  $keep
     * @return   string
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:23
     */
    public function encode(array $payload = []): string
    {
        return $this->getDriver()->encode($payload);
    }

    /**
     * 验证（优化版本，支持缓存）
     * @param string $token
     * @return   stdClass
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:23
     */
    public function verify(string $token): stdClass
    {
        // 使用统一的Token验证方法
        return $this->performTokenVerification($token);
    }
    
    /**
     * 执行Token验证（统一方法）
     * @param string $token
     * @return stdClass
     */
    protected function performTokenVerification(string $token): stdClass
    {
        // 检查缓存中是否有已验证的Token
        $cache = TokenCache::getInstance();
        $cachedPayload = $cache->getVerifiedPayload($token);
        
        if ($cachedPayload !== null) {
            return $cachedPayload;
        }
        
        // 缓存中没有，进行实际验证
        $payload = $this->getDriver()->verify($token);
        
        // 缓存验证结果
        $cache->cacheVerifiedPayload($token, $payload);
        
        return $payload;
    }

    /**
     * 解码
     * @param string $token
     * @return   stdClass
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:24
     */
    public function decode(string $token): stdClass
    {
        return $this->getDriver()->decode($token);
    }

    /**
     * 废弃
     * @param string $token
     * @return   bool
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:24
     */
    public function destroy(string $token): bool
    {
        return $this->getDriver()->destroy($token);
    }

    /**
     * 刷新Token（优化版本，支持缓存清理）
     * @param string $token
     * @return string
     */
    public function refresh(string $token): string
    {
        // 使用统一的Token刷新方法
        return $this->performTokenRefresh($token);
    }
    
    /**
     * 执行Token刷新（统一方法）
     * @param string $token
     * @return string
     */
    protected function performTokenRefresh(string $token): string
    {
        // 清除旧Token的缓存
        $cache = TokenCache::getInstance();
        $cache->clearTokenCache($token);
        
        // 执行Token刷新
        $newToken = $this->getDriver()->refresh($token);
        
        return $newToken;
    }

    /**
     * 检查Token是否需要刷新
     * @param string $token
     * @param int $threshold 提前刷新时间阈值（秒）
     * @return bool
     */
    public function shouldRefresh(string $token, int $threshold = 300): bool
    {
        try {
            $payload = $this->verify($token);
            $ttl = $this->getDriver()->ttl($token, $payload);
            
            // 当剩余时间小于阈值时，建议刷新
            return $ttl > 0 && $ttl < $threshold;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 判断过期
     * @param string $token
     * @return   bool
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 11:24
     */
    public function expired(string $token): bool
    {
        return $this->getDriver()->expired($token);
    }

    /**
     * @param string $token
     * @return   mixed
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/11 22:30
     */
    public function ttl(string $token)
    {
        return $this->getDriver()->ttl($token);
    }

    /**
     * 获取加密 Token
     *
     * @return string
     */
    public function getEncryptedToken(): string
    {
        return getEncryptedToken(uniqid($this->config['iss'], true), $this->config['algo'], $this->config['secret']);
    }

    // ==================== Token配置管理方法 ====================
    
    /**
     * 获取Token配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getConfig(string $key, $default = null)
    {
        $config = config('authentication', []);
        
        // 支持点分隔符访问多维数组
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * 获取Token刷新配置
     * @return array
     */
    public static function getTokenRefreshConfig(): array
    {
        return self::getConfig('token_refresh', [
            'enabled' => true,
            'threshold' => 300,
            'max_refresh_count' => 5
        ]);
    }
    
    /**
     * 验证Token配置完整性
     * @return array 缺失的配置项
     */
    public static function validateConfig(): array
    {
        $required = [
            'driver',
            'expire_time', 
            'keep_time',
            'algo',
            'jwt_algo',
            'secret',
            'jwt_secret',
            'iss'
        ];
        
        $missing = [];
        $config = config('authentication', []);
        
        foreach ($required as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                $missing[] = $key;
            }
        }
        
        return $missing;
    }
}
