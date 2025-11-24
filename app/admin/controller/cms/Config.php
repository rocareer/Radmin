<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\admin\model\cms\Config as ConfigModel;

class Config extends Backend
{
    protected object $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new ConfigModel;
        $this->request->filter('clean_xss');
    }

    public function index(): void
    {
        $data   = $this->model->select();
        $config = [];
        foreach ($data as $k => $v) {
            $config[$v['name']] = $v['value'];
        }
        $this->success('', [
            'data' => $config,
        ]);
    }

    public function save(): void
    {
        $data      = $this->request->post();
        $activeTab = $data['activeTab'] ?? 'base';
        unset($data['activeTab']);

        foreach ($data as $k => $v) {
            $this->model->where('name', $k)
                ->where('group', $activeTab)
                ->update(['value' => $v]);
        }
        $this->success('当前页配置保存成功！');
    }
}