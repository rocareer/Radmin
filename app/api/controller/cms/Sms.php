<?php

namespace app\api\controller\cms;

use Throwable;
use ba\Captcha;
use ba\ClickCaptcha;
use modules\sms\Sms as smsLib;
use think\facade\Event;
use think\facade\Validate;
use app\common\controller\Frontend;

class Sms extends Frontend
{
    protected array $noNeedLogin = ['send'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 发送登录注册通用验证短信（系统自带的短信接口不支持这种情况）
     * @throws Throwable
     */
    public function send(): void
    {
        $params   = $this->request->post(['mobile', 'template_code', 'captchaId', 'captchaInfo']);
        $validate = Validate::rule([
            'mobile'        => 'require|mobile',
            'template_code' => 'require',
            'captchaId'     => 'require',
            'captchaInfo'   => 'require'
        ])->message([
            'mobile'        => 'Mobile format error',
            'template_code' => 'Parameter error',
            'captchaId'     => 'Captcha error',
            'captchaInfo'   => 'Captcha error'
        ]);
        if (!$validate->check($params)) {
            $this->error(__($validate->getError()));
        }

        if ($params['template_code'] != 'user_mobile_verify') {
            $this->error('不支持的短信模板');
        }

        // 检查验证码
        $captchaObj   = new Captcha();
        $clickCaptcha = new ClickCaptcha();
        if (!$clickCaptcha->check($params['captchaId'], $params['captchaInfo'])) {
            $this->error(__('Captcha error'));
        }

        // 检查频繁发送
        $captcha = $captchaObj->getCaptchaData($params['mobile'] . $params['template_code']);
        if ($captcha && time() - $captcha['create_time'] < 60) {
            $this->error(__('Frequent SMS sending'));
        }

        // 监听短信模板分析完成
        Event::listen('TemplateAnalysisAfter', function ($templateData) use ($params) {
            // 存储验证码
            if (array_key_exists('code', $templateData['variables'])) {
                (new Captcha())->create($params['mobile'] . $params['template_code'], $templateData['variables']['code']);
            }
            if (array_key_exists('alnum', $templateData['variables'])) {
                (new Captcha())->create($params['mobile'] . $params['template_code'], $templateData['variables']['alnum']);
            }
        });

        try {
            smsLib::send($params['template_code'], $params['mobile']);
        } catch (Throwable $e) {
            if (!env('APP_DEBUG', false)) {
                $this->error(__('Failed to send SMS. Please contact the website administrator'));
            } else {
                // throw new Exception($e->getMessage());
                $this->error(__($e->getMessage()));
            }
        }
        $this->success(__('SMS sent successfully'));
    }
}