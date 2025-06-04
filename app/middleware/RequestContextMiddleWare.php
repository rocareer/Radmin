<?php
/**
 * File:        RequestContextMiddleWare.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/21 01:30
 * Description:
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
        // 创建上下文

        // 将上下文绑定到当前请求
        $request->text = RequestContext::get();

        // 处理请求
        $response = $handler($request);

        // 清理上下文
        RequestContext::clear();

        return $response;
    }
}
