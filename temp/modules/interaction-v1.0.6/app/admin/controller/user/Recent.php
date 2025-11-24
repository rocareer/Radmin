<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use Throwable;

/**
 * 会员最近动态管理
 *
 */
class Recent extends Backend
{
    /**
     * Recent模型对象
     * @var object
     * @phpstan-var \app\admin\model\user\Recent
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected string|array $preExcludeFields = ['id', 'create_time'];

    protected array $withJoinTable = ['user'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\Recent;
        $this->request->filter('trim');
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        $this->request->filter(['strip_tags', 'trim']);
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
            ->paginate($limit)->each(function ($item) {
                $item->content = strip_tags(htmlspecialchars_decode(html_entity_decode($item->content)));
            });

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}