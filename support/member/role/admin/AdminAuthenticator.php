<?php


namespace support\member\role\admin;

use app\exception\UnauthorizedHttpException;
use support\member\Authenticator;
use support\member\Member;

class AdminAuthenticator extends Authenticator
{
    protected string $role = 'admin';


    /**
     * 认证用户
     * @param array $credentials 用户凭证
     * @return object
     * @throws UnauthorizedHttpException
     */
    public function authenticate(array $credentials): object
    {
        return parent::authenticate($credentials);
    }

    public function extendMemberInfo(): void
    {
        if (Member::isSuperAdmin($this->memberModel->id)) {
            $this->memberModel->super = true;
            $this->memberModel->roles = ['super', 'admin'];
        }
    }


}

