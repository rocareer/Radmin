<?php

namespace app\admin\model\cms;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * Channel
 */
class Channel extends Model
{
    // 表名
    protected $name = 'cms_channel';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $type = [
        'create_time'=>'integer',
        'update_time'=>'integer',
    ];

    protected static function onAfterInsert($model)
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
        }
    }

    public function cmsChannel(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\cms\Channel::class, 'pid', 'id');
    }

    public function cmsContentModel(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\cms\ContentModel::class, 'content_model_id', 'id');
    }
}