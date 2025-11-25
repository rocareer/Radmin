<?php

namespace app\admin\controller\ai;

use app\common\library\ai\Helper;
use app\common\controller\Backend;

/**
 * 对话模型管理
 */
class ChatModel extends Backend
{
    /**
     * ChatModel模型对象
     * @var object
     * @phpstan-var \app\common\model\ai\ChatModel
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected array|string $preExcludeFields = ['id', 'create_time'];

    protected string|array $quickSearchField = ['id', 'title'];

    protected array $noNeedPermission = ['getModelData'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\ai\ChatModel;
    }

    public function getModelData()
    {
        $this->success('', [
            'content'       => Helper::getModelNames(),
            'chatModelAttr' => Helper::$chatModelAttr,
        ]);
    }
}