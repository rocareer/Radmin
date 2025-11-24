<?php

namespace app\admin\validate\cms;

use think\Validate;

class ContentModel extends Validate
{
    protected $failException = true;

    /**
     * 验证规则
     */
    protected $rule = [
        'name'  => 'require|unique:cms_content_model',
        'table' => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{0,63}$|unique:cms_content_model',
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
    ];

    public function __construct()
    {
        $this->field   = [
            'name'  => __('name'),
            'table' => __('table name'),
        ];
        $this->message = array_merge($this->message, [
            'table.regex' => __('table name error')
        ]);
        parent::__construct();
    }

}
