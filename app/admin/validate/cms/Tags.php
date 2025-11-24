<?php

namespace app\admin\validate\cms;

use think\Validate;

class Tags extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'name' => 'require|unique:cms_tags',
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];


    public function __construct()
    {
        $this->field = [
            'name' => __('name'),
        ];
        parent::__construct();
    }
}
