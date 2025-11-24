<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\admin\model\cms\Config as ConfigModel;
use support\Response;

class Config extends Backend
{
    protected object $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new ConfigModel;
        $this->request->filter('clean_xss');
    }

    public function index(): Response
    {
        $data   = $this->model->select();
        $config = [];
        foreach ($data as $k => $v) {
            $config[$v['name']] = $v['value'];
        }
        return $this->success('', [
            'data' => $config,
        ]);
    }

    public function save(): Response
    {
        $data      = $this->request->post();
        $activeTab = $data['activeTab'] ?? 'base';
        unset($data['activeTab']);

        foreach ($data as $k => $v) {
            $this->model->where('name', $k)
                ->where('group', $activeTab)
                ->update(['value' => $v]);
        }
        return $this->success('当前页配置保存成功！');
    }
}