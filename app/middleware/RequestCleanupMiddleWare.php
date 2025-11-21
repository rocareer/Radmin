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
            
            return $response;
            
        } finally {
            // 确保在请求结束时清理上下文
            $this->cleanupRequestContext();
        }
    }
    
    /**
     * 清理请求上下文
     */
    private function cleanupRequestContext(): void
    {
        // 清理成员上下文
        $context = RequestContext::get('member_context');
        if ($context) {
            $context->clearAllRoleContexts();
        }
        
        // 清理所有请求上下文数据
        RequestContext::clear();
    }
}