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
     * 记录登录失败事件处理（解耦版）
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
            // 触发状态更新事件，由状态管理器处理
            WebmanEvent::emit('member.state.update', [
                'member' => $member,
                'role' => $role,
                'action' => 'login_failure',
                'reason' => $reason
            ]);
            
            Log::info('登录失败记录成功', [
                'username' => $member->username, 
                'role' => $role, 
                'reason' => $reason,
                'login_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            return true;
        } catch (\Throwable $e) {
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
            // 统一状态管理：更新状态缓存和记录日志
            $state = new State();
            $state->role = $role;
            $state->memberModel = $member;
            
            // 更新状态缓存
            $state->updateStateCache();
            
            // 记录登录成功日志
            $state->recordLoginLog(true);
            
            Log::info("用户 {$member->username} ({$role}) 登录成功", [
                'member_id' => $member->id,
                'username' => $member->username,
                'role' => $role,
                'login_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('登录成功事件处理失败：' . $e->getMessage(), [
                'member_id' => $member->id ?? 'unknown',
                'role' => $role,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * 处理登录信息更新（统一使用Authenticator中的方法）
     * @param object $member 用户模型
     * @param string $role 用户角色
     * @return bool
     */
    private static function handleLoginInfoUpdate(object $member, string $role): bool
    {
        try {
            // 创建对应角色的认证器实例
            $authenticatorClass = $role === 'admin' ? 'support\member\role\admin\AdminAuthenticator' : 'support\member\role\user\UserAuthenticator';
            $authenticator = new $authenticatorClass();
            
            // 使用认证器中的方法更新登录信息
            return $authenticator->updateLoginInfo($member, $role);
            
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
        $errorMessage = $data['error_message'] ?? '认证失败';
        
        if (!$member) {
            Log::warning('登录失败事件处理失败：member参数为空');
            return;
        }

        try {
            // 直接调用记录登录失败方法，避免中间方法调用
            self::eventRecordLoginFailure([
                'member' => $member,
                'role' => $role,
                'reason' => $errorMessage
            ]);
            
            Log::warning("用户 {$member->username} ({$role}) 登录失败 - {$errorMessage}", [
                'member_id' => $member->id,
                'username' => $member->username,
                'role' => $role,
                'error_message' => $errorMessage,
                'login_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
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
            // 统一状态管理：记录注册成功日志
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
     * 状态更新事件处理
     * @param array $data
     * @return void
     */
    public static function eventStateUpdate(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        $action = $data['action'] ?? 'update';
        $reason = $data['reason'] ?? null;
        
        if (!$member) {
            Log::warning('状态更新事件处理失败：member参数为空');
            return;
        }

        try {
            Log::info("用户状态更新：用户 {$member->username} ({$role}) 状态更新 - {$action}", [
                'user_id' => $member->id,
                'username' => $member->username,
                'role' => $role,
                'action' => $action,
                'reason' => $reason,
                'update_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('状态更新事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 上下文清理事件处理
     * @param array $data
     * @return void
     */
    public static function eventContextCleared(array $data): void
    {
        $previousRole = $data['previous_role'] ?? null;
        $activeRolesCleared = $data['active_roles_cleared'] ?? [];
        
        try {
            Log::info("上下文清理：清理前角色 {$previousRole}，清理活跃角色：" . implode(', ', $activeRolesCleared), [
                'previous_role' => $previousRole,
                'active_roles_cleared' => $activeRolesCleared,
                'clear_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('上下文清理事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 初始化失败事件处理
     * @param array $data
     * @return void
     */
    public static function eventInitializationFailure(array $data): void
    {
        $role = $data['role'] ?? 'admin';
        $errorMessage = $data['error_message'] ?? '初始化失败';
        
        try {
            Log::error("初始化失败：角色 {$role} 初始化失败 - {$errorMessage}", [
                'role' => $role,
                'error_message' => $errorMessage,
                'failure_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ]);
            
        } catch (\Throwable $e) {
            Log::error('初始化失败事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 状态检查事件处理（合并版）
     * @param array $data
     * @return void
     */
    public static function eventStatusCheck(array $data): void
    {
        $member = $data['member'] ?? null;
        $role = $data['role'] ?? 'admin';
        $checkType = $data['check_type'] ?? 'login';
        $status = $data['status'] ?? 'start'; // start, success, failure
        $failureReason = $data['failure_reason'] ?? null;
        $failedItem = $data['failed_item'] ?? null;
        $checkItems = $data['check_items'] ?? [];
        
        if (!$member && $status !== 'start') {
            Log::warning('状态检查事件处理失败：member参数为空');
            return;
        }

        try {
            $logLevel = 'info';
            $message = "用户状态检查：用户 {$member?->username} ({$role}) {$checkType} 状态检查";
            
            switch ($status) {
                case 'start':
                    $message .= "开始";
                    break;
                case 'success':
                    $message .= "成功";
                    break;
                case 'failure':
                    $message .= "失败 - {$failureReason}";
                    $logLevel = 'warning';
                    break;
            }
            
            $logData = [
                'user_id' => $member?->id,
                'username' => $member?->username,
                'role' => $role,
                'check_type' => $checkType,
                'status' => $status,
                'check_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ];
            
            if ($status === 'success') {
                $logData['check_items'] = $checkItems;
            } elseif ($status === 'failure') {
                $logData['failure_reason'] = $failureReason;
                $logData['failed_item'] = $failedItem;
            }
            
            Log::$logLevel($message, $logData);
            
        } catch (\Throwable $e) {
            Log::error('状态检查事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 角色切换事件处理（合并版）
     * @param array $data
     * @return void
     */
    public static function eventRoleSwitch(array $data): void
    {
        $fromRole = $data['from_role'] ?? null;
        $toRole = $data['to_role'] ?? null;
        $status = $data['status'] ?? 'switched'; // switched, skipped, failed
        $reason = $data['reason'] ?? null;
        $currentRole = $data['current_role'] ?? null;
        $activeRoles = $data['active_roles'] ?? [];
        
        try {
            $logLevel = 'info';
            $message = "角色切换：";
            
            switch ($status) {
                case 'switched':
                    $message .= "从 {$fromRole} 切换到 {$toRole}";
                    break;
                case 'skipped':
                    $message .= "角色 {$toRole} 切换被跳过 - 原因：{$reason}";
                    break;
                case 'failed':
                    $message .= "尝试切换到 {$toRole} 失败，当前角色为 {$currentRole}";
                    $logLevel = 'warning';
                    break;
            }
            
            $logData = [
                'from_role' => $fromRole,
                'to_role' => $toRole,
                'status' => $status,
                'switch_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ];
            
            if ($status === 'switched') {
                $logData['active_roles'] = $activeRoles;
            } elseif ($status === 'skipped') {
                $logData['reason'] = $reason;
            } elseif ($status === 'failed') {
                $logData['current_role'] = $currentRole;
            }
            
            Log::$logLevel($message, $logData);
            
        } catch (\Throwable $e) {
            Log::error('角色切换事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 角色清理事件处理（合并版）
     * @param array $data
     * @return void
     */
    public static function eventRoleCleanup(array $data): void
    {
        $role = $data['role'] ?? null;
        $type = $data['type'] ?? 'single'; // single, batch_start, batch_complete, failed
        $wasCurrentRole = $data['was_current_role'] ?? false;
        $clearedRoles = $data['cleared_roles'] ?? [];
        $error = $data['error'] ?? null;
        $timestamp = $data['timestamp'] ?? microtime(true);
        
        try {
            $logLevel = 'info';
            $message = "角色清理：";
            
            switch ($type) {
                case 'single':
                    $message .= "清理角色 {$role}，是否当前角色：" . ($wasCurrentRole ? '是' : '否');
                    break;
                case 'batch_start':
                    $message .= "开始清理所有角色：" . implode(', ', $clearedRoles);
                    break;
                case 'batch_complete':
                    $message .= "所有角色清理完成：" . implode(', ', $clearedRoles);
                    break;
                case 'failed':
                    $message .= "清理角色 {$role} 失败 - {$error}";
                    $logLevel = 'error';
                    break;
            }
            
            $logData = [
                'role' => $role,
                'type' => $type,
                'clear_time' => date('Y-m-d H:i:s'),
                'ip' => request()->getRealIp()
            ];
            
            if ($type === 'single') {
                $logData['was_current_role'] = $wasCurrentRole;
            } elseif (in_array($type, ['batch_start', 'batch_complete'])) {
                $logData['cleared_roles'] = $clearedRoles;
                $logData['timestamp'] = $timestamp;
            } elseif ($type === 'failed') {
                $logData['error'] = $error;
            }
            
            Log::$logLevel($message, $logData);
            
        } catch (\Throwable $e) {
            Log::error('角色清理事件处理失败：' . $e->getMessage());
        }
    }


}