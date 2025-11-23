<?php


namespace support\member\interface;

/**
 * 权限和菜单管理器接口
 */
interface InterfaceRule
{
    /**
     * 获取菜单规则列表
     * @param int|null $uid 用户ID
     * @return array
     */
    public function getMenus(?int $uid = null): array;

    /**
     * 获得权限规则原始数据
     * @param int|null $uid 用户id
     * @return array
     */
    public function getOriginAuthRules(?int $uid = null): array;

    /**
     * 获取权限规则ids
     * @param int|null $uid 用户ID
     * @return array
     */
    public function getRuleIds(?int $uid = null): array;

    /**
     * 获取用户所有分组和对应权限规则
     * @param int|null $uid 用户ID
     * @return array
     */
    public function getGroups(?int $uid = null): array;

    /**
     * 检查是否有某权限
     * @param string $name     菜单规则的 name，可以传递两个，以','号隔开
     * @param int|null $uid      用户ID
     * @param string $relation 如果出现两个 name,是两个都通过(and)还是一个通过即可(or)
     * @param string $mode     如果不使用 url 则菜单规则name匹配到即通过
     * @return bool
     */
    public function check(string $name, ?int $uid = null, string $relation = 'or', string $mode = 'url'): bool;

    /**
     * 获得权限规则列表
     * @param int|null $uid 用户id
     * @return array
     */
    public function getRuleList(?int $uid = null): array;

}