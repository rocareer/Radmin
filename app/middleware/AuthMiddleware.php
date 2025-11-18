<?php
/**
 * File:        RadminAuthMiddleware.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/16 05:55
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace app\middleware;

use support\Container;
use app\exception\TokenException;
use app\exception\TokenExpiredException;
use app\exception\UnauthorizedHttpException;
use support\Context;
use support\member\Member;
use support\Request;
use support\RequestContext;
use support\token\Token;
use support\StatusCode;
use Throwable;

class AuthMiddleware implements MiddlewareInterface
{

    /**
     * @throws Throwable
     */
    public function process(Request $request, callable $handler)
    {
        $request->role();
        $request->token();
        // 2. 检查是否跳过认证
        if (shouldExclude($request->path())) return $handler($request);
        // 3. 没有凭证则跳过
        if (empty($request->token)) return $handler($request);
        // 4. 验证Token有效性 无效则通知刷新
        try {
            $request->payload = Token::verify($request->token);
            Member::setCurrentRole($request->payload->role);
            Member::initialization();
        } catch (TokenExpiredException) {
            throw new TokenException('', StatusCode::TOKEN_SHOULD_REFRESH);
        }
        // 4. 处理请求
        $response = $handler($request);
        return $response;
    }

}
