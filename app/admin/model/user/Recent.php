<?php

namespace app\admin\model\user;

use Throwable;
use think\Model;
use think\model\relation\BelongsTo;

/**
 * Recent
 */
class Recent extends Model
{
    // 表名
    protected $name = 'user_recent';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;


    protected $type = [
        'create_time'=>'integer',
        'update_time'=>'integer',
    ];

    /**
     * 入库前
     * @throws Throwable
     */
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }
}