<?php
/**
 * File:        Index.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/12 02:34
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

/** @noinspection PhpDynamicAsStaticMethodCallInspection */
/** @noinspection PhpUndefinedVariableInspection */


namespace app\user\controller;

use app\api\validate\User as UserValidate;
use app\common\controller\Frontend;
use extend\ba\Captcha;
use extend\ba\ClickCaptcha;
use app\exception\UnauthorizedHttpException;
use support\Response;
use support\token\Token;
use support\member\Member;
use support\StatusCode;
use Throwable;

class Index extends Frontend
{
    protected array $noNeedLogin = ['login', 'logout'];

    protected array $noNeedPermission = ['index'];

    public function initialize():void
    {
        parent::initialize();

    }

    /**
     * 会员签入(登录和注册)
     * @throws Throwable
     */
    public function login(): Response
    {
        // 检查会员中心是否开启
        if (!config('buildadmin.open_member_center')) {
            return $this->error(__('Member center disabled'));
        }

        if ($this->request->isPost()) {
            return $this->handlePostLogin();
        }

        return $this->success('', [
            'userLoginCaptchaSwitch'  => config('buildadmin.user_login_captcha'),
            'accountVerificationType' => get_account_verification_type()
        ]);
    }

    /**
     * 处理POST登录请求
     */
    private function handlePostLogin(): Response
    {
        try {
            $params = $this->request->only(['tab', 'email', 'mobile', 'username', 'password', 'keep', 'captcha', 'captchaId', 'captchaInfo', 'registerType']);
            
            // 验证操作类型
            if (!in_array($params['tab'] ?? '', ['login', 'register'])) {
                return $this->error(__('Unknown operation'));
            }

            // 数据验证
            $validate = new UserValidate();
            $validate->scene($params['tab'])->check($params);

            $res = $params['tab'] == 'login' 
                ? $this->handleLogin($params) 
                : $this->handleRegister($params);

            return $this->success(__('Login succeeded!'), [
                'userInfo'  => $res,
                'routePath' => '/user'
            ]);

        } catch (Throwable $e) {
            return $this->error($e->getMessage() ?: __('Check in failed, please try again or contact the website administrator~'));
        }
    }

    /**
     * 处理登录逻辑
     * @param array $params
     * @return array
     * @throws Throwable
     */
    private function handleLogin(array $params): array
    {
        // 验证码验证
        $this->validateCaptcha($params);
        
        // 构建登录凭证
        $credentials = $this->buildLoginCredentials($params);
        
        return Member::login($credentials, !empty($params['keep']));
    }

    /**
     * 验证验证码
     * @param array $params
     * @throws \InvalidArgumentException
     */
    private function validateCaptcha(array $params): void
    {
        $captchaSwitch = config('buildadmin.user_login_captcha');
        
        if ($captchaSwitch) {
            $captchaObj = new ClickCaptcha();
            if (!$captchaObj->check($params['captchaId'], $params['captchaInfo'])) {
                throw new \InvalidArgumentException(__('Captcha error'));
            }
        }
    }

    /**
     * 构建登录凭证
     * @param array $params
     * @return array
     */
    private function buildLoginCredentials(array $params): array
    {
        return [
            'username'      => $params['username'],
            'password'      => $params['password'],
            'captchaId'     => $params['captchaId'] ?? '',
            'captchaInfo'   => $params['captchaInfo'] ?? '',
            'captchaSwitch' => config('buildadmin.user_login_captcha'),
        ];
    }

    /**
     * 处理注册逻辑
     * @param array $params
     * @return mixed
     * @throws Throwable
     */
    private function handleRegister(array $params)
    {
        $captchaObj = new Captcha();
        if (!$captchaObj->check($params['captcha'], $params[$params['registerType']] . 'user_register')) {
            throw new \InvalidArgumentException(__('Please enter the correct verification code'));
        }

        return $this->auth->register($params['username'], $params['password'], $params['mobile'], $params['email']);
    }

    /**
     * 用户注销 - 支持多角色隔离注销
     * @throws UnauthorizedHttpException
     */
    public function logout(): Response
    {
        if ($this->request->isPost()) {
            $refreshToken = $this->request->post('refreshToken', '');
            
            // 销毁当前角色的refreshToken
            if ($refreshToken) {
                try {
                    $payload = Token::verify((string)$refreshToken);
                    if ($payload->role === 'user') {
                        Token::destroy((string)$refreshToken);
                    }
                } catch (\Throwable $e) {
                    // Token验证失败，继续执行注销逻辑
                }
            }
            
            // 执行用户角色注销
            Member::logout();
            
            return $this->success();
        }
        throw new UnauthorizedHttpException('Unauthorized', __('Unauthorized'),StatusCode::UNAUTHORIZED);
    }

    /**
     * 获取用户个人信息
     */
    public function profile(): Response
    {
        try {
            // 检查用户是否已登录
            if (!$this->member->isLogin()) {
                return $this->error(__('Please login first'), StatusCode::NEED_LOGIN);
            }

            // 获取当前用户信息
            $userInfo = $this->member->getUserInfo();
            
            return $this->success('', [
                'userInfo' => $userInfo
            ]);
            
        } catch (Throwable $e) {
            return $this->error($e->getMessage() ?: __('Failed to get user profile'));
        }
    }

    /**
     * 用户注册
     */
    public function register(): Response
    {
        try {
            // 检查会员中心是否开启
            if (!config('buildadmin.open_member_center')) {
                return $this->error(__('Member center disabled'));
            }

            if (!$this->request->isPost()) {
                return $this->error(__('Method not allowed'));
            }

            $params = $this->request->only(['username', 'password', 'mobile', 'email', 'captcha', 'registerType']);
            
            // 数据验证
            $validate = new UserValidate();
            $validate->scene('register')->check($params);

            // 验证码验证
            $captchaObj = new Captcha();
            if (!$captchaObj->check($params['captcha'], $params[$params['registerType']] . 'user_register')) {
                throw new \InvalidArgumentException(__('Please enter the correct verification code'));
            }

            // 执行注册逻辑
            $result = $this->auth->register($params['username'], $params['password'], $params['mobile'], $params['email']);
            
            return $this->success(__('Registration successful'), [
                'userInfo' => $result
            ]);
            
        } catch (Throwable $e) {
            return $this->error($e->getMessage() ?: __('Registration failed'));
        }
    }
}