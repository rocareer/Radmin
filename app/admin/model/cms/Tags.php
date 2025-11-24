<?php

namespace app\admin\model\cms;

use think\Model;

/**
 * Tags
 */
class Tags extends Model
{
    // 表名
    protected $name = 'cms_tags';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $type = [
        'create_time'=>'integer',
        'update_time'=>'integer',
    ];

}