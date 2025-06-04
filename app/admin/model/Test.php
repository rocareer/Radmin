<?php

namespace app\admin\model;

use app\common\model\BaseModel;

/**
 * Test
 */
class Test extends BaseModel
{
    // 表名
    protected $name = 'test';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

}