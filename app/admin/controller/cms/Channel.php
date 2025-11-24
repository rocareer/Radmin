<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use extend\ba\Tree;
use extend\ra\SystemUtil;
use support\Response;
use Throwable;

/**
 * 频道管理
 *
 */
class Channel extends Backend
{
    /**
     * @var ?Tree
     */
    protected ?Tree $tree = null;
    /**
     * Channel模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Channel
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected string|array $preExcludeFields = ['id', 'update_time', 'create_time'];

    protected array $withJoinTable = ['cmsChannel', 'cmsContentModel'];

    protected string|array $quickSearchField = ['id', 'name'];

    public function initialize(): void
    {
        parent::initialize();
        $this->tree  = Tree::instance();
        $this->model = new \app\admin\model\cms\Channel;
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
            ->visible(['cmsChannel' => ['name'], 'cmsContentModel' => ['name']])
            ->select();
        $res = $this->tree->assembleChild($res->toArray());

        if ($this->request->input('select')) {
            $res = $this->tree->assembleTree($this->tree->getTreeArray($res));
        }

        return $this->success('', [
            'list'   => $res,
            'remark' => SystemUtil::get_route_remark(),
        ]);
    }
}