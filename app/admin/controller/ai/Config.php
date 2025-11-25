<?php

namespace app\admin\controller\ai;

use Throwable;
use app\common\library\ai\Helper;
use app\common\controller\Backend;
use app\common\library\ai\Redis;
use app\common\model\ai\KbsContent;
use app\common\model\ai\Config as ConfigModel;

class Config extends Backend
{
    protected object $model;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new ConfigModel();
    }

    public function index(): void
    {
        $data   = $this->model->select();
        $config = [];
        foreach ($data as $k => $v) {
            $config[$v['name']] = $v['value'];
        }
        $this->success('', [
            'data'               => $config,
            'apiModelNames'      => Helper::getModelNames(),
            'chatModelAttr'      => Helper::$chatModelAttr,
            'embeddingModelAttr' => Helper::$embeddingModelAttr,
            'promptTokenCount'   => Helper::calcTokens($config['ai_system_prompt'] . $config['ai_prompt']),
        ]);
    }

    public function save()
    {
        $oldApiType  = $this->model->where('name', 'ai_api_type')->value('value');
        $oldWorkMode = $this->model->where('name', 'ai_work_mode')->value('value');
        $modelName   = Helper::$embeddingModelAttr[$oldApiType]['name'];
        $data        = $this->request->post();
        if ($data['ai_work_mode'] == 'redis') {
            // 检查redis链接
            try {
                Redis::instance();
            } catch (Throwable $e) {
                $this->error('redis服务异常，请先到 config/ai.php 配置好 redis 信息，具体错误为：' . $e->getMessage());
            }
        }
        foreach ($data as $k => $v) {
            if ($k == 'ai_api_type' && $oldApiType != $v) {
                KbsContent::where('model', $modelName)
                    ->update([
                        'status' => 'pending'
                    ]);
            }
            if ($k == 'ai_work_mode' && $oldWorkMode == 'mysql' && $v == 'redis') {
                // 从MySQL切换到redis
                $content = KbsContent::where('model', $modelName)
                    ->where('status', 'usable')
                    ->select();
                foreach ($content as $item) {
                    $item->status = 'success';
                    $item->extend = array_merge($item->extend, [
                        'error' => '工作模式切换，请手动创建 redis 缓存和索引'
                    ]);
                    $item->save();
                }
            }
            $this->model->where('group', $data['tab'])
                ->where('name', $k)
                ->update(['value' => $v]);
        }
        $this->success('当前页配置保存成功！');
    }

    /**
     * 清理数据和索引
     * @throws Throwable
     */
    public function clear()
    {
        $redis = Redis::instance();
        // 删索引
        $redis->indexDel();

        // 删数据
        $keys = $redis->jsonKeys();
        if ($keys) {
            $redis->keysDel($keys);
        }
        $redis->closeClient();

        $this->success('清理成功~');
    }
}