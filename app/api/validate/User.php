<?php


namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $failException = true;

    protected $rule = [
        'username'     => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{2,15}$|unique:user',
        'password'     => 'require|regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
        'registerType' => 'require|in:email,mobile',
        'email'        => 'email|unique:user|requireIf:registerType,email',
        'mobile'       => 'mobile|unique:user|requireIf:registerType,mobile',
        'captcha'      => 'require', // 注册验证码
        'captchaId'    => 'require', // 登录验证码ID
        'captchaInfo'  => 'require', // 登录验证码信息
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'register' => ['username', 'password', 'registerType', 'email', 'mobile', 'captcha'],
        'login'    => ['username', 'password'], // 基础登录字段
    ];

    /**
     * 登录验证场景（动态添加验证码字段）
     */
    public function sceneLogin(): User
    {
        $fields = ['username', 'password'];

        // 根据系统配置动态添加验证码字段
        if (config('buildadmin.user_login_captcha', false)) {
            $fields[] = 'captchaId';
            $fields[] = 'captchaInfo';
        }

        return $this->only($fields)->remove('username', ['regex', 'unique']);
    }

    public function __construct()
    {
        $this->field = [
            'username'     => __('username'),
            'email'        => __('email'),
            'mobile'       => __('mobile'),
            'password'     => __('password'),
            'captcha'      => __('captcha'),
            'captchaId'    => __('captchaId'),
            'captchaInfo'  => __('captcha'),
            'registerType' => __('Register type'),
        ];
        
        $this->message = array_merge($this->message, [
            'username.regex' => __('Please input correct username'),
            'password.regex' => __('Please input correct password')
        ]);
        
        parent::__construct();
    }
}