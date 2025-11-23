<?php


namespace support\member\interface;

/**
 * 状态管理器接口
 */
interface InterfaceModel
{
    public function verifyPassword(string $inputPassword, $member): bool;

    public function findById(int $id, bool $withAllowFields = true): object;


}