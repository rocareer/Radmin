<?php

namespace app\admin\model\cms;

use think\Model;

/**
 * ContentModel
 */
class ContentModel extends Model
{
    // 表名
    protected $name = 'cms_content_model';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

}