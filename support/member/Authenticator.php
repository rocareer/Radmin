<?php


namespace support\member;

use app\exception\BusinessException;
use app\exception\TokenException;
use app\exception\TokenExpiredException;
use app\exception\UnauthorizedHttpException;
use support\cache\Cache;
use support\Log;
use support\member\interface\InterfaceAuthenticator;
use support\member\role\admin\AdminModel;
use support\member\role\user\UserModel;
use support\orm\Db;
use support\RequestContext;
use support\StatusCode;
use support\token\Token;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Throwable;
use Webman\Event\Event;


/**
 * 认证器抽象基类
 */
abstract class Authenticator implements InterfaceAuthenticator
{
    /**
     * @var string 用户模型类型
     */
    protected string $role = 'user';

    /**
     * @var array 登录凭证
     */
    protected array $credentials;

    /**
     * @var array
     */
    protected array $config = [];

    public object|null $memberModel = null;

    /**
     * @var State|null 状态管理器实例缓存
     */
    protected ?State $stateInstance = null;

    /**
     * @throws BusinessException
     */
    public function __construct()
    {
        $this->config = config('roles.roles.' . $this->role . '.login', []);
        $this->initializeDependencies();
    }

    /**
     * 初始化依赖组件
     *
     * @return void
     */
    protected function initializeDependencies(): void
    {
        // 初始化用户模型
        if (!$this->memberModel) {
            $this->memberModel = $this->createModel($this->role);
        }
    }

    /**
     * 创建模型实例
     */
    protected function createModel(string $role): object
    {
        if ($role === 'admin') {
            return new AdminModel();
        }
        return new UserModel();
    }

    /**
     * 认证用户（优化版）
     *
     * @param array $credentials
     *
     * @return object
     * @throws UnauthorizedHttpException
     */
    public function authenticate(array $credentials): object
    {
        $this->credentials = $credentials;

        try {
            Db::startTrans();

            // 使用统一的认证流程方法
            $this->performAuthenticationSteps();

            // 处理认证成功事件
            $this->handleAuthenticationSuccess();

            Db::commit();
            return $this->memberModel;

        } catch (UnauthorizedHttpException $e) {
            $this->handleAuthenticationFailure($e);
            throw $e;
        } catch (BusinessException $e) {
            // 如果是账号锁定异常，直接抛出，不包装成UnauthorizedHttpException
            if ($e->getCode() === StatusCode::LOGIN_ACCOUNT_LOCKED) {
                $this->handleAuthenticationFailure($e);
                throw $e;
            }
            $this->handleAuthenticationFailure($e);
            throw new UnauthorizedHttpException(
                $e->getMessage(), $e->getCode(), [], $e
            );
        } catch (Throwable $e) {
            $this->handleAuthenticationFailure($e);
            Log::error('认证异常：' . $e->getMessage());
            throw new UnauthorizedHttpException(
                '系统错误，请稍后重试', StatusCode::AUTHENTICATION_FAILED, [], $e
            );
        }
    }


    /**
     * 执行认证流程
     *
     * @author Albert <albert@rocareer.com>
     * @time   2025/11/24 00:20
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws UnauthorizedHttpException
     * @throws BusinessException
     */
    protected function performAuthenticationSteps(): void
    {
        // 认证流程
        $this->validateCredentials();      // 1. 验证基本凭证
        $this->validateCaptcha();          // 2. 验证验证码
        $this->findMember();               // 3. 查找用户
        $this->checkMemberStatus();        // 4. 检查用户状态
        $this->checkLoginRetryLimit();     // 5. 检查登录失败次数限制
        $this->verifyPassword();           // 6. 验证密码
        $this->extendMemberInfo();         // 7. 扩展用户信息
        $this->generateTokens();           // 8. 生成令牌
        $this->updateLoginInfo();          // 9. 更新登录信息
    }

    /**
     * 处理认证成功事件
     *
     * @author Albert <albert@rocareer.com>
     * @time   2025/11/24 00:47
     * @return void
     */
    private function handleAuthenticationSuccess(): void
    {
        // 登录成功事件 - 规范化事件格式
        Event::emit("member.login_success", [
            'member'     => $this->memberModel,
            'role'       => $this->role,
            'success'    => true,
            'timestamp'  => microtime(true),
            'event_type' => 'authentication'
        ]);
    }

    /**
     * 处理认证失败
     *
     * @param Throwable $e
     */
    private function handleAuthenticationFailure(Throwable $e): void
    {
        try {
            Db::rollback();

            // 如果是账号锁定异常，不触发登录失败事件（避免重置锁定时间）
            if ($e instanceof BusinessException
                && $e->getCode() === StatusCode::LOGIN_ACCOUNT_LOCKED
            ) {
                Log::info('账号已锁定，跳过登录失败事件处理');
                return;
            }

            // 登录失败事件 - 规范化事件格式
            Event::emit("member.login_failure", [
                'member'        => $this->memberModel,
                'role'          => $this->role,
                'success'       => false,
                'timestamp'     => microtime(true),
                'event_type'    => 'authentication',
                'error_message' => $e->getMessage()
            ]);
        } catch (Throwable $rollbackError) {
            Log::error('认证失败回滚异常：' . $rollbackError->getMessage());
        }
    }

    /**
     * 验证基本凭证
     * By albert  2025/05/06 01:51:31
     *
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function validateCredentials(): void
    {
        if (empty($this->credentials['username'])) {
            throw new UnauthorizedHttpException(
                '用户名不能为空', StatusCode::USERNAME_REQUIRED
            );
        }

        if (empty($this->credentials['password'])) {
            throw new UnauthorizedHttpException(
                '密码不能为空', StatusCode::PASSWORD_REQUIRED
            );
        }
    }

    /**
     * 验证验证码
     * By albert  2025/05/06 04:00:32
     *
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function validateCaptcha(): void
    {
        if ($this->config['captcha'] && empty($this->credentials['captcha'])) {
            throw new UnauthorizedHttpException(
                '验证码不能为空', StatusCode::CAPTCHA_REQUIRED
            );
        }
    }

    /**
     * 获取用户
     * By albert  2025/05/06 04:00:02
     *
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws UnauthorizedHttpException
     */
    protected function findMember(): void
    {
        $user = $this->memberModel->findByName($this->credentials['username']);
        if (!$user) {
            throw new UnauthorizedHttpException(
                '用户不存在', StatusCode::USER_NOT_FOUND
            );
        }

        $this->memberModel = $user;

    }

    /**
     * 获取状态管理器实例（缓存优化）
     */
    protected function getState(): State
    {
        if (!$this->stateInstance) {
            $this->stateInstance              = new State();
            $this->stateInstance->role        = $this->role;
            $this->stateInstance->memberModel = $this->memberModel;
        }
        return $this->stateInstance;
    }

    /**
     * 检查用户状态
     */
    protected function checkMemberStatus(): void
    {
        // 使用缓存的状态管理器实例检查用户状态
        $this->getState()->checkStatus($this->memberModel, 'login');
    }

    /**
     * 检查登录失败次数限制（在密码验证前调用）
     */
    protected function checkLoginRetryLimit(): void
    {
        // 使用缓存的状态管理器实例检查登录失败次数
        $this->getState()->checkLoginFailures();
    }

    /**
     * 验证密码
     * By albert  2025/05/06 01:33:02
     *
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function verifyPassword(): void
    {
        $res = $this->memberModel->verifyPassword(
            $this->credentials['password'], $this->memberModel
        );
        if (!$res) {
            // 获取角色配置
            $roleConfig = config('roles.roles.' . $this->role, []);
            $maxRetry   = $roleConfig['login']['max_retry'] ?? 10;

            // 获取当前登录失败次数
            $currentFailures = $this->memberModel->login_failure ?? 0;

            // 计算剩余重试次数
            $remainingRetries = max(0, $maxRetry - $currentFailures - 1);

            if ($remainingRetries > 0) {
                throw new UnauthorizedHttpException(
                    "密码错误，您还有 {$remainingRetries} 次重试机会",
                    StatusCode::PASSWORD_ERROR
                );
            } else {
                throw new UnauthorizedHttpException(
                    '账号已锁定，请稍后重试', StatusCode::PASSWORD_ERROR
                );
            }
        }
    }


    /**
     * 生成令牌
     *
     * @return void
     * @throws
     */
    protected function generateTokens(): void
    {
        try {
            // 基础Token数据
            $tokenData = [
                'sub'   => $this->memberModel->id,
                'type'  => 'access',
                'keep'  => false,
                'role'  => $this->role,
                'roles' => $this->memberModel->roles ?? [$this->role]
            ];
            
            // 如果是超级管理员，确保roles包含'super'角色
            if ($this->role === 'admin' && \support\member\Member::isSuperAdmin($this->memberModel->id)) {
                if (!in_array('super', $tokenData['roles'])) {
                    $tokenData['roles'][] = 'super';
                }
            }

            // 生成访问令牌
            $this->memberModel->token = Token::encode($tokenData);

            // 根据需要生成刷新令牌
            if (!empty($this->credentials['keep'])) {
                $tokenData['keep']                = true;
                $this->memberModel->refresh_token = Token::encode($tokenData);
            }
            Log::info('生成令牌成功', [
                'id'        => $this->memberModel->id,
                'name'      => $this->memberModel->username,
                'role'      => $this->role,
                'timestamp' => microtime(true),
                'event_type'=>'generate_token'
            ]);
        } catch (Throwable $e) {
            Log::error('生成令牌失败', [
                    'id'        => $this->memberModel->id,
                    'name'      => $this->memberModel->username,
                    'role'      => $this->role,
                    'timestamp' => microtime(true),
                    'error'     => $e->getMessage(),
                    'trace'     => $e->getTraceAsString(),
                    'event_type'=>'generate_token'

                ]
            );
            throw new BusinessException(
                '生成令牌失败', StatusCode::TOKEN_GENERATE_FAILED
            );
        }

    }

    public function extendMemberInfo(): void
    {
        $this->memberModel->roles = [$this->role];
    }


    /**
     * 刷新认证令牌
     *
     * @param string $refreshToken
     *
     * @return string
     * @throws UnauthorizedHttpException
     */
    public function refreshToken(string $refreshToken): string
    {
        try {
            Db::startTrans();

            // 验证用户
            $this->validateRefreshToken($refreshToken);

            // 检查状态
            $this->checkMemberStatus();

            // 生成新令牌
            $this->generateTokens();

            // todo 记录操作日志
            Log::info('刷新令牌成功', [
                'id'         => $this->memberModel->id,
                'name'       => $this->memberModel->username,
                'role'       => $this->role,
                'success'    => true,
                'timestamp'  => microtime(true),
                'event_type' => 'refresh_token',
            ]);
            Db::commit();
            return $this->memberModel->token;

        } catch (Throwable $e) {
            Db::rollback();
            Log::error('刷新令牌失败', [
                'id'         => $this->memberModel->id,
                'name'       => $this->memberModel->username,
                'role'       => $this->role,
                'success'    => false,
                'timestamp'  => microtime(true),
                'event_type' => 'refresh_token',
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString()
            ]);
            throw new UnauthorizedHttpException(
                '系统错误，请稍后重试', StatusCode::SERVER_ERROR
            );
        }
    }

    /**
     * 验证刷新令牌
     *
     * @param string $refreshToken
     *
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function validateRefreshToken(string $refreshToken): void
    {
        $payload = Token::verify($refreshToken);
        if (empty($payload['user_id'])) {
            throw new UnauthorizedHttpException(
                '无效的刷新令牌', StatusCode::TOKEN_INVALID, true
            );
        }

        $user = $this->memberModel->findById($payload['user_id']);
        if (!$user) {
            throw new UnauthorizedHttpException(
                '用户不存在', StatusCode::USER_NOT_FOUND, true
            );
        }
    }

    /**
     * 注销登录
     *
     * @param object $member
     * @param string $role *
     *
     * @return bool
     * @throws BusinessException
     */
    public function logout(object $member, string $role): bool
    {
        try {
            if (!$this->memberModel) {
                return true;
            }

            // 清除用户状态
            $this->memberModel->refresh_token = null;
            $this->memberModel->save();

            // 清除状态缓存
            $this->clearStateCache();

            // 记录操作日志
            Log::info('注销成功', [
                'id'         => $this->memberModel->id,
                'name'       => $this->memberModel->username,
                'role'       => $this->role,
                'success'    => true,
                'timestamp'  => microtime(true),
                'event_type' => 'logout',
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('注销失败', [
                'id'        => $this->memberModel->id,
                'name'      => $this->memberModel->username,
                'role'      => $this->role,
                'error'     => $e->getMessage(),
                'timestamp' => microtime(true),
                'trace'     => $e->getTraceAsString(),
                'event_type' => 'logout',
            ]);
            throw new BusinessException(
                '注销失败，请稍后重试', StatusCode::SERVER_ERROR
            );
        }
    }

    /**
     * 清除状态缓存
     */
    protected function clearStateCache(): void
    {
        $cacheKey = $this->getStateCacheKey();
        Cache::delete($cacheKey);
    }

    /**
     * 获取状态缓存键（与State类保持一致）
     */
    protected function getStateCacheKey(): string
    {
        $config = config('roles.state', []);
        $prefix = $config['prefix'] ?? 'state-';

        // 与State类保持一致：添加应用标识和用户类型哈希
        $appIdentifier = config('app.name', 'webman-radmin');
        $userTypeHash  = substr(md5($this->role), 0, 8);

        return $prefix
            . "{$appIdentifier}-{$userTypeHash}-{$this->role}-{$this->memberModel->id}";
    }


    /**
     * 强制下线用户
     *
     * @param int $userId
     *
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws UnauthorizedHttpException
     */
    public function forceLogout(int $userId): bool
    {
        $user = $this->memberModel->find($userId);
        if (!$user) {
            throw new UnauthorizedHttpException(
                '用户不存在', StatusCode::USER_NOT_FOUND
            );
        }

        //todo
        // $stateManager = new $this->stateManager($user);
        // $result       = $stateManager->forceLogout($userId);

        // 记录操作日志
        Log::info('强制下线成功', [
            'id'         => $user->id,
            'name'       => $user->username,
            'role'       => $this->role,
            'success'    => true,
            'timestamp'  => microtime(true),
            'event_type' => 'force_logout',
        ]);

        // return $result;
        return true;
    }

    /**
     * 用户信息初始化（从Service迁移）
     *
     * @param string|null $token
     *
     * @throws UnauthorizedHttpException
     */
    public function memberInitialization(?string $token = null): void
    {
        try {
            $request = request();

            // 获取并验证Token
            $payload = $this->getValidatedTokenPayload($token);

            // 验证用户存在性
            $member = $this->validateUserExists($payload->sub);

            // 设置用户信息
            $this->memberModel = $member;
            
            // 扩展用户信息，添加roles和super字段
            $this->extendMemberInfo();
            
            Log::info('用户信息初始化成功', [
                'id'         => $member->id,
                'name'       => $member->username,
                'role'       => $this->role,
                'success'    => true,
                'timestamp'  => microtime(true),
                'event_type' => 'member_initialization',
            ]);

        } catch (TokenExpiredException $e) {
            Log::error('用户信息初始化失败', [
                'id'         => $member->id,
                'name'       => $member->username,
                'role'       => $this->role,
                'error'     => $e->getMessage(),
                'timestamp' => microtime(true),
                'event_type' => 'member_initialization',
            ]);
            throw new TokenException('Token已过期', StatusCode::TOKEN_EXPIRED);
        } catch (Throwable $e) {
            throw new UnauthorizedHttpException(
                $e->getMessage(), StatusCode::NEED_LOGIN
            );
        }
    }

    /**
     * 获取并验证Token载荷（从Service迁移）
     *
     * @param string|null $token
     *
     * @return object
     * @throws UnauthorizedHttpException
     */
    private function getValidatedTokenPayload(?string $token): object
    {
        $request = request();

        // 优先使用请求中的payload
        if (isset($request->payload) && isset($request->payload->sub)) {
            return $request->payload;
        }

        // 从Token中获取payload
        $token = $token ?? $request->token();
        if (!$token) {
            throw new UnauthorizedHttpException(
                '请先登录', StatusCode::NEED_LOGIN
            );
        }

        $payload = Token::verify($token);
        if (!$payload || !isset($payload->sub)) {
            throw new UnauthorizedHttpException(
                '无效的Token', StatusCode::NEED_LOGIN
            );
        }

        // 缓存payload到请求对象
        $request->payload = $payload;
        return $payload;
    }

    /**
     * 验证用户存在性（从Service迁移）
     *
     * @param mixed $userId
     *
     * @return object
     * @throws UnauthorizedHttpException
     */
    private function validateUserExists($userId): object
    {
        if (empty($userId)) {
            throw new UnauthorizedHttpException(
                '用户ID不能为空', StatusCode::NEED_LOGIN
            );
        }

        $member = $this->memberModel->findById($userId);
        if (empty($member)) {
            throw new UnauthorizedHttpException(
                '用户不存在', StatusCode::NEED_LOGIN
            );
        }

        return $member;
    }


    /**
     * 更新登录信息
     *
     * @author Albert <albert@rocareer.com>
     * @time   2025/11/24 00:24
     * @return bool
     */
    public function updateLoginInfo(): bool
    {
        try {
            // 获取当前时间和IP
            $currentTime = time();
            $currentIp   = request()->getRealIp();

            $member = $this->memberModel;
            // 开始事务
            $member->startTrans();

            try {
                // 使用带下划线的字段名，保存时会自动映射到数据库字段
                $member->last_login_time = $currentTime;
                $member->last_login_ip   = $currentIp;
                $member->login_failure   = 0; // 重置登录失败次数

                // 保存更改
                $member->save();

                // 提交事务
                $member->commit();

                Log::info("用户登录信息更新成功", [
                    'member_id'       => $member->id,
                    'role'            => $this->role,
                    'last_login_time' => date('Y-m-d H:i:s', $currentTime),
                    'last_login_ip'   => $currentIp
                ]);

                return true;

            } catch (\Throwable $e) {
                // 回滚事务
                $member->rollback();
                throw $e;
            }

        } catch (\Throwable $e) {
            Log::error('登录信息更新失败：' . $e->getMessage(), [
                'member_id' => $member->id ?? 'unknown',
                'role'      => $this->role,
                'trace'     => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * 记录登录更新日志（从Service迁移）
     *
     * @param object      $member            用户模型
     * @param string      $role              用户角色
     * @param int         $currentTime       当前登录时间
     * @param string      $currentIp         当前登录IP
     * @param int|null    $previousLoginTime 上次登录时间
     * @param string|null $previousLoginIp   上次登录IP
     */
    private function logLoginUpdate(
        object $member,
        string $role,
        int $currentTime,
        string $currentIp,
        ?int $previousLoginTime,
        ?string $previousLoginIp
    ): void {
        $logData = [
            'user_id'             => $member->id,
            'username'            => $member->username ??
                    $member->name ?? 'unknown',
            'role'                => $role,
            'current_login_time'  => date('Y-m-d H:i:s', $currentTime),
            'current_login_ip'    => $currentIp,
            'previous_login_time' => $previousLoginTime ? date(
                'Y-m-d H:i:s', $previousLoginTime
            ) : '首次登录',
            'previous_login_ip'   => $previousLoginIp ?? '首次登录'
        ];

        // 检查是否为异常登录（不同IP或短时间内的多次登录）
        $isAbnormalLogin = false;
        if ($previousLoginTime && $previousLoginIp) {
            $timeDiff  = $currentTime - $previousLoginTime;
            $ipChanged = $previousLoginIp !== $currentIp;

            // 如果IP变化或短时间内多次登录，标记为异常
            if ($ipChanged || $timeDiff < 300) { // 5分钟内
                $isAbnormalLogin              = true;
                $logData['abnormal_login']    = true;
                $logData['time_diff_minutes'] = round($timeDiff / 60, 2);
                $logData['ip_changed']        = $ipChanged;
            }
        }

        if ($isAbnormalLogin) {
            Log::warning('检测到异常登录行为', $logData);
        } else {
            Log::info('用户登录信息更新成功', $logData);
        }
    }

    /**
     * 获取用户登录历史摘要（从Service迁移）
     *
     * @param int    $userId 用户ID
     * @param string $role   用户角色
     *
     * @return array
     */
    protected function getLoginHistorySummary(int $userId, string $role): array
    {
        try {
            // 从缓存或数据库获取登录历史
            $cacheKey = "login_history_{$role}_{$userId}";
            $summary  = RequestContext::get($cacheKey);

            if (!$summary) {
                // 这里可以从数据库获取更详细的登录历史
                // 简化实现，只返回基本信息
                $summary = [
                    'user_id'              => $userId,
                    'role'                 => $role,
                    'last_login_time'      => null,
                    'last_login_ip'        => null,
                    'login_count_today'    => 0,
                    'abnormal_login_count' => 0
                ];

                // 缓存摘要信息
                RequestContext::set($cacheKey, $summary);
            }

            return $summary;

        } catch (\Throwable $e) {
            Log::error('获取登录历史摘要失败：' . $e->getMessage());
            return [];
        }
    }

    /**
     * 更新登录历史摘要（从Service迁移）
     *
     * @param object      $member          用户模型
     * @param string      $role            用户角色
     * @param int         $loginTime       登录时间
     * @param string      $loginIp         登录IP
     * @param string|null $previousLoginIp 上次登录IP
     */
    private function updateLoginHistorySummary(object $member, string $role,
        int $loginTime, string $loginIp, ?string $previousLoginIp
    ): void {
        // 更新登录历史摘要
        $summary = $this->getLoginHistorySummary($member->id, $role);

        // 更新今日登录次数
        $today         = date('Y-m-d', $loginTime);
        $lastLoginDate = date('Y-m-d', $summary['last_login_time'] ?? 0);

        if ($today === $lastLoginDate) {
            $summary['login_count_today'] = ($summary['login_count_today'] ?? 0)
                + 1;
        } else {
            $summary['login_count_today'] = 1;
        }

        // 更新摘要信息
        $summary['last_login_time'] = $loginTime;
        $summary['last_login_ip']   = $loginIp;

        // 检查异常登录
        if ($previousLoginIp && $previousLoginIp !== $loginIp) {
            $summary['abnormal_login_count'] = ($summary['abnormal_login_count']
                    ?? 0) + 1;

            // 记录IP变更事件
            Event::emit('security.ip_changed', [
                'member'      => $member,
                'role'        => $role,
                'previous_ip' => $previousLoginIp,
                'current_ip'  => $loginIp,
                'timestamp'   => microtime(true)
            ]);
        }

        // 缓存更新后的摘要
        $cacheKey = "login_history_{$role}_{$member->id}";
        RequestContext::set($cacheKey, $summary);

        Log::debug('登录历史摘要已更新', [
            'member_id'            => $member->id,
            'role'                 => $role,
            'login_count_today'    => $summary['login_count_today'],
            'abnormal_login_count' => $summary['abnormal_login_count']
        ]);
    }

    /**
     * 注销登录 - 支持多角色隔离注销（从Service迁移）
     *
     * @return bool
     */
    public function logoutWithToken(): bool
    {
        try {
            $currentToken = request()->token;

            // 销毁当前角色的Token
            if (!empty($currentToken)) {
                try {
                    $payload = Token::verify($currentToken);
                    if ($payload->role === $this->role) {
                        Token::destroy($currentToken);
                    }
                } catch (\Throwable $e) {
                    // Token验证失败，继续执行注销逻辑
                }
            }

            // 清理当前角色的上下文数据
            $this->cleanupCurrentRoleContext();

            Event::emit("log.authentication.{$this->role}.logout.success", [
                'member'     => $this->memberModel,
                'role'       => $this->role,
                'timestamp'  => microtime(true),
                'event_type' => 'logout'
            ]);

            return true;

        } catch (\Throwable $e) {
            Event::emit("log.authentication.{$this->role}.logout.failure", [
                'member'        => $this->memberModel ?? null,
                'role'          => $this->role,
                'timestamp'     => microtime(true),
                'event_type'    => 'logout',
                'error_message' => $e->getMessage()
            ]);
            // 登出失败不抛出异常，避免影响用户体验
            return false;
        }
    }

    /**
     * 清理当前角色的上下文数据（从Service迁移）
     */
    protected function cleanupCurrentRoleContext(): void
    {
        // 清理成员上下文中的当前角色数据
        $context = RequestContext::get('member_context');
        if ($context) {
            $context->clearRoleContext($this->role);
        }

        // 清理RequestContext中与当前角色相关的数据
        RequestContext::delete("role_{$this->role}_data");
        RequestContext::delete("role_{$this->role}_token");
        RequestContext::delete("current_{$this->role}_service");

        // 使用Role统一清理角色上下文
        Role::getInstance()->cleanupRoleContext($this->role);

        // 记录清理事件
        Event::emit("service.{$this->role}.context_cleaned", [
            'role'      => $this->role,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * 验证注册凭证（从Service迁移）
     *
     * @param array $credentials
     *
     * @throws \InvalidArgumentException
     */
    protected function validateRegisterCredentials(array $credentials): void
    {
        if (empty($credentials['username'])) {
            throw new \InvalidArgumentException('用户名不能为空');
        }

        if (empty($credentials['password'])) {
            throw new \InvalidArgumentException('密码不能为空');
        }

        if (strlen($credentials['username']) < 3) {
            throw new \InvalidArgumentException('用户名长度不能少于3位');
        }

        if (strlen($credentials['password']) < 6) {
            throw new \InvalidArgumentException('密码长度不能少于6位');
        }
    }

    /**
     * 验证注册数据唯一性（从Service迁移）
     *
     * @param array $credentials
     *
     * @throws \InvalidArgumentException
     */
    protected function validateRegisterData(array $credentials): void
    {
        // 检查用户名是否已存在
        if ($this->getUserByUsername($credentials['username'])) {
            throw new \InvalidArgumentException('用户名已存在');
        }

        // 检查邮箱是否已存在
        if (!empty($credentials['email'])
            && $this->getUserByEmail(
                $credentials['email']
            )
        ) {
            throw new \InvalidArgumentException('邮箱已存在');
        }

        // 检查手机号是否已存在
        if (!empty($credentials['mobile'])
            && $this->getUserByMobile(
                $credentials['mobile']
            )
        ) {
            throw new \InvalidArgumentException('手机号已存在');
        }
    }

    /**
     * 执行注册操作（从Service迁移）
     *
     * @param array $credentials
     *
     * @return object
     * @throws Throwable
     */
    protected function performRegistration(array $credentials): object
    {
        // 密码加密 - 使用 common.php 中的 hash_password 函数
        $credentials['password'] = hash_password($credentials['password']);
        
        // 设置默认状态为启用
        $credentials['status'] = 'enable';
        
        // 设置默认用户组（ID为1的默认分组）
        $credentials['group_id'] = 1;
        
        // 设置默认昵称为用户名
        if (empty($credentials['nickname'])) {
            $credentials['nickname'] = $credentials['username'];
        }

        // 创建用户
        $member = $this->memberModel->create($credentials);

        if (empty($member)) {
            throw new \RuntimeException('用户注册失败');
        }

        // 分配用户到默认分组
        $this->assignUserToDefaultGroup($member->id);

        return $member;
    }
    
    /**
     * 分配用户到默认分组
     *
     * @param int $userId
     * @throws Throwable
     */
    protected function assignUserToDefaultGroup(int $userId): void
    {
        try {
            // 用户表中的group_id字段直接关联用户组，无需额外关系表
            // 在performRegistration中已经设置了group_id = 1
            // 这里可以添加其他用户组相关的初始化逻辑
            
            // 记录用户组分配日志
            error_log("用户ID {$userId} 已分配到默认分组");
            
        } catch (\Throwable $e) {
            // 用户组分配失败不影响注册流程，记录日志即可
            error_log('用户组分配失败: ' . $e->getMessage());
        }
    }

    /**
     * 根据用户名获取用户（从Service迁移）
     *
     * @param string $username
     *
     * @return object|null
     */
    protected function getUserByUsername(string $username): ?object
    {
        return $this->memberModel->where('username', $username)->find();
    }

    /**
     * 根据邮箱获取用户（从Service迁移）
     *
     * @param string $email
     *
     * @return object|null
     */
    protected function getUserByEmail(string $email): ?object
    {
        return $this->memberModel->where('email', $email)->find();
    }

    /**
     * 根据手机号获取用户（从Service迁移）
     *
     * @param string $mobile
     *
     * @return object|null
     */
    protected function getUserByMobile(string $mobile): ?object
    {
        return $this->memberModel->where('mobile', $mobile)->find();
    }

    /**
     * 用户注册（从Service迁移）
     *
     * @param array $credentials
     *
     * @return array
     * @throws Throwable
     */
    public function register(array $credentials): array
    {
        try {
            // 参数验证
            $this->validateRegisterCredentials($credentials);

            // 数据唯一性验证
            $this->validateRegisterData($credentials);

            // 执行注册
            $member = $this->performRegistration($credentials);

            // 扩展用户信息
            $this->extendMemberInfo();
            
            // 注册成功后自动登录
            $this->autoLoginAfterRegistration($member);

            // 触发注册成功事件
            Event::emit("member.register_success", [
                'member'     => $member,
                'role'       => $this->role,
                'success'    => true,
                'timestamp'  => microtime(true),
                'event_type' => 'registration'
            ]);

            return $member->toArray();

        } catch (Throwable $e) {
            // 触发注册失败事件
            Event::emit("member.register_failure", [
                'username'   => $credentials['username'] ?? null,
                'role'       => $this->role,
                'success'    => false,
                'reason'     => $e->getMessage(),
                'timestamp'  => microtime(true),
                'event_type' => 'registration'
            ]);

            throw $e;
        }
    }
    
    /**
     * 注册成功后自动登录
     *
     * @param object $member
     * @throws Throwable
     */
    protected function autoLoginAfterRegistration(object $member): void
    {
        try {
            // 设置用户信息到上下文
            $this->memberModel = $member;
            
            // 生成Token（使用与登录相同的Token生成逻辑）
            $this->generateTokens();
            
            // 更新登录信息
            $this->updateLoginInfo();
            
        } catch (\Throwable $e) {
            // 自动登录失败不影响注册流程，记录日志即可
            error_log('注册后自动登录失败: ' . $e->getMessage());
        }
    }

}
