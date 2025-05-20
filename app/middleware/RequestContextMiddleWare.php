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

class RequestContextMiddleWare implements MiddlewareInterface
{
    /**
     * @throws Exception
     */
    public function process(Request $request, callable $handler)
    {
        $context=Container::get('member.context');
        $response = $handler($request);
        $context->clear();
        return $response;
    }

}
