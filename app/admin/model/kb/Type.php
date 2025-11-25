<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * Type
 */
class Type extends BaseModel
{
    // 表名
    protected $name = 'kb_type';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected static function onAfterInsert($model)
    {
        if (is_null($model->sort)) {
            $pk = $model->getPk();
            if (strlen($model[$pk]) >= 19) {
                $model->where($pk, $model[$pk])->update(['sort' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['sort' => $model[$pk]]);
            }
        }
    }
}