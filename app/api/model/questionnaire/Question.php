<?php

namespace app\api\model\questionnaire;

use modules\questionnaire\library\Tool;
use think\Model;

class Question extends Model
{
    // 表名
    protected $name = 'questionnaire_question';

    // 追加属性
    protected $append = [
        'type_str',//类型文本
        'checked',//选中数据
        'subscript',//下拉框选中的下标
    ];

    //单选多选数据
    public function getOptionsAttr($value): array
    {
        if ($value === '' || $value === null) {
            return [];
        }
        $list  = explode(',', $value);
        $array = [];
        foreach ($list as $v) {
            $array[] = [
                'name'     => $v,
                'disabled' => false
            ];
        }
        return $array;
    }

    //题目类型
    public function getTypeStrAttr($value, $data): string
    {
        return Tool::transitionType($data['type']);
    }

    //数据选中数据字段
    public function getCheckedAttr($value, $data): string|array
    {
        switch ($data['type']) {
            case 1:
            case 5:
            case 6:
            case 7:
                $checked = [];
                break;
            case 0:
            case 2:
            case 4:
            case 3:
            default:
                $checked = '';
                break;
        }
        return $checked;
    }

    //下拉框选中的下标
    public function getSubscriptAttr(): int
    {
        return 0;
    }

    //后缀转换
    public function getFileSuffixAttr($value, $data): array
    {
        $array = [];
        if (in_array($data['type'], [5, 6, 7])) {
            $array = explode(',', $value);
        }
        return $array;
    }
}