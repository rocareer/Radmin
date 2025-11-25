<?php

namespace app\admin\model\examples\table\search;

use think\Model;

/**
 * Linkage
 */
class Linkage extends Model
{
    // 表名
    protected $name = 'examples_table_search_linkage';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;


    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function admin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

    public function userGroup(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\UserGroup::class, 'group_id', 'id');
    }

    public function adminGroup(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\AdminGroup::class, 'group_id', 'id');
    }
}