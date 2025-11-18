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
        try {
            // 1. 设置角色和提取Token
            $request->role();
            $request->token();
            
            // 2. 检查是否跳过认证
            if (shouldExclude($request->path())) {
                return $handler($request);
            }
            
            // 3. 没有凭证则跳过
            if (empty($request->token)) {
                return $handler($request);
            }
            
            // 4. 验证Token有效性
            try {
                $request->payload = Token::verify($request->token);
                
                // 5. 验证payload完整性
                if (!$this->validatePayload($request->payload)) {
                    throw new TokenException('无效的Token', StatusCode::TOKEN_INVALID);
                }
                
                // 6. 设置角色并初始化用户
                Member::setCurrentRole($request->payload->role);
                Member::initialization();
                
            } catch (TokenExpiredException) {
                throw new TokenException('Token已过期', StatusCode::TOKEN_SHOULD_REFRESH);
            } catch (Throwable $e) {
                throw new TokenException('Token验证失败: ' . $e->getMessage(), StatusCode::TOKEN_INVALID);
            }
            
            // 7. 处理请求
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
