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

    protected string $role='user';

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

        // 会员验权和登录标签位
        // Event::trigger('frontendInit', $this->auth);
    }
}