<?php

namespace app\admin\controller\questionnaire;

use app\common\controller\Backend;

/**
 * 问题管理
 */
class Question extends Backend
{
    /**
     * Question模型对象
     * @var object
     * @phpstan-var \app\admin\model\questionnaire\Question
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected array|string $preExcludeFields = ['id', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\questionnaire\Question;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 获取题目
     */
    public function getQuestions(): void
    {
        $res = $this->model
            ->field('id,title,type')
            ->where('status', '=', 1)
            ->order('id desc')
            ->paginate(10);

        $list = $res->items();
        if (count($list) > 0) {
            foreach ($list as &$v) {
                if ($v['type'] == 1) {
                    $type = '多选题';
                } elseif ($v['type'] == 2) {
                    $type = '填空题';
                } else {
                    $type = '单选题';
                }
                $v['title'] = '【' . $v['id'] . '】【' . $type . '】' . $v['title'];
            }
        }

        $this->success('', [
            'list'   => $list,
            'total'  => $res->total(),
            'remark' => '',
        ]);
    }
}