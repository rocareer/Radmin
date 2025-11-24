<?php

namespace app\admin\controller\cms;

use Throwable;
use app\common\controller\Backend;

/**
 * 用户搜索记录
 *
 */
class SearchLog extends Backend
{
    /**
     * SearchLog模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\SearchLog
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected string|array $preExcludeFields = ['id', 'create_time'];

    protected array $withJoinTable = ['user'];

    protected string|array $quickSearchField = ['id', 'search'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\SearchLog;
        $this->request->filter('clean_xss');
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        // 如果是select则转发到select方法,若select未重写,其实还是继续执行index
        if ($this->request->input('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->visible(['user' => ['username']])
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
}