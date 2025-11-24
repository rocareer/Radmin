<?php

namespace app\admin\model\user;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * Message
 */
class Message extends Model
{
    // 表名
    protected $name = 'user_message';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;


    public function user(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'recipient_id', 'id');
    }
}