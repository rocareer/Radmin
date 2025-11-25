<?php

namespace app\common\model\ai;

use think\Model;

/**
 * AI配置
 */
class Config extends Model
{
    // 表名
    protected $name = 'ai_config';

    protected static array $values = [
        'int' => [
            'ai_accurate_hit',
            'ai_short_memory',
            'ai_effective_match_kbs',
            'ai_effective_kbs_count',
            'ai_irrelevant_determination',
            'ai_short_memory_percent',
            'ai_prompt_percent',
            'ai_kbs_percent',
            'ai_user_input_percent',
            'ai_response_percent',
            'ai_gift_tokens',
            'ai_score_exchange_tokens',
            'ai_money_exchange_tokens',
        ],
    ];

    public static function onAfterRead($model)
    {
        if (in_array($model->getData('name'), self::$values['int'])) {
            $model->value = (float)$model->value;
        }
    }
}