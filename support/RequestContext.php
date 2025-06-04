<?php

namespace support;

class RequestContext
{
    private static array $data = [];

    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    public static function get(?string $key=null, mixed $default = null): mixed
    {
        if (empty($key)){
            return self::$data;
        }
        return self::$data[$key] ?? $default;
    }

    public static function delete(string $key): void
    {
        unset(self::$data[$key]);
    }

    public static function clear(): void
    {
        self::$data = [];
    }
}
