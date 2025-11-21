<?php
/**
 * Token 接口定义
 * 统一所有 Token 驱动的标准接口
 */

namespace support\token;

use stdClass;

interface TokenInterface
{
    /**
     * 生成 Token
     * @param array $payload 附加数据
     * @return string
     */
    public function encode(array $payload = []): string;

    /**
     * 解码 Token
     * @param string $token
     * @return stdClass
     */
    public function decode(string $token): stdClass;

    /**
     * 验证 Token
     * @param string $token
     * @return stdClass
     */
    public function verify(string $token): stdClass;

    /**
     * 销毁 Token
     * @param string $token
     * @return bool
     */
    public function destroy(string $token): bool;

    /**
     * 刷新 Token
     * @param string $token
     * @return string
     */
    public function refresh(string $token): string;

    /**
     * 检查 Token 是否过期
     * @param string $token
     * @param stdClass|null $tokenData
     * @return bool
     */
    public function expired(string $token, ?stdClass $tokenData = null): bool;

    /**
     * 获取 Token 剩余有效时间
     * @param string $token
     * @param stdClass|null $tokenData
     * @return int
     */
    public function ttl(string $token, ?stdClass $tokenData = null): int;
}
