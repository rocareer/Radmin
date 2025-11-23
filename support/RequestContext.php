<?php

namespace support;

/**
 * 请求上下文管理器
 * 
 * 职责：
 * 1. 管理请求级别的简单数据（请求信息、时间戳等）
 * 2. 提供轻量级的键值存储
 * 3. 不涉及业务逻辑和成员管理
 * 4. 支持多请求隔离，避免状态干扰
 */
class RequestContext
{
    private static array $data = [];

    /**
     * 设置请求上下文数据
     */
    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    /**
     * 获取请求上下文数据
     */
    public static function get(?string $key = null, mixed $default = null): mixed
    {
        if (empty($key)) {
            return self::$data;
        }
        return self::$data[$key] ?? $default;
    }

    /**
     * 删除指定的上下文数据
     */
    public static function delete(string $key): void
    {
        unset(self::$data[$key]);
    }

    /**
     * 清空所有请求上下文数据
     * 注意：此操作会清除所有请求级别的数据，包括成员信息
     */
    public static function clear(): void
    {
        self::$data = [];
    }

    /**
     * 清空业务相关的上下文数据（保留请求基本信息）
     */
    public static function clearBusinessData(): void
    {
        // 保留请求基本信息和调试信息
        $preservedKeys = [
            'request_start_time', 
            'request_path', 
            'request_method', 
            'request_process_time',
            'request_id' // 用于调试的请求ID
        ];
        
        // 清理所有业务相关的数据
        foreach (self::$data as $key => $value) {
            if (!in_array($key, $preservedKeys)) {
                unset(self::$data[$key]);
            }
        }
        
        // 记录清理事件（仅在调试模式下）
        if (config('app.debug', false)) {
            $clearedCount = count(self::$data) - count($preservedKeys);
            if ($clearedCount > 0) {
                \Webman\Event\Event::emit('request.business_data.cleaned', [
                    'preserved_keys' => $preservedKeys,
                    'remaining_count' => count(self::$data),
                    'timestamp' => microtime(true)
                ]);
            }
        }
    }

    /**
     * 检查是否存在指定的上下文数据
     */
    public static function has(string $key): bool
    {
        return isset(self::$data[$key]);
    }

    /**
     * 获取请求基本信息（用于调试）
     */
    public static function getRequestInfo(): array
    {
        return [
            'start_time' => self::get('request_start_time'),
            'path' => self::get('request_path'),
            'method' => self::get('request_method'),
            'process_time' => self::get('request_process_time'),
            'data_keys' => array_keys(self::$data)
        ];
    }
}
