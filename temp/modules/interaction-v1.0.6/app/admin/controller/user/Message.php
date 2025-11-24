<?php

namespace app\admin\controller\user;

use Throwable;
use app\common\controller\Backend;

/**
 * 会员消息管理
 *
 */
class Message extends Backend
{
    /**
     * Message模型对象
     * @var object
     * @phpstan-var \app\admin\model\user\Message
     */
    protected object $model;

    protected string|array $preExcludeFields = ['id', 'create_time'];

    protected array $withJoinTable = ['user', 'recipient'];

    protected string|array $quickSearchField = ['content', 'id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\Message;
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        $this->request->filter(['strip_tags', 'trim']);
        // 如果是select则转发到select方法,若select未重写,其实还是继续执行index
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->visible(['user' => ['username'], 'recipient' => ['username']])
            ->paginate($limit)->each(function ($item) {
                $item->content = strip_tags(htmlspecialchars_decode(html_entity_decode($item->content)));
            });

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }
}