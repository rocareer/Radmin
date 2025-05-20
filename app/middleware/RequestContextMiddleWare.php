<?php
/** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace app\middleware;

use Exception;
use support\Container;
use support\Response;
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
