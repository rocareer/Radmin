<?php

namespace app\admin\controller\questionnaire;

use app\common\controller\Backend;
use modules\questionnaire\library\Tool;
use support\Response;
use think\Exception;
use Throwable;

/**
 * 问卷管理
 */
class Examination extends Backend
{
    /**
     * Examination模型对象
     * @var object
     * @phpstan-var \app\admin\model\questionnaire\Examination
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\questionnaire\Examination;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */


    /**
     * 预览
     */
    public function preview(): Response
    {
        $id  = $this->request->post('id');
        $row = $this->model->field('id,title,description,questions,begin_time,end_time')->find($id)->toArray();

        $questionsModel = new \app\admin\model\questionnaire\Question();
        $questions      = $questionsModel->where('id', 'in', $row['questions'])
            ->field('title,type,options,must')
            ->order('weigh desc')
            ->select()
            ->toArray();

        $row['questions'] = $questions;

        return $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 更新二维码
     */
    public function updateQrCode(): Response
    {
        $id     = $this->request->post('id');
        $type   = $this->request->post('type');
        $config = \modules\questionnaire\library\Config::getConfig();

        $row = $this->model->find($id);

        $result = null;
        try {
            if (!$row) {
                throw new Exception('数据不存在');
            }

            if ($type === 'mp') {
                $domain = $config['questionnaire_h5']['domain'];
                if (!$domain) {
                    throw new Exception('请先完善H5域名配置');
                }
                //生成二维码
                $params    = $config['questionnaire_h5']['domain'] . '?id=' . $row->id;
                $codeArray = Tool::qrCode($params, $row->mp);;
                if ($codeArray['code'] == 0) {
                    throw new Exception('二维码生成失败:' . $codeArray['msg']);
                }
                $row->mp = $codeArray['url'];
            }

            if ($type === 'mini') {
                $appid  = $config['questionnaire_mini']['appid'];
                $secret = $config['questionnaire_mini']['secret'];

                if (!$appid || !$secret) {
                    throw new Exception('请先完善小程序配置');
                }
                //生成小程序码
                $params    = 'id=' . $row->id;
                $codeArray = Tool::miniCode($params, $row->mini);
                if ($codeArray['code'] == 0) {
                    throw new Exception('小程序码生成失败:' . $codeArray['msg']);
                }
                $row->mini = $codeArray['url'];
            }
            $result = $row->save();
        } catch (Throwable $e) {
            return $this->error($e->getMessage());
        }

        if ($result) {
            $domain = $this->request->domain();
            return $this->success('', [
                'id'   => $row->id,
                'mini' => $row->mini ? $domain . $row->mini : '',
            ]);
        }
    }

    /**
     * 获取二维码
     */
    public function getQrCode(): Response
    {
        $id     = $this->request->post('id');
        $row    = $this->model->field('id,mini,link')->find($id);
        $domain = $this->request->host();

        if (!$row->link) {
            $config   = \modules\questionnaire\library\Config::getConfig();
            $h5domain = $config['questionnaire_h5']['domain'];
            if ($h5domain) {
                $row->link = $h5domain . '?id=' . $row->id;
                $row->save();
            }
        }

        return $this->success('', [
            'id'   => $row->id,
            'link' => $row->link,
            'mini' => $row->mini ? $domain . $row->mini : '',
        ]);
    }

}