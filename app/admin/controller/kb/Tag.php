<?php

namespace app\admin\controller\kb;

use app\common\controller\Backend;
use support\Response;
use extend\ra\SystemUtil;

/**
 * 知识库标签管理
 */
class Tag extends Backend
{
    /**
     * Tag模型对象
     * @var object
     * @phpstan-var \app\admin\model\kb\Tag
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['name'];

    public function initialize():void
    {
        parent::initialize();
        $this->model = new \app\admin\model\kb\Tag();
    }

    /**
     * 获取标签列表（用于选择器）
     */
    public function select(): Response
    {
        $keyword = $this->request->param('keyword', '');
        $limit = $this->request->param('limit', 20);
        
        $query = $this->model->where('status', 1);
        
        if (!empty($keyword)) {
            $query->where('name', 'like', "%{$keyword}%");
        }
        
        $list = $query->order('count DESC, id DESC')->limit($limit)->select();
        
        return json(['code' => 1, 'data' => $list]);
    }

    /**
     * 获取热门标签
     */
    public function hotTags(): Response
    {
        $limit = $this->request->param('limit', 10);
        
        $list = $this->model
            ->where('status', 1)
            ->order('count DESC, id DESC')
            ->limit($limit)
            ->select();
        
        return json(['code' => 1, 'data' => $list]);
    }
}