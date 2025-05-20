<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use extend\ra\SystemUtil;

class Dashboard extends Backend
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): \Radmin\Response
    {
     return $this->success('', [
            'remark' => SystemUtil::get_route_remark()
        ]);
    }
}