<?php

namespace app\api\controller;

use ba\Date;
use think\facade\Db;
use Throwable;
use think\facade\Cookie;
use app\common\model\ai\Kbs;
use app\common\controller\Api;
use app\common\model\ai\AiUser;
use app\common\library\ai\Chat;
use app\common\model\ai\Session;
use app\common\library\ai\Helper;
use app\admin\model\UserScoreLog;
use app\admin\model\UserMoneyLog;
use app\common\model\ai\ChatModel;
use app\common\model\ai\KbsContent;
use app\common\model\ai\UserTokens;
use app\common\model\ai\SessionMessage;
use app\common\library\Auth as UserAuth;
use app\admin\library\Auth as AdminAuth;
use app\common\library\ai\AiInterrupter;

class Ai extends Api
{
    /**
     * 权限类实例
     * @var AdminAuth|UserAuth|null
     */
    protected AdminAuth|UserAuth|null $auth = null;

    /**
     * AI会员信息
     * @var mixed
     */
    protected mixed $aiUserInfo;

    public function initialize(): void
    {
        parent::initialize();

        $adminToken = $this->request->server('HTTP_BATOKEN', $this->request->request('batoken', Cookie::get('batoken') ?: false));
        $userToken  = $this->request->server('HTTP_BA_USER_TOKEN', $this->request->request('ba-user-token', Cookie::get('ba-user-token') ?: false));

        $userType = '';
        if ($adminToken) {
            $userType   = 'admin';
            $this->auth = AdminAuth::instance();
            $this->auth->init($adminToken);
        } elseif ($userToken) {
            $userType   = 'user';
            $this->auth = UserAuth::instance();
            $this->auth->init($userToken);
        }
        if (is_null($this->auth) || !$this->auth->isLogin() || !$userType) {
            $this->error(__('Please login first'), [
                'type' => UserAuth::NEED_LOGIN
            ], UserAuth::LOGIN_RESPONSE_CODE);
        }

        $this->aiUserInfo = AiUser::where('user_type', $userType)
            ->where('user_id', $this->auth->id)
            ->where('status', 1)
            ->find();
        if (!$this->aiUserInfo) {
            // 创建AI会员
            $this->aiUserInfo = AiUser::create([
                'user_type' => $userType,
                'user_id'   => $this->auth->id,
            ]);
        }
    }

    /**
     * AI会话初始化
     * @throws Throwable
     */
    public function init()
    {
        $usableModel = $this->getUsableModel();
        if (!$usableModel) {
            $this->error('没有可用模型！');
        }

        // 配置
        $config     = Helper::getConfig();
        $excludeKey = [
            'ai_api_key',
            'ai_api_url',
            'ai_flat_block_size',
            'ai_hnsw_ef_construction',
            'ai_hnsw_epsilon',
            'ai_hnsw_knn_ef_runtime',
            'ai_hnsw_m',
            'ai_initial_cap',
            'ai_vector_index',
            'ai_vector_similarity',
            'ai_work_mode',
            'ai_accurate_hit',
        ];
        foreach ($config as $k => $v) {
            if (in_array($k, $excludeKey)) {
                unset($config[$k]);
            }
        }

        $chatModelAttr = [];
        foreach (Helper::$chatModelAttr as $key => $item) {
            unset($item['api_url']);
            $chatModelAttr[$key] = $item;
        }

        // 知识点更新情况
        $kbsUpdateStatusMessage = '';
        if ($config['ai_send_kbs_update_status']) {
            $today0      = Date::unixTime();
            $today24     = Date::unixTime('day', 0, 'end');
            $createCount = KbsContent::where('create_time', 'BETWEEN', [$today0, $today24])->count();
            $updateCount = KbsContent::where('update_time', 'BETWEEN', [$today0, $today24])->count();
            $updateCount -= $createCount;
            if ($createCount || $updateCount) {
                $kbsUpdateStatusMessage = '知识库今日新增 ' . $createCount . ' 条知识点，优化 ' . $updateCount . ' 条知识点~';
            }
        }

        // 检查可用模型
        if (!array_key_exists($config['ai_model'], $usableModel)) {
            $config['ai_model'] = array_keys($usableModel)[0];
        }

        $sessionList = Helper::getSessionList($this->aiUserInfo->id);

        // 自动创建新会话
        if (!empty($config['auto_new_session_interval']) && !empty($sessionList[0]['last_message_time'])) {
            $timeDiff = time() - $sessionList[0]['last_message_time'];
            if ($timeDiff >= ($config['auto_new_session_interval'] * 60)) {
                Helper::createSession($this->aiUserInfo->id);
                $sessionList = Helper::getSessionList($this->aiUserInfo->id);
            }
        }

        $this->success('', [
            'data'                   => $config,
            'chatModelAttr'          => $chatModelAttr,
            'usableModel'            => $usableModel,
            'promptTokenCount'       => Helper::calcTokens($config['ai_system_prompt'] . $config['ai_prompt']),
            'kbsUpdateStatusMessage' => $kbsUpdateStatusMessage,
            'sessionList'            => $sessionList,
            'aiUserInfo'             => $this->aiUserInfo,
        ]);
    }

    /**
     * AI生成
     */
    public function index(): void
    {
        try {
            $chat = new Chat($this->aiUserInfo->id, true, []);
            $chat->generation();
        } catch (AiInterrupter) {
            // ...
        }
    }

    public function exChange()
    {
        if ($this->aiUserInfo->user_type == 'admin') {
            $this->error('管理员无积分和余额账户，请通过后台管理Tokens');
        }

        $config = Helper::getConfig();

        if ($this->request->isPost()) {
            $type   = $this->request->post('type');
            $amount = $this->request->post('amount', 0);
            if ($type == 'score') {
                $amount = round($amount);
                if ($this->auth->score < $amount) {
                    $this->error('积分不足！');
                }
                $tokens = $amount * $config['ai_score_exchange_tokens'];
            } else {
                $amount = round($amount, 2);
                if ($this->auth->money < $amount) {
                    $this->error('余额不足！');
                }
                $tokens = $amount * $config['ai_money_exchange_tokens'];
            }

            if ($tokens <= 0) {
                $this->error('兑换失败，请稍后重试！');
            }

            Db::startTrans();
            try {
                UserTokens::create([
                    'ai_user_id' => $this->aiUserInfo->id,
                    'tokens'     => intval($tokens),
                    'memo'       => ($type == 'score' ? '积分' : '余额') . '兑换Tokens',
                ]);

                if ($type == 'score') {
                    UserScoreLog::create([
                        'user_id' => $this->aiUserInfo->user_id,
                        'score'   => $amount * (-1),
                        'memo'    => '兑换Tokens',
                    ]);
                } else {
                    UserMoneyLog::create([
                        'user_id' => $this->aiUserInfo->user_id,
                        'money'   => $amount * (-1),
                        'memo'    => '兑换Tokens',
                    ]);
                }

                Db::commit();
            } catch (Throwable $e) {
                Db::rollback();
                $this->error('兑换失败，请稍候重试：' . $e->getMessage());
            }

            $this->success('成功兑换了 ' . $tokens . ' Tokens~');
        }

        $this->success('', [
            'tokens'                   => $this->aiUserInfo->tokens,
            'money'                    => $this->auth->money,
            'score'                    => $this->auth->score,
            'ai_score_exchange_tokens' => $config['ai_score_exchange_tokens'],
            'ai_money_exchange_tokens' => $config['ai_money_exchange_tokens'],
        ]);
    }

    /**
     * AI生成中断后检查
     */
    public function checkStop()
    {
        $chat = new Chat($this->aiUserInfo->id, false, []);
        $chat->checkStopCache();
    }

    /**
     * 计算一个字符串的 token 数（粗略的）
     */
    public function calcTokens()
    {
        $text = $this->request->param('text');
        $this->success('', [
            'count' => Helper::calcTokens($text),
        ]);
    }

    /**
     * 聊天记录
     * @throws Throwable
     */
    public function records()
    {
        $id                = $this->request->get('id');
        $page              = $this->request->get('page', 1);
        $size              = $this->request->get('size', 15);
        $unexpectedRecords = $this->request->get('unexpectedRecords', 0);

        if (!$id) {
            $this->error(__('Parameter error'));
        }

        $session = Session::where('id', $id)->find();
        if (!$session) {
            $this->error('会话找不到啦~');
        }
        // 权限检查
        if ($this->aiUserInfo->user_type != 'admin') {
            if ($session->status == 0) {
                $this->error('会话已失效~');
            }
            if ($session->user_id != $this->aiUserInfo->id) {
                $this->error('会话找不到啦~');
            }
        }

        if ($page == 1) {
            $min = 0;
        } else {
            $min = ($page - 1) * $size;
            $min += $unexpectedRecords;
        }

        $data = SessionMessage::where('session_id', $id)
            ->order('create_time desc,id desc')
            ->limit($min, $size)
            ->select()
            ->toArray();

        if ($this->aiUserInfo->user_type == 'user') {
            foreach ($data as &$datum) {
                unset($datum['kbsTable']);
            }
        }
        $nextPage = count($data) == $size;
        $data     = self::groupByTime($data);

        $this->success('', [
            'messages' => $data,
            'nextPage' => $nextPage,
        ]);
    }

    /**
     * 会话删除、修改标题
     */
    public function sessionOperate()
    {
        $id       = $this->request->post('id');
        $type     = $this->request->post('type');
        $newTitle = $this->request->post('newTitle');
        if (!$type) {
            $this->error(__('Parameter error'));
        }

        if ($type == 'add') {
            $sessionInfo                = Helper::createSession($this->aiUserInfo->id);
            $sessionInfo['create_time'] = Date::human($sessionInfo['create_time']);
            $this->success('', [
                'sessionInfo' => $sessionInfo
            ]);
        }

        if (!$id || ($type == 'changeTitle' && !$newTitle)) {
            $this->error(__('Parameter error'));
        }

        $session = Session::where('id', $id)
            ->where('user_id', $this->aiUserInfo->id)
            ->find();
        if (!$session) {
            $this->error('会话找不到啦~');
        }

        if ($type == 'del') {
            $session->status = 0;
            $session->save();
            $this->success('', [
                'type'        => $type,
                'sessionList' => Helper::getSessionList($this->aiUserInfo->id),
            ]);
        } elseif ($type == 'changeTitle') {
            $session->title = $newTitle;
            $session->save();
            $this->success();
        }
    }

    /**
     * 获取可用模型
     * @throws Throwable
     */
    public function getUsableModel(): array
    {
        $usable        = [];
        $chatModelData = ChatModel::field('id,title,logo,name,greeting,tokens_multiplier')
            ->where('status', 1)
            ->where(function ($query) {
                if ($this->aiUserInfo->user_type != 'admin') {
                    $query->where('user_status', 1);
                }
            })
            ->where('api_key', '<>', '')
            ->order('weigh', 'desc')
            ->select();
        foreach ($chatModelData as $chatModelDatum) {
            $chatModelDatum['optimize_name'] = Helper::$chatModelAttr[$chatModelDatum->name]['name'];
            $usable[$chatModelDatum->name]   = $chatModelDatum;
        }
        return $usable;
    }

    public function getKbs()
    {
        $where       = [];
        $limit       = $this->request->get("limit/d", 10);
        $quickSearch = $this->request->get("quickSearch/s", '');
        $initValue   = $this->request->get("initValue", '');

        // 快速搜索
        if ($quickSearch) {
            $where[] = ['name', "LIKE", '%' . str_replace('%', '\%', $quickSearch) . '%'];
        }
        if ($initValue) {
            $where[] = ['id', 'in', $initValue];
            $limit   = 999999;
        }

        $kbs = Kbs::where('status', 1)
            ->where($where)
            ->order('weigh', 'desc')
            ->paginate($limit);

        $this->success('', [
            'list'  => $kbs->items(),
            'total' => $kbs->total(),
        ]);
    }

    /**
     * 将消息记录按时间进行分组
     * @param array  $message 消息记录
     * @param string $orderBy 排序方式
     * @return array
     */
    public static function groupByTime(array $message, string $orderBy = 'asc'): array
    {
        if (!$message) {
            return [];
        }

        $messageTemp = [];
        $createTime  = $message[0]['create_time'];
        foreach ($message as $value) {

            if ($orderBy == 'asc') {
                $diff = $createTime - $value['create_time'];
            } else {
                $diff = $value['create_time'] - $createTime;
            }

            if ($diff >= 3600) {
                $createTime = $value['create_time'];
            }
            $messageTemp[$createTime][] = $value;
        }
        unset($message);
        foreach ($messageTemp as $key => $value) {
            $message[] = [
                'datetime' => Date::human($key),
                'data'     => $value
            ];
        }
        unset($messageTemp);
        return $message ?? [];
    }
}