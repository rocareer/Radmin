<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

/**
 * 会员名片组件管理
 *
 */
class CardComponent extends Backend
{
    /**
     * CardComponent模型对象
     * @var object
     * @phpstan-var \app\admin\model\user\CardComponent
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected string|array $preExcludeFields = ['id', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\CardComponent;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}