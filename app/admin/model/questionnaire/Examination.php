<?php

namespace app\admin\model\questionnaire;

use modules\questionnaire\library\Config;
use modules\questionnaire\library\Tool;
use think\Model;

/**
 * Examination
 */
class Examination extends Model
{
    // 表名
    protected $name = 'questionnaire_examination';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 追加属性
    protected $append = [
        'questionsTable',
        'date'
    ];

    public function getQuestionsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setQuestionsAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function getDateAttr($value, $data): array
    {
        return [
            date('Y-m-d H:i:s', $data['begin_time']),
            date('Y-m-d H:i:s', $data['end_time']),
        ];
    }

    public function setBeginTimeAttr($value, $data): int
    {
        return strtotime($data['date'][0]);
    }

    public function setEndTimeAttr($value, $data): int
    {
        return strtotime($data['date'][1]);
    }

    public static function onAfterInsert($model): bool
    {
        $config = Config::getConfig();

        if ($config['questionnaire_h5']['domain']) {
            //生成链接
            $model->link = $config['questionnaire_h5']['domain'] . '?id=' . $model->id;
        }
        if ($config['questionnaire_mini']['appid'] && $config['questionnaire_mini']['secret']) {
            //生成小程序码
            $params   = 'id=' . $model->id;
            $miniCode = Tool::miniCode($params);
            if ($miniCode['code'] === 1) {
                $model->mini = $miniCode['url'];
            }
        }
        $model->save();
        return true;
    }
}