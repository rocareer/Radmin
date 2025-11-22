<?php


namespace support\member;

use app\exception\UnauthorizedHttpException;
use Exception;
use support\RequestContext;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;
use app\exception\BusinessException;
use app\exception\TokenException;

use support\orm\Db;
use support\token\Token;
use support\StatusCode;
use Throwable;

abstract class Service
{
    use DependencyInjectionTrait;
    //common
    /**
     * 角色
     * @var string
     */
    protected string $role = 'guest';

    protected mixed $request;

    /**
     * 用户模型实例
     * @var mixed
     */
    protected mixed $memberModel = null;

    /**
     * 认证器实例
     * @var mixed
     */
    protected mixed $authenticator = null;

    /**
     * 状态管理器实例
     * @var mixed
     */
    protected mixed $state = null;

    /**
     * 子节点
     * @var mixed|null
     */
    protected mixed $children = null;

    /**
     * 默认配置
     * @var array|string[]
     */
    protected array $config = [
        'auth_group'        => 'user_group', // 用户组数据表名
        'auth_group_access' => '', // 用户-用户组关系表
        'auth_rule'         => 'user_rule', // 权限规则表
    ];

    public function __construct()
    {
        $this->request = request();
        $this->initializeDependencies();
    }

    /**
     * 初始化依赖组件（优化版）
     * @return void
     */
    protected function initializeDependencies(): void
    {
        // 优化：减少重复初始化检查
        $this->memberModel = $this->memberModel ?: $this->createModel($this->role);
        $this->authenticator = $this->authenticator ?: $this->createAuthenticator($this->role);
        $this->state = $this->state ?: $this->createState($this->role);
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
     * @throws BusinessException
     */
    public function initialization(): void
    {
        $this->initializeDependencies();
        
        // 如果用户信息已存在，直接返回
        if (!empty(RequestContext::get('member'))) {
            return;
        }
        
        try {
            // 用户信息初始化
            $this->memberInitialization();
            
            // 用户信息扩展
            $this->extendMemberInfo();
            
            // 用户状态检查
            $this->stateCheckStatus();
            
        } catch (Exception $e) {
            Event::emit("state.updateLogin.failure", $this->memberModel);
            throw $e;
        }
    }


    /**
     * 状态:检查状态
     * By albert  2025/05/06 19:24:35
     */
    protected function stateCheckStatus(): void
    {
        Event::dispatch('state.checkStatus', $this->memberModel);
    }

    /**
     * 状态:更新登录记录
     * By albert  2025/05/06 19:23:20
     * @param string $success
     * @return void
     */
    protected function stateUpdateLogin(string $success): void
    {
        Event::emit("state.updateLogin.$success", $this->memberModel);
    }


    /**
     * 用户登录
     * @param array $credentials
     * @param bool  $keep
     * @return array
     * @throws BusinessException
     * @throws Throwable
     */
    public function login(array $credentials, bool $keep = false): array
    {
        try {
            // 参数验证
            $this->validateLoginCredentials($credentials);
            
            // 设置保持登录状态
            $credentials['keep'] = $keep;
            
            // 执行认证
            $this->memberModel = $this->authenticator->authenticate($credentials);
            
            // 验证认证结果
            if (empty($this->memberModel)) {
                throw new UnauthorizedHttpException('认证失败', StatusCode::NEED_LOGIN);
            }
            
            // 设置用户信息
            $this->setMember($this->memberModel);
            
            return $this->memberModel->toArray();
            
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * 验证登录凭证
     * @param array $credentials
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
     * 验证注册凭证
     * @param array $credentials
     * @throws \InvalidArgumentException
     */
    private function validateRegisterCredentials(array $credentials): void
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
     * 验证注册数据唯一性
     * @param array $credentials
     * @throws \InvalidArgumentException
     */
    private function validateRegisterData(array $credentials): void
    {
        // 检查用户名是否已存在
        if ($this->getUserByUsername($credentials['username'])) {
            throw new \InvalidArgumentException('用户名已存在');
        }
        
        // 检查邮箱是否已存在
        if (!empty($credentials['email']) && $this->getUserByEmail($credentials['email'])) {
            throw new \InvalidArgumentException('邮箱已存在');
        }
        
        // 检查手机号是否已存在
        if (!empty($credentials['mobile']) && $this->getUserByMobile($credentials['mobile'])) {
            throw new \InvalidArgumentException('手机号已存在');
        }
    }

    /**
     * 执行注册操作
     * @param array $credentials
     * @return object
     * @throws Throwable
     */
    private function performRegistration(array $credentials): object
    {
        // 密码加密 - 使用 common.php 中的 hash_password 函数
        $credentials['password'] = hash_password($credentials['password']);
        
        // 创建用户
        $member = $this->memberModel->create($credentials);
        
        if (empty($member)) {
            throw new \RuntimeException('用户注册失败');
        }
        
        return $member;
    }

    /**
     * 根据用户名获取用户
     * @param string $username
     * @return object|null
     */
    private function getUserByUsername(string $username): ?object
    {
        return $this->memberModel->where('username', $username)->find();
    }

    /**
     * 根据邮箱获取用户
     * @param string $email
     * @return object|null
     */
    private function getUserByEmail(string $email): ?object
    {
        return $this->memberModel->where('email', $email)->find();
    }

    /**
     * 根据手机号获取用户
     * @param string $mobile
     * @return object|null
     */
    private function getUserByMobile(string $mobile): ?object
    {
        return $this->memberModel->where('mobile', $mobile)->find();
    }

    /**
     * 注销登录 - 支持多角色隔离注销
     * By albert  2025/05/08 04:28:10
     * @return bool
     */
    public function logout(): bool
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
            
            Event::emit("log.authentication.{$this->role}.logout.success", $this->memberModel);
            
            return true;
            
        } catch (\Throwable $e) {
            Event::emit("log.authentication.{$this->role}.logout.failure", $this->memberModel ?? null);
            // 登出失败不抛出异常，避免影响用户体验
            return false;
        }
    }
    
    /**
     * 清理当前角色的上下文数据
     */
    protected function cleanupCurrentRoleContext(): void
    {
        // 清理成员上下文中的当前角色数据
        $context = RequestContext::get('member_context');
        if ($context) {
            $context->clearRoleContext($this->role);
        }
        
        // 成员信息清理由 RoleManager 统一处理
        
        // 重置当前服务的成员信息
        $this->memberModel = null;
    }

    /**
     * 用户注册
     * @param array $credentials
     * @return array
     * @throws Throwable
     */
    public function register(array $credentials): array
    {
        try {
            // 确保依赖已初始化
            $this->initializeDependencies();
            
            // 参数验证
            $this->validateRegisterCredentials($credentials);
            
            // 数据唯一性验证
            $this->validateRegisterData($credentials);
            
            // 执行注册
            $member = $this->performRegistration($credentials);
            
            // 设置用户信息
            $this->setMember($member);
            
            return $member->toArray();
            
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function resetPassword()
    {

    }

    public function changePassword()
    {

    }

    public function checkPassword($password): bool
    {
        if (empty($password)) {
            return false;
        }
        
        if (empty($this->memberModel) || empty($this->memberModel->password)) {
            return false;
        }
        
        return verify_password($password, $this->memberModel->password);
    }

    /**
     * 用户信息初始化
     * @param string|null $token
     * @throws UnauthorizedHttpException
     */
    public function memberInitialization(?string $token = null): void
    {
        try {
            $this->request = request();
            
            // 获取并验证Token
            $payload = $this->getValidatedTokenPayload($token);
            
            // 验证用户存在性
            $member = $this->validateUserExists($payload->sub);
            
            // 设置用户信息
            $this->setMember($member);
            $this->memberModel = $member;
            
        } catch (\support\token\TokenExpiredException $e) {
            throw new TokenException('Token已过期', StatusCode::TOKEN_EXPIRED);
        } catch (Throwable $e) {
            throw new UnauthorizedHttpException($e->getMessage(), StatusCode::NEED_LOGIN);
        }
    }

    /**
     * 获取并验证Token载荷
     * @param string|null $token
     * @return object
     * @throws UnauthorizedHttpException
     */
    private function getValidatedTokenPayload(?string $token): object
    {
        // 优先使用请求中的payload
        if (isset($this->request->payload) && isset($this->request->payload->sub)) {
            return $this->request->payload;
        }
        
        // 从Token中获取payload
        $token = $token ?? $this->request->token();
        if (!$token) {
            throw new UnauthorizedHttpException('请先登录', StatusCode::NEED_LOGIN);
        }
        
        $payload = \support\token\Token::verify($token);
        if (!$payload || !isset($payload->sub)) {
            throw new UnauthorizedHttpException('无效的Token', StatusCode::NEED_LOGIN);
        }
        
        // 缓存payload到请求对象
        $this->request->payload = $payload;
        return $payload;
    }

    /**
     * 验证用户存在性
     * @param mixed $userId
     * @return object
     * @throws UnauthorizedHttpException
     */
    private function validateUserExists($userId): object
    {
        if (empty($userId)) {
            throw new UnauthorizedHttpException('用户ID不能为空', StatusCode::NEED_LOGIN);
        }
        
        $member = $this->memberModel->findById($userId);
        if (empty($member)) {
            throw new UnauthorizedHttpException('用户不存在', StatusCode::NEED_LOGIN);
        }
        
        return $member;
    }

    public function extendMemberInfo(): void
    {
        if (!empty($this->memberModel)) {
            $this->memberModel->roles = [$this->role];
        }
    }

    /**
     * 用户:获取当前登录用户信息
     * By albert  2025/05/06 19:32:38
     * @return object|null
     */
    public function getMember(): ?object
    {
        return $this->memberModel;
    }


    /**
     * 检查当前用户是否拥有指定角色
     * By albert  2025/04/30 04:02:04
     *
     * @param      $role
     * @param null $roles
     * @return bool
     */
    public function hasRole($role, $roles = null): bool
    {
        $payloadRoles = $roles ?? request()->member->roles ?? [];
        return in_array($role, $payloadRoles);
    }

    /**
     * 获取菜单规则列表
     * @access public
     * @param int $uid 用户ID
     * @return array
     * @throws Throwable
     */
    public function getMenus(?int $uid = null): array
    {
        $uid             = $uid ?? $this->memberModel->id;
        $this->children  = [];

        $originAuthRules = $this->getOriginAuthRules($uid);

        foreach ($originAuthRules as $rule) {
            $this->children[$rule['pid']][] = $rule;
        }

        // 没有根菜单规则
        if (!isset($this->children[0])) return [];

        return $this->getChildren($this->children[0]);
    }

    /**
     * 获得权限规则原始数据
     * @param int|null $uid 用户id
     * @return array
     * @throws Throwable
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getOriginAuthRules(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;
        $ids = $this->getRuleIds($uid);
        if (empty($ids)) return [];

        $where   = [];
        $where[] = ['status', '=', '1'];
        // 如果没有 * 则只获取用户拥有的规则
        if (!in_array('*', $ids)) {
            $where[] = ['id', 'in', $ids];
        }
        $rules = Db::name($this->config['auth_rule'])
            ->withoutField(['remark', 'status', 'weigh', 'update_time', 'create_time'])
            ->where($where)
            ->order('weigh desc,id asc')
            ->select()
            ->toArray();
        foreach ($rules as $key => $rule) {
            if (!empty($rule['keepalive'])) {
                $rules[$key]['keepalive'] = $rule['name'];
            }
        }

        return $rules;
    }


    /**
     * 获取权限规则ids
     * @param int $uid
     * @return array
     * @throws Throwable
     */
    public function getRuleIds(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;
        // 用户的组别和规则ID
        $groups = $this->getGroups($uid);

        $ids    = [];
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        return array_unique($ids);
    }


    /**
     * 获取用户所有分组和对应权限规则
     * @param int $uid
     * @return array
     * @throws Throwable
     */
    public function getGroups(?int $id = null): array
    {
        $id = $id ?? $this->memberModel->id;

        $dbName = $this->config['auth_group_access'] ?: 'user';
        if ($this->config['auth_group_access']) {
            $userGroups = Db::name($dbName)
                ->alias('aga')
                ->join($this->config['auth_group'] . ' ag', 'aga.group_id = ag.id', 'LEFT')
                ->field('aga.uid,aga.group_id,ag.id,ag.pid,ag.name,ag.rules')
                ->where("aga.uid='$id' and ag.status='1'")
                ->select()
                ->toArray();
        } else {
            $userGroups = Db::name($dbName)
                ->alias('u')
                ->join($this->config['auth_group'] . ' ag', 'u.group_id = ag.id', 'LEFT')
                ->field('u.id as uid,u.group_id,ag.id,ag.name,ag.rules')
                ->where("u.id='$id' and ag.status='1'")
                ->select()
                ->toArray();
        }

        return $userGroups;
    }

    /**
     * 获取传递的菜单规则的子规则
     * @param array $rules 菜单规则
     * @return array
     */
    private function getChildren(array $rules): array
    {
        foreach ($rules as $key => $rule) {
            if (array_key_exists($rule['id'], $this->children)) {
                $rules[$key]['children'] = $this->getChildren($this->children[$rule['id']]);
            }
        }
        return $rules;
    }

    /**
     * 设置用户信息
     * By albert  2025/05/06 17:48:07
     * @param $member
     * @return void
     */
    public function setMember($member): void
    {
        $this->memberModel = $member;
        RequestContext::set('member', $this->memberModel);
    }

    /**
     * 重置用户信息
     * By albert  2025/05/06 17:48:20
     * @return void
     */
    public function resetMember(): void
    {
        $this->memberModel = null;
        RequestContext::clear();
    }


    /**
     * 设置错误消息
     * @param $error
     * @return Service
     */
    public function setError($error): Service
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 获取错误消息
     * @return string
     */
    public function getError(): string
    {
        return $this->error ? __($this->error) : '';
    }


    /**
     * 检查是否有某权限F
     * @param string $name     菜单规则的 name，可以传递两个，以','号隔开
     * @param int    $uid      用户ID
     * @param string $relation 如果出现两个 name,是两个都通过(and)还是一个通过即可(or)
     * @param string $mode     如果不使用 url 则菜单规则name匹配到即通过
     * @return bool
     * @throws Throwable
     */
    public function check(string $name, ?int $uid = null, string $relation = 'or', string $mode = 'url'): bool
    {
        $uid = $uid ?? $this->memberModel->id;
        // 获取用户需要验证的所有有效规则列表
        $ruleList = $this->getRuleList($uid);
        if (in_array('*', $ruleList)) {
            return true;
        }

        if ($name) {
            $name = strtolower($name);
            if (str_contains($name, ',')) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = json_decode(strtolower(json_encode(request()->all(), JSON_UNESCAPED_UNICODE)), true);
        }
        foreach ($ruleList as $rule) {
            $query = preg_replace('/^.+\?/U', '', $rule);
            if ('url' == $mode && $query != $rule) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $rule      = preg_replace('/\?.*$/U', '', $rule);
                if (in_array($rule, $name) && $intersect == $param) {
                    // 如果节点相符且url参数满足
                    $list[] = $rule;
                }
            } elseif (in_array($rule, $name)) {
                $list[] = $rule;
            }
        }
        if ('or' == $relation && !empty($list)) {
            return true;
        }

        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * 获得权限规则列表
     * @param int|null $uid 用户id
     * @return array
     * @throws Throwable
     */
    public function getRuleList(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;
        // 读取用户规则节点
        $ids = $this->getRuleIds($uid);
        if (empty($ids)) return [];

        $originAuthRules = $this->getOriginAuthRules($uid);

        // 用户规则
        $rules = [];
        if (in_array('*', $ids)) {
            $rules[] = "*";
        }
        foreach ($originAuthRules as $rule) {
            $rules[$rule['id']] = strtolower($rule['name']);
        }
        return array_unique($rules);
    }

    /**
     * 终端鉴权
     * @param string $token
     * @return   bool
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/13 00:39
     */


}