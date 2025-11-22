<?php

namespace support\member;

use app\exception\BusinessException;
use support\StatusCode;
use Webman\Event\Event;
use support\log\Log;

/**
 * 状态检查事件监听器
 * 负责处理所有状态检查相关的事件
 */
class StateEventListener
{
    /**
     * 状态检查前事件处理
     * @param array $data
     * @return void
     */
    public static function eventBeforeCheck(array $data): void
    {
        // 可以在这里添加前置检查逻辑
        // 例如：记录日志、验证参数等
        Log::debug('状态检查前事件触发', $data);
    }

    /**
     * 检查账号状态事件处理
     * @param array $data
     * @return bool|null
     */
    public static function eventCheckAccountStatus(array $data): ?bool
    {
        $member = $data['member'] ?? null;
        if (!$member) {
            Log::warning('检查账号状态失败：member参数为空');
            return false;
        }

        // 检查账号状态
        if ($member->status !== 'enable') {
            throw new BusinessException('账号已被禁用', StatusCode::USER_DISABLED, true);
        }

        Log::debug('账号状态检查通过', ['username' => $member->username, 'role' => $data['role'] ?? 'admin']);
        return true;
    }

    /**
     * 检查登录失败次数事件处理
     * @param array $data
     * @return bool|null
     */
    public static function eventCheckLoginFailures(array $data): ?bool
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        
        if (!$member) {
            Log::warning('检查登录失败次数失败：member参数为空');
            return false;
        }

        $roleConfig = config('roles.roles.' . $role, []);
        $maxRetry = $roleConfig['login']['max_retry'] ?? 10;
        $lockTime = $roleConfig['login']['lock_time'] ?? 3600;
        
        $loginFailure = $member->login_failure ?? 0;
        $lastLoginTime = $member->last_login_time ?? 0;
        
        if ((int)$loginFailure >= $maxRetry) {
            $unlockTime = (int)$lastLoginTime + $lockTime;

            if (time() < $unlockTime) {
                throw new BusinessException(
                    "账号已锁定，请在" . ($unlockTime - time()) . "秒后重试",
                    StatusCode::LOGIN_ACCOUNT_LOCKED, true
                );
            }
            
            // 解锁账号
            $member->login_failure = 0;
            $member->save();
            Log::info('账号自动解锁', ['username' => $member->username, 'role' => $role]);
        }

        Log::debug('登录失败次数检查通过', ['username' => $member->username, 'role' => $role, 'login_failure' => $loginFailure]);
        return true;
    }

    /**
     * 检查单点登录状态事件处理
     * @param array $data
     * @return bool|null
     */
    public static function eventCheckSsoStatus(array $data): ?bool
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        
        if (!$member) {
            Log::warning('检查单点登录状态失败：member参数为空');
            return false;
        }

        $roleConfig = config('roles.roles.' . $role, []);
        $ssoConfig = $roleConfig['login']['sso'] ?? false;
        
        if ($ssoConfig) {
            // 单点登录模式下，检查是否在其他地方登录
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            
            $cacheKey = $state->getStateCacheKey();
            $stateData = \support\cache\Cache::get($cacheKey);
            
            if ($stateData && isset($stateData['token']) && $stateData['token'] !== request()->token) {
                throw new BusinessException('账号已在其他地方登录', StatusCode::USER_LOGGED_IN_ELSEWHERE, true);
            }
        }

        Log::debug('单点登录状态检查通过', ['username' => $member->username, 'role' => $role]);
        return true;
    }

    /**
     * 状态检查后事件处理
     * @param array $data
     * @return void
     */
    public static function eventAfterCheck(array $data): void
    {
        // 可以在这里添加后置检查逻辑
        // 例如：记录检查结果、清理临时数据等
        Log::debug('状态检查后事件触发', $data);
    }

    /**
     * 更新登录状态事件处理
     * @param array $data
     * @return bool
     */
    public static function eventUpdateLoginState(array $data): bool
    {
        $member = $data['member'] ?? null;
        $success = $data['success'] ?? false;
        $role = $data['role'] ?? 'admin';
        
        if (!$member) {
            Log::warning('更新登录状态失败：member参数为空');
            return false;
        }

        try {
            $member->startTrans();

            if ($success) {
                $member->login_failure = 0;
                
                // 更新状态缓存
                $state = new State();
                $state->role = $role;
                $state->memberModel = $member;
                $state->updateStateCache();
                Log::info('登录状态更新成功', ['username' => $member->username, 'role' => $role]);
            } else {
                // 安全处理登录失败次数
                $loginFailure = $member->login_failure ?? 0;
                $member->login_failure = (int)$loginFailure + 1;
                Log::warning('登录状态更新失败', ['username' => $member->username, 'role' => $role, 'login_failure' => $loginFailure + 1]);
            }

            $member->last_login_time = time();
            $member->last_login_ip = request()->getRealIp();
            $member->save();

            $member->commit();
            return true;
        } catch (\Throwable $e) {
            if (method_exists($member, 'rollback')) {
                $member->rollback();
            }
            Log::error('更新登录状态失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 记录登录失败事件处理
     * @param array $data
     * @return bool
     */
    public static function eventRecordLoginFailure(array $data): bool
    {
        $member = $data['member'] ?? null;
        $reason = $data['reason'] ?? null;
        $role = $data['role'] ?? 'admin';
        
        if (!$member) {
            Log::warning('记录登录失败失败：member参数为空');
            return false;
        }

        try {
            $member->startTrans();

            $loginFailure = $member->login_failure ?? 0;
            $member->login_failure = (int)$loginFailure + 1;
            $member->last_login_time = time();
            $member->last_login_ip = request()->getRealIp();
            $member->save();

            // 记录登录日志
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            $state->recordLoginLog(false, $reason);

            $member->commit();
            Log::info('登录失败记录成功', ['username' => $member->username, 'role' => $role, 'reason' => $reason]);
            return true;
        } catch (\Throwable $e) {
            if (method_exists($member, 'rollback')) {
                $member->rollback();
            }
            Log::error('记录登录失败信息失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 登录成功事件处理
     * @param array $data
     * @return void
     */
    public static function eventLoginSuccess(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        
        if (!$member) {
            Log::warning('登录成功事件处理失败：member参数为空');
            return;
        }

        try {
            $member->startTrans();

            // 登录成功时重置失败次数
            $member->login_failure = 0;
            
            // 更新状态缓存
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            $state->updateStateCache();
            
            // 更新登录时间
            $member->last_login_time = time();
            $member->last_login_ip = request()->getRealIp();
            $member->save();

            // 记录登录成功日志
            $state->recordLoginLog(true);
            
            $member->commit();
            Log::info("用户 {$member->username} ({$role}) 登录成功");
            
        } catch (\Throwable $e) {
            if (method_exists($member, 'rollback')) {
                $member->rollback();
            }
            Log::error('登录成功事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 登录失败事件处理
     * @param array $data
     * @return void
     */
    public static function eventLoginFailure(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        
        if (!$member) {
            Log::warning('登录失败事件处理失败：member参数为空');
            return;
        }

        try {
            // 记录登录失败
            self::eventRecordLoginFailure([
                'member' => $member,
                'role' => $role,
                'reason' => '认证失败'
            ]);
            
            Log::warning("用户 {$member->username} ({$role}) 登录失败");
            
        } catch (\Throwable $e) {
            Log::error('登录失败事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 通配符事件处理
     * @param array $data
     * @param string $eventName
     * @return void
     */
    public static function eventHandleWildcard(array $data, string $eventName): void
    {
        // 处理通配符事件，例如 state.check.*
        // 可以根据 $eventName 进行不同的处理
        Log::debug('通配符事件处理', ['event_name' => $eventName, 'data' => $data]);
    }
}