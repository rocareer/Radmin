<?php

namespace app\admin\controller\examples\table;

use ba\Tree;
use Throwable;
use app\common\controller\Backend;

/**
 * 树状表格示例
 * 1. 请设置 quickSearchField 属性至少包含`树状字段`，方便远程下拉时通过该名称进行搜索，第28行
 * 2. 请参考 57-61 和 66-71 行的`注释一`和`注释二`
 */
class Treetable extends Backend
{
    /**
     * @var ?Tree
     */
    protected ?Tree $tree;

    /**
     * Treetable 模型对象
     * @var object
     * @phpstan-var \app\admin\model\examples\table\Treetable
     */
    protected object $model;

    protected string|array $quickSearchField = ['name'];

    protected string|array $defaultSortField = 'id,desc';

    protected string|array $preExcludeFields = ['create_time'];

    public function initialize(): void
    {
        parent::initialize();
        $this->tree  = Tree::instance();
        $this->model = new \app\admin\model\examples\table\Treetable();
    }

    /**
     * 重写查看方法
     * @throws Throwable
     */
    public function index(): void
    {
        $this->request->filter(['strip_tags', 'trim']);

        list($where, $alias) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->alias($alias)
            ->where($where)
            ->select()
            ->toArray();

        /**
         * 树状表格必看注释一
         * 1. 获取表格数据（没有分页，所以简化了以上的数据查询代码）
         * 2. 递归的根据指定字段组装 children 数组，此时直接给前端，表格就可以正常的渲染为树状了，一个方法搞定
         */
        $res = $this->tree->assembleChild($res);

        if ($this->request->param('select')) {

            /**
             * 树状表格必看注释二
             * 1. 在远程 select 中，数据要成树状显示，需要对数据做一些改动
             * 2. 通过已组装好 children 的数据，建立`树枝`结构，并最终合并为一个二维数组方便渲染
             * 3. 简单讲就是把组装好 children 的数据，给以下这两个方法即可
             */
            $res = $this->tree->assembleTree($this->tree->getTreeArray($res));
        }

        $this->success('', [
            'list'   => $res,
            'remark' => get_route_remark(),
        ]);
    }

}