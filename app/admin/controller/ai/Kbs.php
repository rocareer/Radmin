<?php

namespace app\admin\controller\ai;

use app\common\controller\Backend;

/**
 * 知识库管理
 */
class Kbs extends Backend
{
    /**
     * Kbs模型对象
     * @var object
     * @phpstan-var \app\common\model\ai\Kbs
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected array|string $preExcludeFields = ['id', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\ai\Kbs;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}