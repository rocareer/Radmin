<?php

namespace app\admin\model\cms;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * FriendlyLink
 */
class FriendlyLink extends Model
{
    // 表名
    protected $name = 'cms_friendly_link';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected static function onAfterInsert($model)
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            if (strlen($model[$pk]) >= 19) {
                $model->where($pk, $model[$pk])->update(['weigh' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
            }
        }
    }

    public function getLogoAttr($value): string
    {
        return full_url($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}