<?php

namespace app\common\model\ai;

use think\facade\Db;
use think\Model;

/**
 * Session
 */
class Session extends Model
{
    // 表名
    protected $name = 'ai_session';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    public function getTitleAttr($value): string
    {
        return $value ? $value : '无标题';
    }

    public function aiUser(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\ai\AiUser::class, 'user_id', 'id');
    }
}