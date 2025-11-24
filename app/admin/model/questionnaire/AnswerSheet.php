<?php

namespace app\admin\model\questionnaire;

use modules\questionnaire\library\Tool;
use think\Model;

/**
 * AnswerSheet
 */
class AnswerSheet extends Model
{
    // 表名
    protected $name = 'questionnaire_answer_sheet';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //处理平台数据
    public function getPlatformAttr($value): string
    {
        return strval($value);
    }

    //删除后操作
    public static function onAfterDelete($model): void
    {
        //删除文件
        $list = Answer::where('sid', '=', $model->id)
            ->where('type', 'in', [5, 6, 7])
            ->field('type,checked')
            ->select()
            ->toArray();

        if (count($list) > 0) {
            $files    = [];
            $rootPath = app()->getRootPath() . '/public';
            foreach ($list as $item) {

                if ($item['checked']) {
                    if ($item['type'] == 5) {
                        $files = array_merge($files, $item['checked']);
                    }
                    if ($item['type'] == 6 || $item['type'] == 7) {
                        foreach ($item['checked'] as $v) {
                            $parsed = parse_url($v['url']);
                            $path   = $parsed['path'] ?? '';
                            if ($path) {
                                $files[] = $rootPath . $path;
                            }
                        }
                    }
                }
            }
            if (count($files) > 0) {
                Tool::delFile($files);
            }
        }
        //删除答题
        Answer::where('sid', '=', $model->id)->delete();

    }

}