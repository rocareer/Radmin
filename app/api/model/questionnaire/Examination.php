<?php

namespace app\api\model\questionnaire;

use think\Model;

class Examination extends Model
{
    // 表名
    protected $name = 'questionnaire_examination';

    public function getQuestionsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }
}