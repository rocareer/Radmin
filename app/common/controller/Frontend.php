<?php

namespace app\common\controller;

use app\exception\UnauthorizedHttpException;
use support\Context;
use support\member\Member;
use support\RequestContext;
use support\StatusCode;
use Throwable;


class Frontend extends Api
{
    /**
     * 无需登录的方法
     * 访问本控制器的此方法，无需会员登录
     * @var array
     */
    protected array $noNeedLogin = [];

    /**
     * 无需鉴权的方法
     * @var array
     */
    protected array $noNeedPermission = [];

    protected ?string $role='user';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 初始化
     * @throws Throwable
     */
    public function initialize(): void
    {
        parent::initialize();

        $needLogin = !action_in_arr($this->noNeedLogin);

        if ($needLogin) {
            // 需要登录的方法：强制初始化并验证
            // $this->requireAuthentication();
        }

        // 会员验权和登录标签位
        // Event::trigger('frontendInit', $this->auth);
    }

    /**
     * 强制要求登录
     * By albert  2025/11/21 20:34:39
     * @return void
     * @throws UnauthorizedHttpException
     */
    protected function requireAuthentication(): void
    {
        Member::initialization();
        $this->member = RequestContext::get('member');
        if (empty($this->member)) {
            throw new UnauthorizedHttpException('请先登录', StatusCode::NEED_LOGIN);
        }
        if (!action_in_arr($this->noNeedPermission)) {
            $routePath = (str_replace('/user/', '', $this->request->path()) ?? '');
            if (!Member::check($routePath)) {
                throw new UnauthorizedHttpException('没有权限', StatusCode::NO_PERMISSION);
            }
        }
    }

    /**
     * 尝试初始化用户信息（用于免登录方法中检测用户状态）
     * @return bool 是否成功初始化用户信息
     */
    protected function tryInitializeMember(): bool
    {
        try {
            Member::initialization();
            $this->member = \support\member\Context::getInstance()->getCurrentMember();

            return !empty($this->member);
        } catch (\Throwable $e) {
            // 初始化失败（如Token无效），保持member为空，不抛出异常
            $this->member = null;
            return false;
        }
    }
}