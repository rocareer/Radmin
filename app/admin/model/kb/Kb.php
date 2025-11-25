<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * Kb
 */
class Kb extends BaseModel
{
    // 表名
    protected $name = 'kb_kb';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function kbType(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\kb\Type::class, 'kb_type_id', 'id');
    }

    public function kbCategory(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\kb\Category::class, 'kb_category_id', 'id');
    }

    public function admin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}