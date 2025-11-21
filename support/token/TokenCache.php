<?php
/**
 * Token缓存管理器
 * 优化Token验证性能，减少重复验证操作
 */

namespace support\token;

use stdClass;

class TokenCache
{
    protected static ?TokenCache $instance = null;
    protected array $verifiedTokens = [];
    protected array $tokenPayloads = [];
    protected int $cacheTtl = 300; // 5分钟缓存

    private function __construct()
    {
        // 私有构造函数，确保单例模式
    }

    public static function getInstance(): TokenCache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取缓存的Token验证结果
     */
    public function getVerifiedPayload(string $token): ?stdClass
    {
        $cacheKey = $this->getCacheKey($token);
        
        if (isset($this->verifiedTokens[$cacheKey])) {
            $cachedData = $this->verifiedTokens[$cacheKey];
            
            // 检查缓存是否过期
            if (time() - $cachedData['timestamp'] < $this->cacheTtl) {
                return $cachedData['payload'];
            } else {
                // 缓存过期，清除
                unset($this->verifiedTokens[$cacheKey]);
            }
        }
        
        return null;
    }

    /**
     * 缓存Token验证结果
     */
    public function cacheVerifiedPayload(string $token, stdClass $payload): void
    {
        $cacheKey = $this->getCacheKey($token);
        
        $this->verifiedTokens[$cacheKey] = [
            'payload' => $payload,
            'timestamp' => time()
        ];
        
        // 限制缓存大小，避免内存泄漏
        if (count($this->verifiedTokens) > 100) {
            $this->cleanupOldCache();
        }
    }

    /**
     * 清除指定Token的缓存
     */
    public function clearTokenCache(string $token): void
    {
        $cacheKey = $this->getCacheKey($token);
        unset($this->verifiedTokens[$cacheKey]);
    }

    /**
     * 清除所有Token缓存
     */
    public function clearAllCache(): void
    {
        $this->verifiedTokens = [];
        $this->tokenPayloads = [];
    }

    /**
     * 清理过期缓存
     */
    protected function cleanupOldCache(): void
    {
        $currentTime = time();
        
        foreach ($this->verifiedTokens as $key => $data) {
            if ($currentTime - $data['timestamp'] > $this->cacheTtl) {
                unset($this->verifiedTokens[$key]);
            }
        }
        
        // 如果仍然超过限制，清理最旧的缓存
        if (count($this->verifiedTokens) > 100) {
            $this->verifiedTokens = array_slice($this->verifiedTokens, -100, 100, true);
        }
    }

    /**
     * 生成缓存键
     */
    protected function getCacheKey(string $token): string
    {
        return md5($token);
    }

    /**
     * 设置缓存TTL
     */
    public function setCacheTtl(int $ttl): void
    {
        $this->cacheTtl = $ttl;
    }
}