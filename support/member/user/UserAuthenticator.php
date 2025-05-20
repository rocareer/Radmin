<?php


namespace support\member\user;

use Radmin\exception\UnauthorizedHttpException;
use support\member\Authenticator;

class UserAuthenticator extends Authenticator
{
    protected string $role = 'user';

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


}

