<?php

namespace app\admin\controller\kb;

use app\common\controller\Backend;
use support\Response;
use extend\ra\SystemUtil;

/**
 * 知识库内容管理
 */
class Content extends Backend
{
    /**
     * Content模型对象
     * @var object
     * @phpstan-var \app\admin\model\kb\Content
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['title', 'content', 'keywords'];

    public function initialize():void
    {
        parent::initialize();
        $this->model = new \app\admin\model\kb\Content();
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
                ->with(['kb', 'type', 'category', 'admin'])
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
            
            // 设置创建者
            $data['admin_id'] = $this->admin['id'] ?? 0;
            
            // 处理发布时间
            if (empty($data['publish_time'])) {
                $data['publish_time'] = time();
            } else {
                $data['publish_time'] = strtotime($data['publish_time']);
            }
            
            // 处理标签
            $tagIds = [];
            if (!empty($data['tag_ids'])) {
                $tagIds = is_array($data['tag_ids']) ? $data['tag_ids'] : explode(',', $data['tag_ids']);
                unset($data['tag_ids']);
            }
            
            $result = $this->model->save($data);
            if ($result === false) {
                $this->error($this->model->getError() ?: '添加失败');
            }
            
            // 保存标签关联
            if (!empty($tagIds)) {
                $this->model->tags()->saveAll($tagIds);
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
            
            // 处理发布时间
            if (!empty($data['publish_time'])) {
                $data['publish_time'] = strtotime($data['publish_time']);
            }
            
            // 处理标签
            if (isset($data['tag_ids'])) {
                $tagIds = is_array($data['tag_ids']) ? $data['tag_ids'] : explode(',', $data['tag_ids']);
                unset($data['tag_ids']);
            }
            
            $result = $row->save($data);
            if ($result === false) {
                $this->error($row->getError() ?: '更新失败');
            }
            
            // 更新标签关联
            if (isset($tagIds)) {
                $row->tags()->detach();
                $row->tags()->attach($tagIds);
            }
            
            $this->success('更新成功');
        }
        
        $this->error('非法请求');
    }

    /**
     * 获取内容详情
     */
    public function detail($id): Response
    {
        $row = $this->model->with(['type', 'category', 'tags', 'attachments'])->find($id);
        if (!$row) {
            return json(['code' => 0, 'msg' => '记录不存在']);
        }
        
        return json(['code' => 1, 'data' => $row]);
    }

    /**
     * 更新浏览量
     */
    public function updateViews($id): Response
    {
        $row = $this->model->find($id);
        if (!$row) {
            return json(['code' => 0, 'msg' => '记录不存在']);
        }
        
        $row->views = $row->views + 1;
        $row->save();
        
        return json(['code' => 1, 'data' => ['views' => $row->views]]);
    }

    /**
     * 搜索内容
     */
    public function search(): Response
    {
        $keyword = $this->request->param('keyword', '');
        $limit = $this->request->param('limit', 10);
        
        if (empty($keyword)) {
            return json(['code' => 0, 'msg' => '请输入关键词']);
        }
        
        $list = $this->model
            ->with(['type', 'category'])
            ->where('status', 1)
            ->where('title|content|keywords', 'like', "%{$keyword}%")
            ->order('is_top DESC, publish_time DESC')
            ->limit($limit)
            ->select();
        
        return json(['code' => 1, 'data' => $list]);
    }
}