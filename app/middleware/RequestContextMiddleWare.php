<?php
/**
 * File:        RequestContextMiddleWare.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/21 01:30
 * Description: 请求上下文中间件 - 管理请求级别的上下文数据
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace app\middleware;

use Exception;
use support\Container;
use support\Request;
use support\RequestContext;

class RequestContextMiddleWare
{
    public function process($request, $handler)
    {
        // 初始化请求上下文
        RequestContext::set('request_start_time', microtime(true));
        RequestContext::set('request_path', $request->path());
        RequestContext::set('request_method', $request->method());

        // 将上下文绑定到当前请求
        $request->text = RequestContext::get();

        // 处理请求
        $response = $handler($request);

        // 记录请求处理时间
        $startTime = RequestContext::get('request_start_time');
        if ($startTime) {
            $processTime = round((microtime(true) - $startTime) * 1000, 2);
            RequestContext::set('request_process_time', $processTime);
        }

        // 注意：不在这里清理上下文，由后续中间件或请求结束处理器负责
        // 这样可以确保角色隔离中间件等后续中间件能正常访问上下文数据

        return $response;
    }
}
