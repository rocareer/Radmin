<?php

namespace app\admin\controller\kb;

use app\common\controller\Backend;
use support\Response;
use extend\ra\SystemUtil;

/**
 * 知识库分类
 */
class Category extends Backend
{
    /**
     * Category模型对象
     * @var object
     * @phpstan-var \app\admin\model\kb\Category
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['name'];

    public function initialize():void
    {
        parent::initialize();
        $this->model = new \app\admin\model\kb\Category();
    }


    /**
     * 查看
     * @throws Throwable
     */
    public function index():Response
    {
        if ($this->request->input('select')) {
            return $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        // 统计每个分类下的知识库数量
        $items = $res->items();
        foreach ($items as &$item) {
            $item['kb_count'] = \app\admin\model\kb\Kb::where('kb_category_id', $item['id'])->count();
        }

        return $this->success('', [
            'list'   => $items,
            'total'  => $res->total(),
            'remark' => SystemUtil::get_route_remark(),
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}