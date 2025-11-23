<?php


namespace support\member;

use app\exception\BusinessException;
use support\Log;
use support\orm\Db;
use support\StatusCode;
use support\member\interface\InterfaceState;
use Throwable;
use support\cache\Cache as WebmanCache;
use Webman\Event\Event as WebmanEvent;

/**
 * 统一状态管理器 - 负责所有登录状态检查和处理
 */
class State implements InterfaceState
{
    public bool $login = false;

    /**
     * @var string 用户类型
     */
    public string $role = 'admin';

    /**
     * 缓存前缀
     */
    protected string $cachePrefix = '';

    /**
     * 缓存时间（秒）
     */
    protected array $config = [];

    /**
     * @var string
     */
    protected static string $loginLogTable = '';

    //instance
    public mixed $memberModel;

    public function __construct()
    {
        $this->config = config('roles.state', []);
    }

    /**
     * 初始化依赖组件（简化版，State类不需要复杂的依赖初始化）
     * @return void
     */
    protected function initializeDependencies(): void
    {
        // State类主要依赖外部传入的memberModel
        // 这里只需要确保基础配置已加载
        if (empty($this->config)) {
            $this->config = config('roles.state', []);
        }
        
        // 如果角色未设置，尝试自动检测
        if (empty($this->role)) {
            $this->role = $this->detectMemberRole();
        }
    }


    /**
     * 统一检查用户状态
     * @param mixed $member 用户模型
     * @param string $checkType 检查类型
     * @return bool
     * @throws BusinessException
     */
    public function checkStatus($member, string $checkType = 'login'): bool
    {
        $this->memberModel = $member;
        
        // 触发状态检查开始事件
        WebmanEvent::emit('member.status_check.start', [
            'member' => $this->memberModel,
            'role' => $this->role,
            'check_type' => $checkType
        ]);

        $checkItems = [];
        
        try {
            // 检查账号状态
            if ($this->memberModel->status !== 'enable') {
                throw new BusinessException('账号已被禁用', StatusCode::USER_DISABLED, true);
            }
            $checkItems[] = '账号状态检查通过';
            
            // 检查登录失败次数
            $this->checkLoginFailures();
            $checkItems[] = '登录失败次数检查通过';
            
            // 检查单点登录状态
            $this->checkSsoStatus();
            $checkItems[] = '单点登录状态检查通过';
            
            // 触发状态检查成功事件
            WebmanEvent::emit('member.status_check.success', [
                'member' => $this->memberModel,
                'role' => $this->role,
                'check_type' => $checkType,
                'check_items' => $checkItems
            ]);
            
            return true;
            
        } catch (BusinessException $e) {
            // 触发状态检查失败事件
            WebmanEvent::emit('member.status_check.failure', [
                'member' => $this->memberModel,
                'role' => $this->role,
                'check_type' => $checkType,
                'failure_reason' => $e->getMessage(),
                'failed_item' => end($checkItems) ?: '初始检查'
            ]);
            
            throw $e;
        }
    }

    /**
     * 统一记录登录失败
     * @param mixed $member 用户模型
     * @param string|null $reason 失败原因
     * @return bool
     */
    public function recordLoginFailure($member, ?string $reason = null): bool
    {
        try {
            $this->memberModel = $member;
            $this->memberModel->startTrans();

            $loginFailure = $this->memberModel->login_failure ?? 0;
            $this->memberModel->login_failure = (int)$loginFailure + 1;
            // 注意：登录失败时不更新登录时间和IP，只在登录成功时更新
            $this->memberModel->save();

            // 记录登录日志
            $this->recordLoginLog(false, $reason);

            $this->memberModel->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->memberModel && method_exists($this->memberModel, 'rollback')) {
                $this->memberModel->rollback();
            }
            Log::error('记录登录失败信息失败：' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 检查单点登录状态
     * @throws BusinessException
     */
    protected function checkSsoStatus(): void
    {
        $roleConfig = config('roles.roles.' . $this->role, []);
        $ssoConfig = $roleConfig['login']['sso'] ?? false;
        if ($ssoConfig) {
            // 单点登录模式下，检查是否在其他地方登录
            $cacheKey = $this->getStateCacheKey();
            $stateData = WebmanCache::get($cacheKey);
            
            if ($stateData && isset($stateData['token']) && $stateData['token'] !== request()->token) {
                throw new BusinessException('账号已在其他地方登录', StatusCode::MEMBER_LOGGED_IN_ELSEWHERE, true);
            }
        }
    }

    /**
     * 检查登录失败
     * By albert  2025/05/06 00:33:08
     * @return bool
     * @throws BusinessException
     */
    public function checkLoginFailures(): bool
    {
        $roleConfig = config('roles.roles.' . $this->role, []);
        $maxRetry = $roleConfig['login']['max_retry'] ?? 10;
        $lockTime = $roleConfig['login']['lock_time'] ?? 3600;

        $loginFailure = $this->memberModel->login_failure ?? 0;
        $lastLoginTime = $this->memberModel->last_login_time ?? 0;
        
        // 确保 last_login_time 是时间戳格式
        if (!is_numeric($lastLoginTime)) {
            // 如果是字符串格式的时间，转换为时间戳
            $lastLoginTime = is_string($lastLoginTime) ? strtotime($lastLoginTime) : 0;
        }
        $lastLoginTime = (int)$lastLoginTime;
        
        if ((int)$loginFailure >= $maxRetry) {
            $unlockTime = $lastLoginTime + $lockTime;

            if (time() < $unlockTime) {
                throw new BusinessException(
                    "账号已锁定，请在" . ($unlockTime - time()) . "秒后重试",
                    StatusCode::LOGIN_ACCOUNT_LOCKED
                );
            }
            $this->memberModel->login_failure = 0;
            $this->memberModel->save();
        }
        return true;
    }

    /**
     * 更新登录状态（已废弃，统一使用事件处理）
     * @deprecated 请使用事件系统处理登录状态更新
     * @param        $member
     * @param string $success
     * @return bool
     */
    public function updateLoginState($member, string $success): bool
    {
        Log::warning('updateLoginState方法已废弃，请使用事件系统处理登录状态更新');
        return false;
    }
    
    /**
     * 更新状态缓存
     */
    public function updateStateCache(): void
    {
        $cacheKey = $this->getStateCacheKey();
        $cacheTime = $this->config['state']['cache_time'] ?? 86400;
        
        $stateData = [
            'user_id' => $this->memberModel->id,
            'token' => request()->token,
            'last_activity' => time(),
            'role' => $this->role
        ];
        
       //cache 的缓存函数
       WebmanCache::set($cacheKey, $stateData, $cacheTime);
    }


    public function recordLoginLog(bool $success, ?string $reason = null): void
    {
        try {
            $tableName = $this->getLoginLogTableName();
            
            // 检查表是否存在，如果不存在则跳过记录
            if (!$this->tableExists($tableName)) {
                return;
            }
            
            $logData = [
                'user_id'     => $this->memberModel->id,
                'username'    => $this->memberModel->username,
                'ip'          => request()->getRealIp(),
                'user_agent'  => request()->header('user-agent'),
                'success'     => $success,
                'create_time' => time()
            ];
            
            if (!$success && $reason) {
                $logData['message'] = $reason;
            }
            
            Db::name($tableName)->insert($logData);
        } catch (Throwable $e) {
            Log::error('记录登录日志失败：' . $e->getMessage());
        }
    }
    
    /**
     * 检查表是否存在
     */
    protected function tableExists(string $tableName): bool
    {
        try {
            $result = Db::query("SHOW TABLES LIKE '{$tableName}'");
            return !empty($result);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * 获取登录日志表名
     */
    protected function getLoginLogTableName(): string
    {
        return $this->role . '_login_log';
    }


    /**
     * 强制下线用户
     * @param int $userId
     * @return bool
     */
    public function forceLogout(int $userId): bool
    {
        try {
            $user = $this->memberModel->find($userId);
            if (!$user) {
                throw new BusinessException('用户不存在', StatusCode::USER_NOT_FOUND);
            }

            // 清除状态缓存
            $this->memberModel = $user;
            $cacheKey = $this->getStateCacheKey();
           WebmanCache::delete($cacheKey);

            // 清除刷新令牌
            $user->refresh_token = null;
            $user->save();

            return true;
        } catch (BusinessException $e) {
            throw $e;
        } catch (Throwable) {
            throw new BusinessException('操作失败，请稍后重试', StatusCode::SERVER_ERROR);
        }
    }
    
    /**
     * 清除指定角色的所有状态缓存
     * @param string $role
     * @return bool
     */
    public function clearRoleStates(string $role): bool
    {
        try {
            $pattern = $this->config['state']['prefix'] . "{$role}-*";
            // 这里需要根据具体的缓存驱动实现清除逻辑
            // 例如使用 Redis 的 keys 命令或 scan 命令
            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * 自动检测用户类型（优化版）
     * @return string
     */
    protected function detectMemberRole(): string
    {
        // 如果当前角色已设置且有效，直接返回
        if (!empty($this->role) && in_array($this->role, ['admin', 'user'])) {
            return $this->role;
        }
        
        // 如果用户模型已初始化且有角色属性，使用模型中的角色
        if ($this->memberModel && property_exists($this->memberModel, 'role') && !empty($this->memberModel->role)) {
            return $this->memberModel->role;
        }
        
        // 从类名推断角色（备用方案）
        $className = static::class;
        if (strpos($className, 'Admin') !== false) {
            return 'admin';
        }
        if (strpos($className, 'User') !== false) {
            return 'user';
        }
        
        // 默认返回管理员角色
        return 'admin';
    }

    /**
     * 获取缓存键（增强唯一性保障）
     * By albert  2025/05/06 04:47:33
     * @return string
     */
    protected function getStateCacheKey(): string
    {
        $this->initCachePrefix();
        
        // 增强唯一性保障：添加应用标识和用户类型哈希
        $appIdentifier = config('app.name', 'webman-radmin');
        $userTypeHash = substr(md5($this->role), 0, 8);
        
        return $this->cachePrefix . "{$appIdentifier}-{$userTypeHash}-{$this->role}-{$this->memberModel->id}";
    }

    /**
     * 初始化缓存前缀
     * By albert  2025/05/06 04:03:36
     * @return void
     */
    protected function initCachePrefix(): void
    {
        if (empty($this->cachePrefix)) {
            $this->cachePrefix = $this->config['state']['prefix'] ?? 'state-';
        }
    }


}
