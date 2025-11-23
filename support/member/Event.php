<?php

namespace support\member;

use app\exception\BusinessException;
use support\StatusCode;
use Webman\Event\Event as WebmanEvent;
use support\Log;
use support\member\State;

/**
 * 会员事件监听器
 * 负责处理所有会员相关的生命周期事件
 */
class Event
{
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
            // 直接处理登录信息更新，不再触发单独事件
            self::handleLoginInfoUpdate($member, $role);
            
            // 更新状态缓存
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            $state->updateStateCache();

            // 记录登录成功日志
            $state->recordLoginLog(true);
            
            Log::info("用户 {$member->username} ({$role}) 登录成功");
            
        } catch (\Throwable $e) {
            Log::error('登录成功事件处理失败：' . $e->getMessage(), [
                'member_id' => $member->id ?? 'unknown',
                'role' => $role,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * 处理登录信息更新（使用Service中的方法）
     * @param object $member 用户模型
     * @param string $role 用户角色
     * @return bool
     */
    private static function handleLoginInfoUpdate(object $member, string $role): bool
    {
        try {
            // 获取对应角色的Service实例
            $service = $role === 'admin' ? new \app\admin\service\AuthService() : new \app\common\service\UserService();
            
            // 使用Service中的方法更新登录信息
            return $service->updateLoginInfo($member, $role);
            
        } catch (\Throwable $e) {
            Log::error('登录信息更新失败：' . $e->getMessage(), [
                'member_id' => $member->id ?? 'unknown',
                'role' => $role,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
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
     * 用户注册成功事件处理
     * @param array $data
     * @return void
     */
    public static function eventRegisterSuccess(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'user';
        
        if (!$member) {
            Log::warning('用户注册成功事件处理失败：member参数为空');
            return;
        }

        try {
            // 记录注册成功日志
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            $state->recordLoginLog(true, '用户注册成功');
            
            Log::info("用户 {$member->username} ({$role}) 注册成功", [
                'user_id' => $member->id,
                'username' => $member->username,
                'role' => $role,
                'register_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('用户注册成功事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 用户注册失败事件处理
     * @param array $data
     * @return void
     */
    public static function eventRegisterFailure(array $data): void
    {
        $username = $data['username'] ?? null;
        $role = $data['role'] ?? 'user';
        $reason = $data['reason'] ?? '注册失败';
        
        if (!$username) {
            Log::warning('用户注册失败事件处理失败：username参数为空');
            return;
        }

        try {
            Log::error("用户 {$username} ({$role}) 注册失败", [
                'username' => $username,
                'role' => $role,
                'reason' => $reason,
                'register_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('用户注册失败事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 用户注销成功事件处理
     * @param array $data
     * @return void
     */
    public static function eventLogoutSuccess(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        
        if (!$member) {
            Log::warning('用户注销成功事件处理失败：member参数为空');
            return;
        }

        try {
            // 记录注销成功日志
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            $state->recordLoginLog(true, '用户注销成功');
            
            Log::info("用户 {$member->username} ({$role}) 注销成功", [
                'user_id' => $member->id,
                'username' => $member->username,
                'role' => $role,
                'logout_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('用户注销成功事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 用户注销失败事件处理
     * @param array $data
     * @return void
     */
    public static function eventLogoutFailure(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        $reason = $data['reason'] ?? '注销失败';
        
        if (!$member) {
            Log::warning('用户注销失败事件处理失败：member参数为空');
            return;
        }

        try {
            Log::error("用户 {$member->username} ({$role}) 注销失败", [
                'user_id' => $member->id,
                'username' => $member->username,
                'role' => $role,
                'reason' => $reason,
                'logout_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('用户注销失败事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 强制注销事件处理
     * @param array $data
     * @return void
     */
    public static function eventForceLogout(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        $operator = $data['operator'] ?? 'system';
        
        if (!$member) {
            Log::warning('强制注销事件处理失败：member参数为空');
            return;
        }

        try {
            // 记录强制注销日志
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            $state->recordLoginLog(true, '用户被强制注销');
            
            Log::info("用户 {$member->username} ({$role}) 被强制注销", [
                'user_id' => $member->id,
                'username' => $member->username,
                'role' => $role,
                'operator' => $operator,
                'force_logout_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('强制注销事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 角色切换事件处理
     * @param array $data
     * @return void
     */
    public static function eventRoleSwitched(array $data): void
    {
        $fromRole = $data['from_role'] ?? null;
        $toRole = $data['to_role'] ?? null;
        $activeRoles = $data['active_roles'] ?? [];
        
        if (!$fromRole || !$toRole) {
            Log::warning('角色切换事件处理失败：from_role或to_role参数为空');
            return;
        }

        try {
            Log::info("用户角色切换：从 {$fromRole} 切换到 {$toRole}", [
                'from_role' => $fromRole,
                'to_role' => $toRole,
                'active_roles' => $activeRoles,
                'switch_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('角色切换事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 角色一致性检查失败事件处理
     * @param array $data
     * @return void
     */
    public static function eventRoleConsistencyMismatch(array $data): void
    {
        $tokenRole = $data['token_role'] ?? null;
        $expectedRole = $data['expected_role'] ?? null;
        $requestPath = $data['request_path'] ?? null;
        
        if (!$tokenRole || !$expectedRole) {
            Log::warning('角色一致性检查失败事件处理失败：token_role或expected_role参数为空');
            return;
        }

        try {
            Log::warning("角色一致性检查失败：Token角色 {$tokenRole} 与期望角色 {$expectedRole} 不匹配", [
                'token_role' => $tokenRole,
                'expected_role' => $expectedRole,
                'request_path' => $requestPath,
                'check_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('角色一致性检查失败事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 用户菜单获取错误事件处理（已废除，只做简单日志记录）
     * @param array $data
     * @return void
     */
    public static function eventUserMenuGetError(array $data): void
    {
        $uid = $data['uid'] ?? null;
        $error = $data['error'] ?? null;
        $role = $data['role'] ?? null;
        
        // 只做简单日志记录，不进行复杂处理
        if ($uid && $error) {
            Log::info("用户菜单获取错误（已废除事件）：用户 {$uid} 在角色 {$role} 下获取菜单失败 - {$error}");
        }
    }
}