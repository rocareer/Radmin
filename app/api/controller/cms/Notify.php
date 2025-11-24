<?php

namespace app\api\controller\cms;

use modules\cms\library\Helper;
use Throwable;
use ba\PayLib;
use think\facade\Db;
use think\facade\Log;
use Yansongda\Pay\Pay;
use app\admin\model\cms\PayLog;
use app\common\controller\Frontend;
use Psr\Http\Message\ResponseInterface;

class Notify extends Frontend
{
    protected array $noNeedLogin = ['wechat', 'alipay'];

    public function initialize(): void
    {
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 1800');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Origin: *');
        parent::initialize();
    }

    public function wechat(): ResponseInterface
    {
        try {
            Pay::config(PayLib::getConfig());
            $result = Pay::wechat()->callback();
            if ($result['resource_type'] == 'encrypt-resource' && $result['event_type'] == 'TRANSACTION.SUCCESS') {
                $ciphertext = $result['resource']['ciphertext'];
                if ($ciphertext['trade_state'] == 'SUCCESS' && $ciphertext['amount']['currency'] == 'CNY') {
                    $payLog = PayLog::where('sn', $ciphertext['out_trade_no'])->find();
                    if ($payLog && bcmul($payLog->amount, 100) == $ciphertext['amount']['total']) {
                        $this->paySuccess($payLog, $result);
                    } else {
                        Log::write('订单未能找到' . $ciphertext['out_trade_no'] . '，已支付金额：' . $ciphertext['amount']['total'], 'error');
                    }
                } else {
                    Log::write('支付回调异常【' . json_encode($result) . '】', 'error');
                }
            }
        } catch (Throwable $e) {
            Log::write('支付回调异常' . $e->getMessage(), 'error');
        }

        return Pay::wechat()->success();
    }

    /**
     * 支付宝支付回调
     * @throws Throwable
     */
    public function alipay(): ResponseInterface
    {
        try {
            $config = PayLib::getConfig();
            Pay::config($config);
            $result = Pay::alipay()->callback();
            if ($result['trade_status'] == 'TRADE_SUCCESS') {
                $payLog = PayLog::where('sn', $result['out_trade_no'])->find();
                if ($payLog) {
                    if ($config['alipay']['default']['app_id'] == $result['app_id'] && $result['total_amount'] == $payLog->amount) {
                        $this->paySuccess($payLog, $result);
                    }
                } else {
                    Log::write('支付宝订单未能找到' . $result['out_trade_no'] . '，已支付金额：' . $result['total_amount']);
                }
            }
        } catch (Throwable $e) {
            Log::write('支付回调异常' . $e->getMessage());
        }

        return Pay::alipay()->success();
    }

    private function paySuccess($payLog, $result): void
    {
        Db::startTrans();
        try {
            Helper::divideInto($payLog);

            $payLog->pay_time = time();
            $payLog->save();

            Db::commit();
        } catch (Throwable $e) {
            Log::write('支付成功但修改状态失败：' . $e->getMessage() . '【' . json_encode($result) . '】', 'error');
            Db::rollback();
        }
    }
}