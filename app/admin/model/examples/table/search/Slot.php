<?php

namespace app\admin\model\examples\table\search;

use think\Model;

/**
 * Slot
 */
class Slot extends Model
{
    // 表名
    protected $name = 'examples_table_search_slot';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;


    public function getCheckboxAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setCheckboxAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}