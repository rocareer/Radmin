<?php


namespace app\api\controller;

use app\common\controller\Api;
use app\exception\TokenException;
use app\exception\TokenExpiredException;
use Exception;
use extend\ba\Captcha;
use extend\ba\ClickCaptcha;
use app\exception\BusinessException;
use support\Response;
use support\token\Token;
use support\StatusCode;
use Throwable;


class Common extends Api
{
    /**
     * 图形验证码
     * @throws Throwable
     */
    public function captcha(): Response
    {
        $captchaId = $this->request->input('id');
        $config    = array(
            'codeSet'  => '123456789',            // 验证码字符集合
            'fontSize' => 22,                     // 验证码字体大小(px)
            'useCurve' => false,                  // 是否画混淆曲线
            'useNoise' => true,                   // 是否添加杂点
            'length'   => 4,                      // 验证码位数
            'bg'       => array(255, 255, 255),   // 背景颜色
        );

        $captcha = new Captcha($config);
        return $captcha->entry($captchaId);
    }

    /**
     * 点选验证码
     */
    public function clickCaptcha(): Response
    {
        $id      = $this->request->input('id');
        $captcha = new ClickCaptcha();
        return $this->success('', $captcha->creat($id));
    }

    /**
     * 点选验证码检查
     * @throws Throwable
     */
    public function checkClickCaptcha(): Response
    {
        $id      = $this->request->post('id');
        $info    = $this->request->post('info');
        $unset   = $this->request->post('unset', false);
        $captcha = new ClickCaptcha();
        if ($captcha->check($id, $info, $unset)) return $this->success();
        return $this->error();
    }

    /**
     * Token刷新接口（优化版本）
     * @throws BusinessException
     */
    public function refreshToken(): Response
    {
        $refreshToken = $this->request->post('refreshToken');
        if (empty($refreshToken)) {
            throw new BusinessException('刷新Token不能为空', StatusCode::NEED_LOGIN, true);
        }

        try {
            // 验证刷新Token
            $payload = Token::verify($refreshToken);
            
            // 检查Token类型
            if ($payload->type !== 'refresh') {
                throw new BusinessException('无效的刷新Token类型', StatusCode::TOKEN_INVALID, true);
            }
            
            // 执行Token刷新
            $newToken = Token::refresh($refreshToken);
            
            // 返回新Token信息
            return $this->success('', [
                'type'           => $payload->role . '-refresh',
                'token'          => $newToken,
                'expiresIn'      => config('token.expire', 7200),
                'tokenType'      => 'Bearer',
                'refreshIn'      => config('token.refresh_expire', 2592000),
            ]);
            
        } catch (TokenExpiredException $e) {
            throw new BusinessException('刷新Token已过期，请重新登录', StatusCode::TOKEN_EXPIRED, true);
        } catch (TokenException $e) {
            throw new BusinessException('Token刷新失败: ' . $e->getMessage(), StatusCode::TOKEN_REFRESH_FAILED, true);
        } catch (Exception $e) {
            throw new BusinessException('系统错误，请稍后重试', StatusCode::SERVER_ERROR, true);
        }
    }
}