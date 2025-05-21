<?php
/** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace app\middleware;

use Exception;
use support\Request;

class RequestMiddleWare implements MiddlewareInterface
{
    /**
     * @throws Exception
     */
    public function process(Request $request, callable $handler)
    {

        /**
         * 全局请求日志
         */
        // if (config('app.request.log.enable')) {
        //     //生成全局 requestID
        //     $request->requestID = uniqid('R-', true);
        //     $logContent         = [
        //         'requestID' => $request->requestID,
        //         'url'       => $request->url(),
        //         'method'    => $request->method(),
        //         'IP'        => $request->getRealIp(),
        //         'time'      => time()
        //     ];
        //     Log::channel(config('app.request.log.channel'))->info('Request', $logContent);
        // }


        return $handler($request);
    }


}
