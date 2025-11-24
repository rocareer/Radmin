<?php

namespace app\admin\controller\questionnaire;

use app\common\controller\Backend;
use support\Response;

/**
 * 答卷管理
 */
class AnswerSheet extends Backend
{
    /**
     * AnswerSheet模型对象
     * @var object
     * @phpstan-var \app\admin\model\questionnaire\AnswerSheet
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'eid', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\questionnaire\AnswerSheet;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */


    /**
     * 查阅
     */
    public function look(): Response
    {
        $id = $this->request->post('id');

        $row              = $this->model->field('id,title')->find($id)->toArray();
        $answerModel      = new \app\admin\model\questionnaire\Answer();
        $questions        = $answerModel->where('sid', '=', $row['id'])
            ->field('title,type,options,checked,must')
            ->select()
            ->toArray();
        $row['questions'] = $questions;

        return $this->success('', [
            'row' => $row
        ]);
    }
}