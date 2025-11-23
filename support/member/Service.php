<?php


namespace support\member;

use app\exception\BusinessException;
use app\exception\UnauthorizedHttpException;
use Exception;
use support\member\interface\InterfaceService;
use support\member\role\admin\AdminAuthenticator;
use support\member\role\admin\AdminModel;
use support\member\role\user\UserAuthenticator;
use support\member\role\user\UserModel;
use support\RequestContext;
use support\StatusCode;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Throwable;
use Webman\Event\Event;

abstract class Service implements InterfaceService
{
    //common
    /**
     * 角色
     *
     * @var string
     */
    protected string $role = 'guest';

    protected mixed $request;

    /**
     * 用户模型实例
     *
     * @var mixed
     */
    protected mixed $memberModel = null;

    /**
     * 认证器实例
     *
     * @var mixed
     */
    protected mixed $authenticator = null;

    /**
     * 状态管理器实例
     *
     * @var mixed
     */
    protected mixed $state = null;

    /**
     * 权限规则管理器实例
     *
     * @var mixed
     */
    protected mixed $rule = null;

    /**
     * 默认配置
     *
     * @var array|string[]
     */
    protected array $config
        = [
            'auth_group'        => 'user_group', // 用户组数据表名
            'auth_group_access' => '', // 用户-用户组关系表
            'auth_rule'         => 'user_rule', // 权限规则表
        ];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->request = request();
        // 移除构造函数中的依赖初始化，改为按需初始化
    }

    /**
     * 获取当前用户信息
     *
     * @author Albert <albert@rocareer.com>
     * @time   2025/11/23 13:48
     * @return mixed|object|null
     */
    public function getCurrentMember(): mixed
    {
        return Context::getInstance()->getCurrentMember();
    }

    /**
     * 按需初始化依赖组件
     *
     * @param string $dependency 需要初始化的依赖类型 (model|authenticator|state|rule)
     * @return void
     */
    protected function initializeDependency(string $dependency): void
    {
        switch ($dependency) {
            case 'model':
                if (!$this->memberModel) {
                    $this->memberModel = $this->createModel($this->role);
                }
                break;
            case 'authenticator':
                if (!$this->authenticator) {
                    $this->authenticator = $this->createAuthenticator($this->role);
                }
                break;
            case 'state':
                if (!$this->state) {
                    $this->state = $this->createState($this->role);
                }
                break;
            case 'rule':
                if (!$this->rule) {
                    $this->initializeDependency('model');
                    $this->rule = $this->createRule($this->role, $this->memberModel);
                }
                break;
        }
    }

    /**
     * 获取模型实例（延迟初始化）
     *
     * @param string|null $role 角色类型，为null时使用当前角色
     * @return object
     */
    protected function getMemberModel(?string $role = null): object
    {
        // 如果请求的是当前角色的模型且已存在，直接返回
        if ($role === null && $this->memberModel) {
            return $this->memberModel;
        }
        
        // 创建新的模型实例
        $targetRole = $role ?? $this->role;
        return $this->createModel($targetRole);
    }

    /**
     * 获取认证器实例（延迟初始化）
     *
     * @return object
     */
    protected function getAuthenticator(): object
    {
        $this->initializeDependency('authenticator');
        return $this->authenticator;
    }

    /**
     * 获取状态管理器实例（延迟初始化）
     *
     * @return object
     */
    protected function getState(): object
    {
        $this->initializeDependency('state');
        return $this->state;
    }
    
    /**
     * 获取权限规则管理器实例（延迟初始化）
     *
     * @return object
     */
    protected function getRule(): object
    {
        $this->initializeDependency('rule');
        return $this->rule;
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
     * 创建认证器实例
     */
    protected function createAuthenticator(string $role): object
    {
        if ($role === 'admin') {
            return new AdminAuthenticator();
        }
        return new UserAuthenticator();
    }

    /**
     * 创建状态管理器实例
     * 
     * @param string $role 角色类型
     * @return object
     */
    protected function createState(string $role): object
    {
        // 目前所有角色使用相同的状态管理器
        // 未来可以根据不同角色返回不同的状态管理器实例
        return new State();
    }
    
    /**
     * 创建权限规则管理器实例
     * 
     * @param string $role 角色类型
     * @param object $memberModel 用户模型实例
     * @return object
     */
    protected function createRule(string $role, object $memberModel): object
    {
        // 根据角色创建对应的权限规则管理器
        // 未来可以根据不同角色返回不同的权限规则管理器实例
        $ruleClass = $role === 'admin' ? 
            role\admin\AdminRule::class :
            role\user\UserRule::class;
            
        return new $ruleClass($role, $memberModel);
    }


    public function __get($name)
    {
        if (property_exists($this->memberModel, $name)) {
            return $this->memberModel->$name;
        }

        // 添加逻辑来处理未定义的属性
        return null;
    }

    /**
     * 用户信息初始化
     *
     * @throws UnauthorizedHttpException|BusinessException
     */
    public function initialization(): void
    {
        // 如果用户信息已存在，直接返回
        if (!empty(Context::getInstance()->getCurrentMember())) {
            return;
        }

        try {
            // 用户信息初始化
            $this->memberInitialization();

            // 用户信息扩展
            $this->extendMemberInfo();

            // 状态检查
            $this->checkStatus('login');

        } catch (Exception $e) {
            Event::emit("state.updateLogin.failure", [
                'member'        => $this->memberModel,
                'role'          => $this->role,
                'success'       => false,
                'timestamp'     => microtime(true),
                'event_type'    => 'login_update',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 统一状态检查接口
     *
     * @param string $checkType 检查类型（login/access/security等）
     * @return bool
     * @throws BusinessException
     */
    public function checkStatus(string $checkType = 'login'): bool
    {
        if (!$this->memberModel) {
            throw new BusinessException('用户模型未初始化', StatusCode::USER_NOT_FOUND);
        }
        
        // 委托给专门的状态管理器执行检查
        $state = $this->getState();
        return $state->checkStatus($this->memberModel, $checkType);
    }


    /**
     * 用户登录
     *
     * @param array $credentials
     * @param bool  $keep
     * @return array
     * @throws BusinessException
     * @throws Throwable
     */
    public function login(array $credentials, bool $keep = false): array
    {
        // 参数验证
        $this->validateLoginCredentials($credentials);

        // 设置保持登录状态
        $credentials['keep'] = $keep;

        // 执行认证
        $authenticator     = $this->getAuthenticator();
        $this->memberModel = $authenticator->authenticate($credentials);

        // 验证认证结果
        if (empty($this->memberModel)) {
            throw new UnauthorizedHttpException(
                '认证失败', StatusCode::NEED_LOGIN
            );
        }

        // 设置用户信息
        $this->setMember($this->memberModel);

        return $this->memberModel->toArray();
    }

    /**
     * 验证登录凭证
     *
     * @param array $credentials
     *
     * @throws \InvalidArgumentException
     */
    private function validateLoginCredentials(array $credentials): void
    {
        if (empty($credentials['username'])) {
            throw new \InvalidArgumentException('用户名不能为空');
        }

        if (empty($credentials['password'])) {
            throw new \InvalidArgumentException('密码不能为空');
        }
    }


    /**
     * 注销登录
     *
     * @return bool
     */
    public function logout(): bool
    {
        try {
            // 委托给认证器处理注销逻辑
            $authenticator = $this->getAuthenticator();
            $result        = $authenticator->logoutWithToken();

            // 重置当前服务的成员信息
            $this->memberModel   = null;
            $this->authenticator = null;
            $this->state         = null;
            $this->rule          = null;

            return $result;

        } catch (\Throwable) {
            // 登出失败不抛出异常，避免影响用户体验
            return false;
        }
    }


    /**
     * 用户注册
     *
     * @param array $credentials
     * @return array
     * @throws Throwable
     */
    public function register(array $credentials): array
    {
        // 委托给认证器处理注册逻辑
        $authenticator = $this->getAuthenticator();
        $result        = $authenticator->register($credentials);

        // 获取认证器中的用户模型
        $this->memberModel = $authenticator->memberModel;

        // 设置用户信息到上下文
        $this->setMember($this->memberModel);

        return $result;
    }

    /**
     * 重置密码（待实现）
     */
    public function resetPassword(): void
    {
        // 待实现
    }

    /**
     * 修改密码（待实现）
     */
    public function changePassword(): void
    {
        // 待实现
    }

    /**
     * 检查密码
     *
     * @param string $password
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        if (empty($password)) {
            return false;
        }

        $model = $this->getMemberModel();
        if (empty($model) || empty($model->password)) {
            return false;
        }

        return verify_password($password, $model->password);
    }

    /**
     * 用户信息初始化
     *
     * @param string|null $token
     * @throws UnauthorizedHttpException
     */
    public function memberInitialization(?string $token = null): void
    {
        // 委托给认证器处理用户初始化
        $authenticator = $this->getAuthenticator();
        $authenticator->memberInitialization($token);

        // 获取认证器中的用户模型
        $this->memberModel = $authenticator->memberModel;

        // 设置用户信息到上下文
        $this->setMember($this->memberModel);
    }


    /**
     * 用户信息扩展
     *
     * @return void
     */
    public function extendMemberInfo(): void
    {
        if (!empty($this->memberModel)) {
            $this->memberModel->roles = [$this->role];
        }
    }

    /**
     * 根据ID获取用户信息
     *
     * @param int $id
     * @param string|null $role
     * @return object|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getMemberById(int $id, ?string $role = null): ?object
    {
        $model = $this->getMemberModel($role);
        return $model->where('id', $id)->find();
    }

    /**
     * 根据用户名获取用户信息
     *
     * @param string $username
     * @param string|null $role
     * @return object|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getMemberByName(string $username, ?string $role = null): ?object
    {
        $model = $this->getMemberModel($role);
        return $model->where('username', $username)->find();
    }

    /**
     * 检查当前用户是否拥有指定角色
     *
     * @param string $role
     * @param array|null $roles
     * @return bool
     */
    public function hasRole(string $role, ?array $roles = null): bool
    {
        // 如果提供了角色数组，直接检查
        if ($roles !== null) {
            return in_array($role, $roles);
        }

        // 从当前成员模型中获取角色信息
        if (!empty($this->memberModel) && isset($this->memberModel->roles)) {
            return in_array($role, (array)$this->memberModel->roles);
        }

        // 从请求上下文中获取角色信息
        $member = request()->member ?? null;
        if ($member && isset($member->roles)) {
            return in_array($role, (array)$member->roles);
        }

        // 默认返回当前服务角色的检查
        return $role === $this->role;
    }

    /**
     * 获取菜单规则列表
     *
     * @access public
     *
     * @param int|null $uid 用户ID
     *
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Throwable
     */
    public function getMenus(?int $uid = null): array
    {
        // 委托给权限规则管理器处理
        $rule = $this->getRule();
        return $rule->getMenus($uid);
    }

    /**
     * 获得权限规则原始数据
     *
     * @param int|null $uid 用户id
     *
     * @return array
     * @throws Throwable
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getOriginAuthRules(?int $uid = null): array
    {
        // 委托给权限规则管理器处理
        $rule = $this->getRule();
        return $rule->getOriginAuthRules($uid);
    }


    /**
     * 获取权限规则ids
     *
     * @param int|null $uid
     *
     * @return array
     * @throws Throwable
     */
    public function getRuleIds(?int $uid = null): array
    {
        // 委托给权限规则管理器处理
        $rule = $this->getRule();
        return $rule->getRuleIds($uid);
    }


    /**
     * 获取用户所有分组和对应权限规则
     *
     * @param int|null $id
     *
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getGroups(?int $id = null): array
    {
        // 委托给权限规则管理器处理
        $rule = $this->getRule();
        return $rule->getGroups($id);
    }

    /**
     * 获取传递的菜单规则的子规则
     *
     * @param array $rules 菜单规则
     *
     * @return array
     */
    

    /**
     * 设置用户信息
     *
     * @param object $member
     * @return void
     */
    public function setMember(object $member): void
    {
        $this->memberModel = $member;
        Context::getInstance()->setCurrentMember(
            $this->memberModel, $this->role
        );
    }

    /**
     * 重置用户信息
     *
     * @return void
     */
    public function resetMember(): void
    {
        $this->memberModel   = null;
        $this->authenticator = null;
        $this->state         = null;
        $this->rule          = null;
        RequestContext::clear();
    }


    /**
     * 设置错误消息
     *
     * @param string $error
     * @return Service
     */
    public function setError(string $error): Service
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 获取错误消息
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error ? __($this->error) : '';
    }


    /**
     * 检查是否有某权限F
     *
     * @param string   $name     菜单规则的 name，可以传递两个，以','号隔开
     * @param int|null $uid      用户ID
     * @param string   $relation 如果出现两个 name,是两个都通过(and)还是一个通过即可(or)
     * @param string   $mode     如果不使用 url 则菜单规则name匹配到即通过
     *
     * @return bool
     * @throws Throwable
     */
    public function check(string $name, ?int $uid = null,
        string $relation = 'or', string $mode = 'url'
    ): bool {
        // 委托给权限规则管理器处理
        $rule = $this->getRule();
        return $rule->check($name, $uid, $relation, $mode);
    }

    /**
     * 获得权限规则列表
     *
     * @param int|null $uid 用户id
     *
     * @return array
     * @throws Throwable
     */
    public function getRuleList(?int $uid = null): array
    {
        // 委托给权限规则管理器处理
        $rule = $this->getRule();
        return $rule->getRuleList($uid);
    }

    /**
     * 终端鉴权
     *
     * @param string $token
     *
     * @return   bool
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/13 00:39
     */


}