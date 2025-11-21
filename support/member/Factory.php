<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */


namespace support\member;

use app\exception\BusinessException;

class Factory
{
    private static array $instances = [];

    private static array $serviceClasses = [
        'admin' => 'support\member\admin\AdminService',
        'user'  => 'support\member\user\UserService',
    ];

    /**
     * 获取服务实例
     * @param string $role 角色类型
     * @return object
     * @throws BusinessException
     */
    public static function getService(string $role): object
    {
        if (!isset(self::$serviceClasses[$role])) {
            throw new BusinessException("未知的服务类型: {$role}");
        }

        $cacheKey = "service_{$role}";

        if (!isset(self::$instances[$cacheKey])) {
            $class = self::$serviceClasses[$role];
            self::$instances[$cacheKey] = new $class();
        }

        return self::$instances[$cacheKey];
    }

    /**
     * 获取当前角色的服务实例
     * @return object
     * @throws BusinessException
     */
    public static function getCurrentService(): object
    {
        $role = request()->role ?? 'user';
        return self::getService($role);
    }


}
