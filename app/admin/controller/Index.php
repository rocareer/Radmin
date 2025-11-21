<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\common\controller\Backend;
use app\exception\BusinessException;
use support\RequestContext;
use support\Response;
use support\token\Token;
use extend\ra\SystemUtil;
use support\member\Member;
use Throwable;

class Index extends Backend
{
    protected array $noNeedLogin      = ['logout', 'login'];
    protected array $noNeedPermission = ['index'];

    /**
     * 后台初始化请求
     * @throws Throwable
     */
    public function index(): Response
    {

        $menus    = Member::getMenus($this->member->id);

        if (!$menus) {
            return $this->error(__('No background menu, please contact super administrator!'));
        }
        return $this->success('', [
            'adminInfo'  => RequestContext::get('member'),
            'menus'      => $menus,
            'siteConfig' => [
                'siteName'     => SystemUtil::get_sys_config('site_name'),
                'version'      => SystemUtil::get_sys_config('version'),
                'apiUrl'       => config('buildadmin.api_url'),
                'upload'       => keys_to_camel_case(get_upload_config(), ['max_size', 'save_name', 'allowed_suffixes', 'allowed_mime_types']),
                'cdnUrl'       => full_url(),
                'cdnUrlParams' => config('buildadmin.cdn_url_params'),
            ],
            'terminal'   => [
                'phpDevelopmentServer' => str_contains($_SERVER['SERVER_SOFTWARE'], 'Workerman'),
                // 'phpDevelopmentServer' => $_SERVER['SERVER_SOFTWARE'],
                'npmPackageManager'    => config('terminal.npm_package_manager'),
            ]
        ]);
    }

    /**
     * 管理员登录
     * @throws Throwable
     */
    public function login(): Response
    {
        // 检查登录态
        if (RequestContext::get('member')) {
            return $this->success(__('You have already logged in. There is no need to log in again~'), [
                'type' => 'logged in'
            ], 303);
        }

        $captchaSwitch = config('buildadmin.admin_login_captcha');

        if ($this->request->isPost()) {
            try {
                $credentials = [
                    'username'      => $this->request->post('username'),
                    'password'      => $this->request->post('password'),
                    'captchaId'     => $this->request->post('captchaId'),
                    'captchaInfo'   => $this->request->post('captchaInfo'),
                    'captchaSwitch' => $captchaSwitch,
                ];

                $res = Member::login($credentials, (bool)$this->request->post('keep'));
                return $this->success(__('Login succeeded!'), [
                    'userInfo' => $res
                ]);

            } catch (Throwable $e) {
                // 统一错误处理
                $msg = $e->getMessage() ?: __('Incorrect user name or password!');
                return $this->error($msg);
            }
        }

        return $this->success('', [
            'captcha' => $captchaSwitch
        ]);
    }

    /**
     * 管理员注销
     * @throws BusinessException
     */
    public function logout()
    {
        if ($this->request->isPost()) {
            $refreshToken = $this->request->post('refreshToken', '');
            if ($refreshToken) Token::destroy((string)$refreshToken);
            Member::logout();
            return $this->success();
        }
    }
}
