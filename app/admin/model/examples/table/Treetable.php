<?php

namespace app\admin\model\examples\table;

use think\Model;

/**
 * 树状表格在本模型文件中，无特别之处
 */
class Treetable extends Model
{
    // 表名
    protected $name = 'examples_table_treetable';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;
}