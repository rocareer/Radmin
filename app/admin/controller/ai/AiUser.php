<?php

namespace app\admin\controller\ai;

use app\common\controller\Backend;

/**
 * AI会员管理
 */
class AiUser extends Backend
{
    /**
     * AiUser模型对象
     * @var object
     * @phpstan-var \app\common\model\ai\AiUser
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'tokens', 'last_use_time', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\ai\AiUser;
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}