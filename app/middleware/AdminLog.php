<?php


namespace app\middleware;

use app\admin\model\AdminLog as AdminLogModel;
use support\Request;
use support\Response;
use Throwable;

class AdminLog implements MiddlewareInterface
{
    /**
     * 写入管理日志
     * @throws Throwable
     */
	public function process(Request $request, callable $handler)
    {
        $response = $handler($request);
        if (($request->isPost() || $request->method()=='DELETE'||$request->action=='sync') &&  config('buildadmin.auto_write_admin_log')) AdminLogModel::instance()->record();
        return $response;
    }
}