<?php

namespace app\api\controller\cms;

use Throwable;
use ba\PayLib;
use ba\Filesystem;
use think\facade\Db;
use app\common\model\User;
use modules\cms\library\Helper;
use app\admin\model\cms\PayLog;
use app\admin\model\UserScoreLog;
use app\admin\model\UserMoneyLog;
use app\common\controller\Frontend;
use app\admin\library\module\Server;

/**
 * 支付
 */
class Pay extends Frontend
{
    protected array $noNeedPermission = ['*'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function create(): void
    {
        $project = $this->request->request('project', 'admire');
        $type    = $this->request->request('type', 'wx');

        $pay = Server::getIni(Filesystem::fsFit(root_path() . 'modules/pay/'));
        if (in_array($type, ['wx', 'alipay', 'uni-wx-mini', 'uni-wx-mp']) && (!$pay || $pay['state'] != 1)) {
            $this->error('请先安装支付模块！');
        }

        // 赞赏
        if ($project == 'admire') {
            $this->admire();
        } elseif ($project == 'content') {
            $this->content();
        } else {
            $this->error('支付项目错误！');
        }
    }

    public function check(): void
    {
        $id = $this->request->get('id');
        if ($id) {
            $log = PayLog::where('id', $id)->find();
            if ($log->pay_time) {
                $this->success();
            } else {
                $this->error();
            }
        }
    }

    /**
     * 内容付费
     */
    public function content(): void
    {
        $objectId = $this->request->request('object');

        /**
         * 支付方式，已实现的支付方式如下
         * wx=PC微信扫描支付
         * uni-wx-mini=uni-app的微信小程序支付
         * uni-wx-mp=uni-app的微信公众号支付
         * alipay=PC支付宝支付
         * balance=全端余额支付
         * score=全端积分支付
         */
        $type = $this->request->request('type', 'wx');

        $contentInfo = Db::name('cms_content')
            ->where('id', $objectId)
            ->where('status', 'normal')
            ->find();
        if (!$contentInfo) {
            $this->error('要购买的内容找不到了~');
        }
        if ((float)$contentInfo['price'] <= 0) {
            $this->error('发起购买失败，请刷新页面重试~');
        }

        if ($contentInfo['currency'] == 'integral' && in_array($type, ['wx', 'alipay', 'balance', 'uni-wx-mini', 'uni-wx-mp'])) {
            $this->error('请使用积分购买内容~');
        } elseif ($contentInfo['currency'] == 'RMB' && $type == 'score') {
            $this->error('请使用人民币购买内容~');
        }

        $title = '购买《' . $contentInfo['title'] . '》';

        Db::startTrans();
        try {
            do {
                $sn = Helper::generateSn();
            } while (Db::name('cms_pay_log')->where('sn', $sn)->value('id'));

            $payLogType = in_array($type, ['wx', 'uni-wx-mini', 'uni-wx-mp']) ? 'wx' : $type;
            $payLog     = PayLog::create([
                'user_id'   => $this->auth->id,
                'object_id' => $objectId,
                'title'     => $title,
                'project'   => 'content',
                'amount'    => $contentInfo['price'],
                'type'      => $payLogType,
                'sn'        => $sn,
            ]);

            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        if ($type == 'wx') {
            $order = [
                'out_trade_no' => $sn,
                'amount'       => [
                    'total' => intval(bcmul($contentInfo['price'], 100)),
                ],
                'description'  => $title,
            ];
            $res   = \Yansongda\Pay\Pay::wechat(PayLib::getConfig())->scan($order);

            if (empty($res->code_url)) {
                $this->error('请求支付失败，请联系管理员！');
            }

            $this->success('请支付', [
                'info' => $payLog,
                'pay'  => $res
            ]);
        } elseif ($type == 'uni-wx-mp') {
            $name     = 'wechat_mp';
            $oauthLog = Db::name('oauth_log')
                ->where('user_id', $this->auth->id)
                ->where('source', $name)
                ->find();
            if (!$oauthLog) {
                $this->error('网页端只支持公众号支付，请使用微信客户端打开网页，并使用微信登录后，再发起支付。');
            }

            $oauthLogExtend = json_decode($oauthLog['extend'], true);
            $order          = [
                'out_trade_no' => $sn,
                'description'  => $title,
                'amount'       => [
                    'total' => intval(bcmul($contentInfo['price'], 100)),
                ],
                'payer'        => [
                    'openid' => $oauthLogExtend['openid'],
                ],
            ];
            try {
                $res = \Yansongda\Pay\Pay::wechat(PayLib::getConfig())->mp($order);
            } catch (Throwable $e) {
                print_r($e->response);
            }

            $this->success('请支付', [
                'info' => $payLog,
                'pay'  => $res ?? []
            ]);
        } elseif ($type == 'uni-wx-mini') {
            $name     = 'wechat_mini_program';
            $oauthLog = Db::name('oauth_log')
                ->where('user_id', $this->auth->id)
                ->where('source', $name)
                ->find();
            if (!$oauthLog) {
                $code   = $this->request->param('code');
                $config = config('oauth');
                if (!isset($config[$name]) || !self::checkState($config[$name])) {
                    $this->error('支付需要 openid，请先安装第三方授权登录模块，完成微信小程序登录配置');
                }

                $config            = $config[$name];
                $OAuth             = new \Yurun\OAuthLogin\Weixin\OAuth2($config['app_id'], $config['app_secret']);
                $OAuth->openidMode = \Yurun\OAuthLogin\Weixin\OpenidMode::OPEN_ID;
                $OAuth->getSessionKey($code);

                $openid = $OAuth->openid;
            } else {
                $oauthLogExtend = json_decode($oauthLog['extend'], true);
                $openid         = $oauthLogExtend['openid'];
            }

            $order = [
                'out_trade_no' => $sn,
                'amount'       => [
                    'total'    => intval(bcmul($contentInfo['price'], 100)),
                    'currency' => 'CNY'
                ],
                'description'  => $title,
                'payer'        => [
                    'openid' => $openid
                ],
            ];

            try {
                $res = \Yansongda\Pay\Pay::wechat(PayLib::getConfig())->mini($order);
            } catch (Throwable $e) {
                print_r($e->response);
            }

            $this->success('请支付', [
                'info' => $payLog,
                'pay'  => $res ?? []
            ]);
        } elseif ($type == 'alipay') {
            $order = [
                'out_trade_no' => $sn,
                'total_amount' => $contentInfo['price'],
                'subject'      => $title,
                'qr_pay_mode'  => '4',
                'qrcode_width' => 220,
            ];
            $res   = \Yansongda\Pay\Pay::alipay(PayLib::getConfig())->web($order);
            $this->success('请支付', [
                'pay'  => [
                    'code_url' => $res->getBody()->getContents(),
                ],
                'info' => $payLog
            ]);
        } elseif ($type == 'balance') {
            $user = $this->auth->getUserInfo();
            if ($user['money'] < $contentInfo['price']) {
                $this->error('您的余额不足~');
            }
            Db::startTrans();
            try {
                UserMoneyLog::create([
                    'user_id' => $this->auth->id,
                    'money'   => $contentInfo['price'] * (-1),
                    'memo'    => $title,
                ]);
                Helper::divideInto($payLog);
                $payLog->pay_time = time();
                $payLog->save();

                Db::commit();
            } catch (Throwable $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('', [
                'info' => $payLog,
            ]);
        } elseif ($type == 'score') {
            $user = $this->auth->getUserInfo();
            if ($user['score'] < $contentInfo['price']) {
                $this->error('您的积分不足~');
            }

            Db::startTrans();
            try {
                UserScoreLog::create([
                    'user_id' => $this->auth->id,
                    'score'   => $contentInfo['price'] * (-1),
                    'memo'    => $title,
                ]);

                if ($payLog->object_id) {
                    // 取得文章作者，作者可能为管理员
                    $contentUserId = Db::name('cms_content')
                        ->where('id', $payLog->object_id)
                        ->value('user_id');
                    if ($contentUserId) {
                        // 确定会员存在，以免 UserScoreLog 抛出异常
                        $user = User::where('id', $contentUserId)->value('id');
                        if ($user) {
                            UserScoreLog::create([
                                'user_id' => $contentUserId,
                                'score'   => intval($contentInfo['price']),
                                'memo'    => $title,
                            ]);
                            $payLog->remark = ($payLog->remark ? $payLog->remark . '；' : '') . $contentInfo['price'] . '积分 已经自动发放至作者账户，会员ID：' . $contentUserId;
                        }
                    }
                }

                $payLog->pay_time = time();
                $payLog->save();

                Db::commit();
            } catch (Throwable $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('', [
                'info' => $payLog,
            ]);
        } else {
            $this->error('未知的支付方法！');
        }
    }

    /**
     * 赞赏内容
     */
    public function admire(): void
    {
        $objectId = $this->request->request('object');
        $amount   = $this->request->request('amount');
        $type     = $this->request->request('type', 'wx');
        $remark   = $this->request->request('remark');

        Db::startTrans();
        try {
            $contentTitle = Db::name('cms_content')
                ->where('id', $objectId)
                ->value('title');

            if (!$contentTitle) {
                $this->error('要赞赏的内容找不到了~');
            }

            do {
                $sn = Helper::generateSn();
            } while (Db::name('cms_pay_log')->where('sn', $sn)->value('id'));

            $title = '赞赏《' . $contentTitle . '》';

            $payLog = PayLog::create([
                'user_id'   => $this->auth->id,
                'object_id' => $objectId,
                'title'     => $title,
                'project'   => 'admire',
                'amount'    => $amount,
                'type'      => $type,
                'sn'        => $sn,
                'remark'    => $remark,
            ]);
            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        if ($type == 'wx') {
            $order = [
                'out_trade_no' => $sn,
                'amount'       => [
                    'total' => intval(bcmul($amount, 100)),
                ],
                'description'  => $title,
            ];
            $res   = \Yansongda\Pay\Pay::wechat(PayLib::getConfig())->scan($order);
            if (empty($res->code_url)) {
                $this->error('请求支付失败，请联系管理员！');
            }

            $this->success('请支付', [
                'info' => $payLog,
                'pay'  => $res
            ]);
        } elseif ($type == 'alipay') {
            $order = [
                'out_trade_no' => $sn,
                'total_amount' => $amount,
                'subject'      => $title,
                'qr_pay_mode'  => '4',
                'qrcode_width' => 220,
            ];
            $res   = \Yansongda\Pay\Pay::alipay(PayLib::getConfig())->web($order);
            $this->success('请支付', [
                'pay'  => [
                    'code_url' => $res->getBody()->getContents(),
                ],
                'info' => $payLog
            ]);
        } elseif ($type == 'balance') {
            $user = $this->auth->getUserInfo();
            if ($user['money'] < $amount) {
                $this->error('您的余额不足~');
            }
            Db::startTrans();
            try {
                UserMoneyLog::create([
                    'user_id' => $this->auth->id,
                    'money'   => $amount * (-1),
                    'memo'    => $title,
                ]);
                Helper::divideInto($payLog);
                $payLog->pay_time = time();
                $payLog->save();
                Db::commit();
            } catch (Throwable $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('赞赏成功~');
        }
    }

    private static function checkState($arr): bool
    {
        if (!is_array($arr)) return false;
        foreach ($arr as $item) {
            if (!$item) return false;
        }
        return true;
    }
}