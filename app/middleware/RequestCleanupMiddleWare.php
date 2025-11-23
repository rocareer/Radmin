<?php
/**
 * File:        RequestCleanupMiddleWare.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/21
 * Description: 请求清理中间件 - 负责在请求结束时清理所有上下文数据
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace app\middleware;

use support\Request;
use support\RequestContext;

class RequestCleanupMiddleWare
{
    public function process($request, $handler)
    {
        try {
            // 处理请求
            $response = $handler($request);
            
            // 在请求返回后清理上下文
            $this->cleanupRequestContext();
            
            return $response;
            
        } catch (\Throwable $e) {
            // 即使发生异常也要清理上下文
            $this->cleanupRequestContext();
            throw $e;
        }
    }
    
    /**
     * 清理请求上下文
     */
    private function cleanupRequestContext(): void
    {
        // 1. 清理业务相关的上下文数据，保留请求基本信息用于日志记录
        RequestContext::clearBusinessData();
        
        // 2. 清理临时存储的成员上下文引用（但不清理单例实例本身）
        RequestContext::delete('member_context');
        
        // 3. 清理请求级别的临时数据
        RequestContext::delete('payload');
        RequestContext::delete('token_validated');
        RequestContext::delete('user_initialized');
        
        // 4. 记录清理事件，用于调试
        if (config('app.debug', false)) {
            $remainingKeys = array_keys(RequestContext::get());
            \Webman\Event\Event::emit('request.context.cleaned', [
                'remaining_keys' => $remainingKeys,
                'timestamp' => microtime(true)
            ]);
        }
        
        // 注意：不清理单例的Context实例，因为它在请求间共享
        // 用户上下文的生命周期应该由Token过期机制和登出操作管理，而不是请求清理
    }
}