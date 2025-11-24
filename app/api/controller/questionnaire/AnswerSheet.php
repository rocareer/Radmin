<?php

namespace app\api\controller\questionnaire;

use app\common\controller\Api;
use app\common\library\Upload;
use think\facade\Db;
use Throwable;

class AnswerSheet extends Api
{
    protected object $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\api\model\questionnaire\AnswerSheet();
    }

    /**
     * 提交答题
     */
    public function submitAnswer(): void
    {
        $params = $this->request->post();

        // var_dump($params);

        $answerSheet = [
            'title'    => $params['title'],
            'eid'      => $params['id'],
            'platform' => $params['platform'],
            'user'     => $params['user'],
        ];

        $questions = [];
        foreach ($params['questions'] as $v) {
            $options = implode(',', array_column($v['options'], 'name'));

            switch ($v['type']) {
                case 1:
                    //多选
                    $checked = implode(',', $v['checked']);
                    break;
                case 5:
                    $array   = array_column($v['checked'], 'url');
                    $checked = implode(',', $array);
                    break;
                case 6:
                case 7:
                    $array = [];
                    foreach ($v['checked'] as $vv) {
                        $array[] = [
                            'name' => $vv['name'] ?? $vv['url'],
                            'url'  => $vv['url'],
                        ];
                    }
                    $checked = json_encode($array);
                    break;
                default:
                    $checked = $v['checked'];
                    break;
            }

            $questions[] = [
                'title'   => $v['title'],
                'type'    => $v['type'],
                'options' => $options,
                'checked' => $checked,
                'must'    => $v['must']
            ];
        }
        $AnswerModel      = new \app\api\model\questionnaire\Answer();
        $ExaminationModel = new \app\api\model\questionnaire\Examination();
        // 启动事务
        Db::startTrans();
        try {
            $answerSheet = $this->model->create($answerSheet);
            foreach ($questions as $k => $v) {
                $questions[$k]['sid'] = $answerSheet->id;
            }
            $AnswerModel->saveAll($questions);
            $ExaminationModel->where('id', '=', $params['id'])->inc('num', 1)->update();
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败：' . $e->getMessage());
        }
        $this->success('已提交');
    }

    /**
     * 上传文件
     */
    public function upFile(): void
    {
        $file   = $this->request->file('file');
        $size   = $this->request->post('size');
        $suffix = $this->request->post('suffix');

        $driver = 'local';
        $topic  = 'questionnaire';

        $config = [
            'max_size'         => $size . 'mb',//最大上传
            'allowed_suffixes' => $suffix,//允许的后缀
        ];

        try {
            $upload     = new Upload($file, $config);
            $attachment = $upload
                ->setFile($file)
                ->setDriver($driver)
                ->setTopic($topic)
                ->upload();

        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('', ['url' => $attachment['url'] ?? '']);
    }
}