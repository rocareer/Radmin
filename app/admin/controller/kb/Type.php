<?php

namespace app\admin\controller\kb;

use app\common\controller\Backend;
use support\Response;
use extend\ra\SystemUtil;

/**
 * 知识库类型
 */
class Type extends Backend
{
    /**
     * Type模型对象
     * @var object
     * @phpstan-var \app\admin\model\kb\Type
     */
    protected object $model;

    protected string|array $defaultSortField = 'sort,desc';

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected string $weighField = 'sort';

    protected string|array $quickSearchField = ['id'];

    public function initialize():void
    {
        parent::initialize();
        $this->model = new \app\admin\model\kb\Type();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}