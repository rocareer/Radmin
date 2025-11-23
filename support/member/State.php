<?php


namespace support\member;

use app\exception\BusinessException;
use support\Log;
use support\orm\Db;
use support\StatusCode;
use Throwable;
use support\cache\Cache as WebmanCache;

/**
 * 统一状态管理器 - 负责所有登录状态检查和处理
 */
class State
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
    protected mixed $memberModel;

    public function __construct()
    {
        $this->config = config('roles.state', []);
    }


    /**
     * 统一检查用户状态
     * @param mixed $member 用户模型
     * @return bool
     * @throws BusinessException
     */
    public function checkStatus($member): bool
    {
        $this->memberModel = $member;

        // 检查账号状态
        if ($this->memberModel->status !== 'enable') {
            throw new BusinessException('账号已被禁用', StatusCode::USER_DISABLED, true);
        }
        
        // 检查登录失败次数
        $this->checkLoginFailures();
        
        // 检查单点登录状态
        $this->checkSsoStatus();
        
        return true;
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
                throw new BusinessException('账号已在其他地方登录', StatusCode::USER_LOGGED_IN_ELSEWHERE, true);
            }
        }
    }

    /**
     * 检查登录失败
     * By albert  2025/05/06 00:33:08
     * @return bool
     * @throws BusinessException
     */
    protected function checkLoginFailures(): bool
    {
        $roleConfig = config('roles.roles.' . $this->role, []);
        $maxRetry = $roleConfig['login']['max_retry'] ?? 10;
        $lockTime = $roleConfig['login']['lock_time'] ?? 3600;
        
        $loginFailure = $this->memberModel->login_failure ?? 0;
        $lastLoginTime = $this->memberModel->last_login_time ?? 0;
        
        if ((int)$loginFailure >= $maxRetry) {
            $unlockTime = (int)$lastLoginTime + $lockTime;

            if (time() < $unlockTime) {
                throw new BusinessException(
                    "账号已锁定，请在" . ($unlockTime - time()) . "秒后重试",
                    StatusCode::LOGIN_ACCOUNT_LOCKED, true
                );
            }
            $this->memberModel->login_failure = 0;
            $this->memberModel->save();
        }
        return true;
    }

    /**
     * 更新登录信息
     * By albert  2025/05/06 02:50:25
     * @param        $member
     * @param string $success
     * @return bool
     */
    public function updateLoginState($member, string $success): bool
    {
        try {
            // 检查member参数是否有效
            if (!$member) {
                Log::error('更新登录状态失败：member参数为空');
                return false;
            }
            
            $this->memberModel = $member;
            $this->memberModel->startTrans();

            if ($success === 'state.updateLogin.success') {
                $this->memberModel->login_failure = 0;
                
                // 登录成功时更新状态缓存
                $this->updateStateCache();
                
                // 记录登录日志
                $this->recordLoginLog(true);
            } else {
                // 安全处理登录失败次数，避免非数值错误
                $loginFailure = $this->memberModel->login_failure ?? 0;
                $this->memberModel->login_failure = (int)$loginFailure + 1;
                
                // 登录失败时记录日志
                $this->recordLoginLog(false);
            }

            // 注意：登录时间和IP的更新已移至登录成功事件中统一处理
            // 这里只保存模型状态
            $this->memberModel->save();

            $this->memberModel->commit();
            return true;
        } catch (Throwable $e) {
            // 只有在事务已启动时才回滚
            if ($this->memberModel && method_exists($this->memberModel, 'rollback')) {
                $this->memberModel->rollback();
            }
            Log::error('更新登录状态失败：' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 更新状态缓存
     */
    protected function updateStateCache(): void
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


    protected function recordLoginLog(bool $success, ?string $reason = null): void
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
     * 自动检测用户类型
     * @return string
     */
    protected function detectMemberRole(): string
    {
        $this->initializeDependencies();
        if (property_exists($this->memberModel, 'role')) {
            return $this->memberModel->role;
        }
        return strtolower(str_replace('StateManager', '', static::class));
    }

    /**
     * 获取缓存键
     * By albert  2025/05/06 04:47:33
     * @return string
     */
    protected function getStateCacheKey(): string
    {
        $this->initCachePrefix();
        return $this->cachePrefix . "{$this->role}-{$this->memberModel->id}";
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
