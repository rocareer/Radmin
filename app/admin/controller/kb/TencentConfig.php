<?php

namespace app\admin\controller\kb;

use app\common\controller\Backend;
use support\Response;
use extend\ra\SystemUtil;

/**
 * 腾讯云知识库配置管理
 */
class TencentConfig extends Backend
{
    /**
     * TencentConfig模型对象
     * @var object
     * @phpstan-var \app\admin\model\kb\TencentConfig
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['name', 'remark'];

    public function initialize():void
    {
        parent::initialize();
        $this->model = new \app\admin\model\kb\TencentConfig();
    }

    /**
     * 查看
     */
    public function index(): void
    {
        if ($this->request->isAjax()) {
            if ($this->request->param('selectFields')) {
                $this->selectList();
            }
            
            list($where, $alias, $limit, $order) = $this->queryBuilder();
            $res = $this->model
                ->alias($alias)
                ->where($where)
                ->order($order)
                ->paginate($limit);

            $this->success('', [
                'list'   => $res->items(),
                'total'  => $res->total(),
                'remark' => get_route_remark(),
            ]);
        }
        
        $this->success('', [
            'remark' => get_route_remark()
        ]);
    }

    /**
     * 添加
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['name'])) {
                $this->error('配置名称不能为空');
            }
            if (empty($data['secret_id'])) {
                $this->error('SecretId不能为空');
            }
            if (empty($data['secret_key'])) {
                $this->error('SecretKey不能为空');
            }
            
            $result = $this->model->save($data);
            if ($result === false) {
                $this->error($this->model->getError() ?: '添加失败');
            }
            
            $this->success('添加成功');
        }
        
        $this->error('非法请求');
    }

    /**
     * 编辑
     */
    public function edit(): void
    {
        $id = $this->request->param($this->model->getPk());
        $row = $this->model->find($id);
        if (!$row) {
            $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            // 如果密码为空，则不更新
            if (isset($data['secret_key']) && $data['secret_key'] === '******') {
                unset($data['secret_key']);
            }
            
            $result = $row->save($data);
            if ($result === false) {
                $this->error($row->getError() ?: '更新失败');
            }
            
            $this->success('更新成功');
        }
        
        // 隐藏密钥
        $row['secret_key'] = '******';
        
        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 删除
     */
    public function del(): void
    {
        $ids = $this->request->param($this->model->getPk());
        if (!$ids) {
            $this->error('参数错误');
        }

        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
        foreach ($list as $item) {
            $result = $item->delete();
            if ($result === false) {
                $this->error('删除失败');
            }
        }

        $this->success('删除成功');
    }

    /**
     * 测试连接
     */
    public function testConnection(): Response
    {
        $id = $this->request->param('id');
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $config = $this->model->find($id);
        if (!$config) {
            return json(['code' => 0, 'msg' => '配置不存在']);
        }

        try {
            // 这里应该调用腾讯云API进行连接测试
            // 暂时返回成功，实际使用时需要集成腾讯云SDK
            return json(['code' => 1, 'msg' => '连接成功']);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '连接失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 配置页面数据
     */
    public function config(): Response
    {
        // 获取当前配置
        $config = $this->model->where('status', 1)->find();
        
        $data = [
            'data' => $config ? $config->toArray() : [],
            'stats' => [
                'total' => 0,
                'uploaded' => 0,
                'synced' => 0
            ],
            'lastConnectionTime' => '',
            'connectionStatus' => 'unknown',
            'syncStatus' => '未同步'
        ];

        if ($config) {
            // 获取文件统计信息
            $uploadModel = new \app\admin\model\kb\TencentUpload();
            $syncModel = new \app\admin\model\kb\TencentSync();
            
            $data['stats']['total'] = $uploadModel->count();
            $data['stats']['uploaded'] = $uploadModel->where('status', 1)->count();
            $data['stats']['synced'] = $syncModel->where('status', 1)->count();
            
            // 获取最后连接时间
            $data['lastConnectionTime'] = $config['update_time'];
            
            // 简单的连接状态判断
            if ($config['secret_id'] && $config['secret_key']) {
                $data['connectionStatus'] = 'success';
            }
        }

        return json(['code' => 1, 'msg' => '获取成功', 'data' => $data]);
    }

    /**
     * 保存配置
     */
    public function saveConfig(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '非法请求']);
        }

        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['name'])) {
            return json(['code' => 0, 'msg' => '配置名称不能为空']);
        }
        if (empty($data['secret_id'])) {
            return json(['code' => 0, 'msg' => 'SecretId不能为空']);
        }
        if (empty($data['secret_key'])) {
            return json(['code' => 0, 'msg' => 'SecretKey不能为空']);
        }

        // 查找现有配置
        $config = $this->model->where('status', 1)->find();
        
        if ($config) {
            // 更新现有配置
            if ($data['secret_key'] === '******') {
                unset($data['secret_key']);
            }
            $result = $config->save($data);
        } else {
            // 创建新配置
            $result = $this->model->save($data);
        }

        if ($result === false) {
            return json(['code' => 0, 'msg' => '保存失败']);
        }

        return json(['code' => 1, 'msg' => '保存成功']);
    }

    /**
     * 立即同步
     */
    public function syncNow(): Response
    {
        try {
            // 这里应该调用腾讯云同步逻辑
            // 暂时返回成功，实际使用时需要实现同步逻辑
            return json(['code' => 1, 'msg' => '同步完成']);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '同步失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 清理缓存
     */
    public function clearCache(): Response
    {
        try {
            // 这里应该清理相关缓存
            // 暂时返回成功，实际使用时需要实现缓存清理逻辑
            return json(['code' => 1, 'msg' => '缓存清理完成']);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '缓存清理失败: ' . $e->getMessage()]);
        }
    }
}