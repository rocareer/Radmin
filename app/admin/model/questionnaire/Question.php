<?php

namespace app\admin\model\questionnaire;

use modules\questionnaire\library\Tool;
use think\Model;

/**
 * Question
 */
class Question extends Model
{
    // 表名
    protected $name = 'questionnaire_question';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 追加属性
    protected $append = [
        'type_str',
        'checked'
    ];

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

    public function getOptionsAttr($value): array
    {
        if ($value === '' || $value === null) {
            return [];
        }
        return explode(',', $value);
    }

    public function setOptionsAttr($value): string
    {
        if ($value === '' || $value === null) {
            return '';
        }
        return implode(',', $value);
    }

    public function getTypeAttr($value): string
    {
        return strval($value);
    }

    public function getTypeStrAttr($value, $data): string
    {
        return Tool::transitionType(intval($data['type']));
    }

    public function getCheckedAttr($value, $data): mixed
    {
        if ($data['type'] == 1) {
            $checked = [];
        } else {
            $checked = $value;
        }
        return $checked;
    }

    public function setFileSuffixAttr($value, $data): string
    {
        $string = '';
        if (in_array($data['type'], [5, 6, 7])) {
            $string = implode(',', $value);
        }
        return $string;
    }

    public function getFileSuffixAttr($value, $data): array
    {
        $array = [];
        if (in_array($data['type'], [5, 6, 7])) {
            if ($value) {
                $array = explode(',', $value);
            }
        }
        return $array;
    }
}