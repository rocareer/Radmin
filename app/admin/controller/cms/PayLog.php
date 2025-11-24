<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use extend\ra\SystemUtil;
use support\Response;
use Throwable;

/**
 * 用户支付记录
 *
 */
class PayLog extends Backend
{
    /**
     * PayLog模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\PayLog
     */
    protected object $model;

    protected string|array $preExcludeFields = ['id', 'pay_time', 'create_time'];

    protected array $withJoinTable = ['user', 'cmsContent'];

    protected string|array $quickSearchField = ['id', 'title'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\PayLog;
        $this->request->filter('clean_xss');
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): Response
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
            ->visible(['user' => ['username'], 'cmsContent' => ['title']])
            ->paginate($limit);

        return $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => SystemUtil::get_route_remark(),
        ]);
    }
}