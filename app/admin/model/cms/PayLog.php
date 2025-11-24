<?php

namespace app\admin\model\cms;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * PayLog
 */
class PayLog extends Model
{
    // 表名
    protected $name = 'cms_pay_log';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    // 字段类型转换
    protected $type = [
        'pay_time' => 'timestamp:Y-m-d H:i:s',
    ];

    public function getAmountAttr($value): float
    {
        return (float)bcdiv($value, 100, 2);
    }

    public function setAmountAttr($value): string
    {
        return bcmul($value, 100);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function cmsContent(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\cms\Content::class, 'object_id', 'id');
    }
}