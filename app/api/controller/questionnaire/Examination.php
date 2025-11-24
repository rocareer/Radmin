<?php

namespace app\api\controller\questionnaire;

use app\common\controller\Api;

class Examination extends Api
{

    protected object $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\api\model\questionnaire\Examination();
    }

    /***
     * 获取问卷
     */
    public function getExamination(): void
    {
        $id   = $this->request->post('id'); //问卷ID
        $user = $this->request->post('user'); //用户标识

        if (!$id) {
            $this->success('', [
                'status' => 0,
                'msg'    => '暂无问卷'
            ]);
        }

        $row = $this->model
            ->where('id', '=', $id)
            ->field('id,title,description,questions,begin_time,end_time,status')
            ->find();

        if (!$row) {
            $this->success('', [
                'status' => 0,
                'msg'    => '暂无问卷'
            ]);
        }

        if (intval($row->status) === 0) {
            $this->success('', [
                'status' => 4,
                'msg'    => '问卷未开启'
            ]);
        }

        if ($row->begin_time > time()) {
            $this->success('', [
                'status' => 3,
                'msg'    => '问卷未开始'
            ]);
        }

        if ($row->end_time < time()) {
            $this->success('', [
                'status' => 3,
                'msg'    => '问卷已结束'
            ]);
        }
        $answerSheetModel = new \app\api\model\questionnaire\AnswerSheet();
        $count            = $answerSheetModel->where('user', '=', $user)
            ->where('eid', '=', $row->id)
            ->count();

        if ($count > 0) {
            $this->success('', [
                'status' => 2,
                'msg'    => '此问卷您已完成'
            ]);
        }

        $row = $row->toArray();

        $questionsModel = new \app\api\model\questionnaire\Question();
        $questions      = $questionsModel->where('id', 'in', $row['questions'])
            ->field('title,must,type,options,file_size,file_num,file_suffix')
            ->order('weigh desc')
            ->select()
            ->toArray();

        $row['questions'] = $questions;

        $this->success('操作成功', [
            'row'    => $row,
            'status' => 1
        ]);
    }
}