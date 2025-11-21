<?php
/**
 * File:        AuthMiddleware.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/16 05:55
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace app\middleware;

use ReflectionClass;
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
        try {
            // 1. 获取角色（由RoleIsolationMiddleWare设置）
            $role = $request->role;
            if (empty($role)) {
                throw new TokenException('角色未设置', StatusCode::TOKEN_INVALID);
            }

            // 2. 通过反射获取控制器哪些方法不需要登录
            $controller = new ReflectionClass($request->controller);
            $noNeedLogin = $controller->getDefaultProperties()['noNeedLogin'] ?? [];

            // 3. 访问的方法需要登录
            if (!in_array($request->action, $noNeedLogin)) {

                // 4. 验证Token有效性
                try {
                    $request->payload = Token::verify($request->token);

                    // 5. 验证payload完整性
                    if (!$this->validatePayload($request->payload)) {
                        throw new TokenException('无效的Token', StatusCode::TOKEN_INVALID);
                    }

                    // 6. 验证角色一致性（角色已由RoleIsolationMiddleWare验证）
                    if ($request->payload->role !== $role) {
                        throw new TokenException('Token角色与请求路径不匹配', StatusCode::TOKEN_INVALID);
                    }

                    // 7. 设置角色并初始化用户
                    Member::setCurrentRole($role);
                    Member::initialization();

                } catch (TokenExpiredException) {
                    throw new TokenException('', StatusCode::TOKEN_SHOULD_REFRESH);
                } catch (Throwable $e) {
                    throw new TokenException('Token验证失败: ' . $e->getMessage(), StatusCode::TOKEN_INVALID);
                }
            }

            // 8. 处理请求
            return $handler($request);
            
        } catch (TokenException $e) {
            // Token相关异常，直接抛出
            throw $e;
        } catch (Throwable $e) {
            // 其他异常，包装为Token异常
            throw new TokenException('鉴权处理失败: ' . $e->getMessage(), StatusCode::TOKEN_INVALID);
        }
    }
    

    
    /**
     * 验证Token payload完整性
     * @param mixed $payload
     * @return bool
     */
    private function validatePayload($payload): bool
    {
        return isset($payload->sub) && isset($payload->role) && 
               !empty($payload->sub) && !empty($payload->role);
    }

}
