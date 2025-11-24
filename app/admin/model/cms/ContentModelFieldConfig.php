<?php

namespace app\admin\model\cms;

use think\Model;
use think\model\relation\BelongsTo;

/**
 * ContentModelFieldConfig
 */
class ContentModelFieldConfig extends Model
{
    // 表名
    protected $name = 'cms_content_model_field_config';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected static function onAfterInsert($model)
    {
        if ($model->weigh == 0) {
            $pk       = $model->getPk();
            $maxWeigh = $model->max('weigh');
            $model->where($pk, $model[$pk])->update(['weigh' => $maxWeigh + 1]);
        }
    }

    public function setRuleAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function getRuleAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function cmsContentModel(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\cms\ContentModel::class, 'content_model_id', 'id');
    }
}