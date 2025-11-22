<?php


namespace support\member;

use app\exception\BusinessException;
use app\exception\UnauthorizedHttpException;


use support\Log;
use support\orm\Db;
use support\token\Token;
use support\StatusCode;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Throwable;
use Webman\Event\Event;

/**
 * 认证器抽象基类
 */
abstract class Authenticator
{
    use DependencyInjectionTrait;
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

    protected object|null $memberModel = null;

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
     * 认证用户（优化版）
     * @param array $credentials
     * @return object
     * @throws UnauthorizedHttpException
     */
    public function authenticate(array $credentials): object
    {
        $this->credentials = $credentials;

        try {
            Db::startTrans();

            // 认证流程
            $this->validateCredentials();      // 1. 验证基本凭证
            $this->validateCaptcha();          // 2. 验证验证码
            $this->findMember();               // 3. 查找用户
            $this->checkMemberStatus();        // 4. 检查用户状态
            $this->verifyPassword();           // 5. 验证密码
            $this->generateTokens();           // 6. 生成令牌
            $this->extendMemberInfo();         // 7. 扩展用户信息
            
            // 登录成功事件
            Event::emit("state.login_success", [
                'member' => $this->memberModel,
                'role' => $this->role,
                'success' => true
            ]);

            Db::commit();
            return $this->memberModel;

        } catch (UnauthorizedHttpException $e) {
            $this->handleAuthenticationFailure($e);
            throw $e;
        } catch (Throwable $e) {
            $this->handleAuthenticationFailure($e);
            Log::error('认证异常：' . $e->getMessage());
            throw new UnauthorizedHttpException('系统错误，请稍后重试', StatusCode::AUTHENTICATION_FAILED, [], $e);
        }
    }

    /**
     * 处理认证失败
     * @param Throwable $e
     */
    private function handleAuthenticationFailure(Throwable $e): void
    {
        try {
            Db::rollback();
            
            // 登录失败事件
            Event::emit("state.login_failure", [
                'member' => $this->memberModel,
                'role' => $this->role,
                'success' => false
            ]);
        } catch (Throwable $rollbackError) {
            Log::error('认证失败回滚异常：' . $rollbackError->getMessage());
        }
    }

    /**
     * 验证基本凭证
     * By albert  2025/05/06 01:51:31
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function validateCredentials(): void
    {
        if (empty($this->credentials['username'])) {
            throw new UnauthorizedHttpException('用户名不能为空', StatusCode::USERNAME_REQUIRED);
        }

        if (empty($this->credentials['password'])) {
            throw new UnauthorizedHttpException('密码不能为空', StatusCode::PASSWORD_REQUIRED);
        }
    }

    /**
     * 验证验证码
     * By albert  2025/05/06 04:00:32
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function validateCaptcha(): void
    {
        if ($this->config['captcha'] && empty($this->credentials['captcha'])) {
            throw new UnauthorizedHttpException('验证码不能为空', StatusCode::CAPTCHA_REQUIRED);
        }
    }

    /**
     * 获取用户
     * By albert  2025/05/06 04:00:02
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws UnauthorizedHttpException
     */
    protected function findMember(): void
    {
        $user = $this->memberModel->findByName($this->credentials['username']);
        if (!$user) {
            throw new UnauthorizedHttpException('用户不存在', StatusCode::USER_NOT_FOUND);
        }

        $this->memberModel = $user;

    }

    /**
     * 检查用户状态
     */
    protected function checkMemberStatus(): void
    {
        Event::dispatch('state.checkStatus', $this->memberModel);
    }

    /**
     * 验证密码
     * By albert  2025/05/06 01:33:02
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function verifyPassword(): void
    {

        $res = $this->memberModel->verifyPassword($this->credentials['password'], $this->memberModel);
        if (!$res) {
            throw new UnauthorizedHttpException('密码错误', StatusCode::PASSWORD_ERROR);
        }
    }


    /**
     * 生成令牌
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

            // 生成访问令牌
            $this->memberModel->token = Token::encode($tokenData);

            // 根据需要生成刷新令牌
            if (!empty($this->credentials['keep'])) {
                $tokenData['keep'] = true;
                $this->memberModel->refresh_token = Token::encode($tokenData);
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            throw new BusinessException('生成令牌失败', StatusCode::TOKEN_GENERATE_FAILED);
        }

    }

    public function extendMemberInfo(): void
    {
        $this->memberModel->roles = [$this->role];
    }




    /**
     * todo
     * 记录操作日志
     * @param array $logData 日志数据
     */
    protected function recordOperationLog(array $logData): void
    {
        // if ($this->stateManager && method_exists($this->stateManager, 'recordOperationLog')) {
        //     $this->stateManager->recordOperationLog(
        //         $logData['action'] ?? 'operation',
        //         $logData['type'] ?? 'common',
        //         $logData['description'] ?? '',
        //         $logData['data'] ?? []
        //     );
        // }
    }

    /**
     * 刷新认证令牌
     * @param string $refreshToken
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

            // 记录操作日志
            $this->recordOperationLog([
                'action'      => 'refresh_token',
                'description' => '刷新认证令牌',
                'data'        => ['ip' => request()->getRealIp()]
            ]);

            Db::commit();
            return $this->memberModel->token;

        } catch (Throwable $e) {
            Db::rollback();
            Log::error('刷新令牌失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new UnauthorizedHttpException('系统错误，请稍后重试', StatusCode::SERVER_ERROR);
        }
    }

    /**
     * 验证刷新令牌
     * @param string $refreshToken
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function validateRefreshToken(string $refreshToken): void
    {
        $payload = Token::verify($refreshToken);
        if (empty($payload['user_id'])) {
            throw new UnauthorizedHttpException('无效的刷新令牌', StatusCode::TOKEN_INVALID, true);
        }

        $user = $this->memberModel->findById($payload['user_id']);
        if (!$user) {
            throw new UnauthorizedHttpException('用户不存在', StatusCode::USER_NOT_FOUND, true);
        }
    }

    /**
     * 注销登录
     * @return bool
     * @throws BusinessException
     */
    public function logout(): bool
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
            $this->recordOperationLog([
                'action'      => 'logout',
                'description' => '用户注销',
                'data'        => ['ip' => request()->getRealIp()]
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('注销失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new BusinessException('注销失败，请稍后重试', StatusCode::SERVER_ERROR);
        }
    }
    
    /**
     * 清除状态缓存
     */
    protected function clearStateCache(): void
    {
        $cacheKey = $this->getStateCacheKey();
        cache($cacheKey, null);
    }
    
    /**
     * 获取状态缓存键
     */
    protected function getStateCacheKey(): string
    {
        $config = config('auth.state');
        $prefix = $config['prefix'] ?? 'state-';
        return $prefix . $this->role . '-' . $this->memberModel->id;
    }


    /**
     * 强制下线用户
     * @param int $userId
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
            throw new UnauthorizedHttpException('用户不存在', StatusCode::USER_NOT_FOUND);
        }

        //todo
        // $stateManager = new $this->stateManager($user);
        // $result       = $stateManager->forceLogout($userId);

        // 记录操作日志
        if ($this->memberModel) {
            $this->recordOperationLog([
                'action'      => 'force_logout',
                'description' => '强制下线用户',
                'data'        => [
                    'target_user_id' => $userId,
                    'ip'             => request()->getRealIp()
                ]
            ]);
        }

        // return $result;
        return true;
    }

}
