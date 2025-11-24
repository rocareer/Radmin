<?php

namespace app\admin\model\questionnaire;

use app\common\model\BaseModel;
use modules\questionnaire\library\Tool;
use think\Model;

/**
 * Answer
 */
class Answer extends BaseModel
{
    // 表名
    protected $name = 'questionnaire_answer';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 追加属性
    protected $append = [
        'type_str',
    ];

    public function getOptionsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function getTypeStrAttr($value, $data): string
    {
        return Tool::transitionType(intval($data['type']));
    }

    public function getCheckedAttr($value, $data): mixed
    {
        switch ($data['type']) {
            case 0:
            case 2:
            case 3:
            case 4:
            default:
                $checked = $value;
                break;
            case 1:
                $checked = $value ? explode(',', $value) : [];
                break;
            case 5:
                $checked = [];
                if ($value) {
                    $array = explode(',', $value);
                    foreach ($array as $item) {
                        $checked[] = full_url($item);
                    }
                }
                break;
            case 6:
            case 7:
                $checked = [];
                if ($value) {
                    $array = json_decode($value, true);
                    foreach ($array as $item) {
                        $url = full_url($item['url']);
                        if ($data['type'] == 6) {
                            $type = 'video';
                        } else {
                            $type = Tool::fileType($url);
                        }
                        $checked[] = [
                            'name' => $item['name'],
                            'url'  => $url,
                            'type' => $type
                        ];
                    }
                }
                break;
        }
        return $checked;
    }

    public function getMustAttr($value): string
    {
        return strval($value);
    }

    public function getTypeAttr($value): string
    {
        return strval($value);
    }

}