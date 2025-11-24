<?php

namespace app\api\controller;

use ba\Date;
use Throwable;
use think\db\Query;
use think\facade\Db;
use think\facade\Config;
use app\admin\model\user\Recent;
use app\admin\model\user\Message;
use app\common\controller\Frontend;

/**
 * 会员互动模块接口
 */
class Interaction extends Frontend
{
    protected array $noNeedLogin = ['count', 'userCard', 'userRecentData', 'loadMoreUserRecent'];

    protected array $noNeedPermission = ['*'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 会员名片
     * @throws Throwable
     */
    public function userCard(): void
    {
        $userId = $this->request->get('userId');
        $user   = Db::name('user')
            ->field('id,avatar,gender,birthday,join_time,nickname,motto,create_time')
            ->where('id', $userId)
            ->find();
        if (!$user) {
            $this->error(__("The user can't be found!"));
        }
        $user['join_time'] = Date::human($user['join_time'] ?? $user['create_time']);
        $user['avatar']    = full_url($user['avatar'], true, Config::get('buildadmin.default_avatar'));

        $components    = [];
        $componentData = Db::name('user_card_component')
            ->where('status', '1')
            ->order('weigh desc')
            ->select();
        foreach ($componentData as $componentDatum) {
            $components[$componentDatum['position']][] = $componentDatum;
        }

        if (isset($components['tab'])) {
            foreach ($components['tab'] as &$component) {
                if ($component['component'] == 'recent') {
                    $component['data'] = $this->userRecentData($userId);
                    break;
                }
            }
        }

        $this->success('', [
            'user'            => $user,
            'components'      => $components,
            'officialAccount' => get_sys_config('official_account'),
        ]);
    }

    /**
     * 会员动态分页数据加载
     * @throws Throwable
     */
    public function loadMoreUserRecent(): void
    {
        $userId = $this->request->get('userId');
        $this->success('', [
            'data' => $this->userRecentData($userId),
        ]);
    }

    /**
     * 未读消息数量
     * @throws Throwable
     */
    public function count(): void
    {
        $this->success('', [
            'pollingInterval' => get_sys_config('polling_interval'),
            'count'           => Message::where('recipient_id', $this->auth->id)
                ->where('status', 'unread')
                ->count('id'),
        ]);
    }

    /**
     * 消息列表
     * @throws Throwable
     */
    public function messageList(): void
    {
        $limit         = $this->request->request('limit');
        $keywords      = $this->request->request('keywords');
        $nickname      = $this->request->request('nickname');
        $defaultAvatar = Config::get('buildadmin.default_avatar');

        $userInfoFields = ['id', 'avatar', 'nickname', 'motto'];
        $sessionQuery   = Message::withJoin([
            'user'      => $userInfoFields,
            'recipient' => $userInfoFields,
        ])
            // ROW_NUMBER() 是 MySQL8.0 才引入的窗口函数
            ->fieldRaw('ROW_NUMBER() OVER ( PARTITION BY user_id + recipient_id ORDER BY create_time DESC ) AS rn')
            ->where('user_id|recipient_id', $this->auth->id)
            ->where(function (Query $query) use ($nickname, $keywords) {
                if ($nickname) {
                    $query->whereOr('user.nickname|recipient.nickname', 'like', '%' . str_replace('%', '\%', $nickname) . '%');
                }
                if ($keywords) {
                    $query->where('content', 'like', '%' . str_replace('%', '\%', $keywords) . '%');
                }
            })
            ->where('del_user_id', '<>', $this->auth->id)
            ->order('create_time', 'desc')
            ->buildSql();

        $sessionRecipient = Db::table($sessionQuery . ' rnq')
            ->where('rn', 1)
            ->order('create_time', 'desc')
            ->paginate($limit);

        $existUnread = false;
        $sessions    = $sessionRecipient->getCollection()->toArray();

        foreach ($sessions as $key => $item) {
            if ($item['user__id'] == $this->auth->id) {
                $sessions[$key]['show_user'] = [
                    'id'       => $item['recipient__id'],
                    'avatar'   => full_url($item['recipient__avatar'], true, $defaultAvatar),
                    'nickname' => $item['recipient__nickname'],
                    'motto'    => $item['recipient__motto'],
                ];
                // 自己发出的消息
                $sessions[$key]['status'] = 'read';
            } else {
                $sessions[$key]['show_user'] = [
                    'id'       => $item['user__id'],
                    'avatar'   => full_url($item['user__avatar'], true, $defaultAvatar),
                    'nickname' => $item['user__nickname'],
                    'motto'    => $item['user__motto'],
                ];
                if ($sessions[$key]['status'] == 'unread') {
                    $existUnread = true;
                }
            }

            $sessions[$key]['create_time'] = Date::human($item['create_time']);
        }

        $this->success('', [
            'list'            => $sessions,
            'total'           => $sessionRecipient->total(),
            'officialAccount' => get_sys_config('official_account'),
            'existUnread'     => $existUnread,
        ]);
    }

    /**
     * 对话
     * @throws Throwable
     */
    public function dialog(): void
    {
        $userId = $this->request->request('userId/d');
        $limit  = $this->request->request('limit');

        $userInfo = Db::name('user')
            ->field('id,avatar,nickname')
            ->where('id', $userId)
            ->find();
        if (!$userInfo) {
            $this->error(__("The user can't be found!"));
        }

        $defaultAvatar = Config::get('buildadmin.default_avatar');
        $res           = Message::withJoin(['user', 'recipient'])
            // think-orm 3.0.10 参数绑定失效，以下SQL已确保字符串安全
            ->whereRaw("(user_id={$this->auth->id} and recipient_id=$userId) OR (user_id=$userId and recipient_id={$this->auth->id})")
            ->where('del_user_id', '<>', $this->auth->id)
            ->visible(['user.id', 'user.avatar', 'user.nickname', 'user.motto', 'recipient.avatar', 'recipient.nickname', 'recipient.motto'])
            ->order('create_time', 'desc')
            ->paginate($limit)
            ->each(function ($item) use ($defaultAvatar) {
                if ($item['recipient_id'] == $this->auth->id && $item['status'] == 'unread') {
                    $item->save(['status' => 'read']);
                }
                $item['create_time']         = Date::human($item['create_time']);
                $item['user']['avatar']      = full_url($item['user']['avatar'], true, $defaultAvatar);
                $item['recipient']['avatar'] = full_url($item['recipient']['avatar'], true, $defaultAvatar);
            });
        $this->success('', [
            'list'     => $res->items(),
            'total'    => $res->total(),
            'userInfo' => $userInfo,
        ]);
    }

    /**
     * 发送消息
     * @throws Throwable
     */
    public function sendMessage(): void
    {
        $userId  = $this->request->post('userId');
        $content = $this->request->post('content');
        try {
            Message::create([
                'user_id'      => $this->auth->id,
                'recipient_id' => $userId,
                'content'      => $content,
            ]);
        } catch (Throwable $e) {
            if (env('app_debug', false)) throw $e;
            $this->error(__('Message sending failed, please try again!'));
        }
        $this->success();
    }

    /**
     * 标记消息阅读
     * @throws Throwable
     */
    public function markRead(): void
    {
        $ids = $this->request->post('ids');

        $where   = [];
        $where[] = ['recipient_id', '=', $this->auth->id];
        $where[] = ['status', '=', 'unread'];

        if ($ids != 'all') {
            $where[] = ['id', '=', $ids];
        }

        Message::where($where)->update([
            'status' => 'read'
        ]);

        $this->success('', [
            'count' => Message::where('recipient_id', $this->auth->id)
                ->where('status', 'unread')
                ->count('id'),
        ]);
    }

    /**
     * 删除消息
     * @throws Throwable
     */
    public function delMessage(): void
    {
        $ids = $this->request->post('ids/a');
        Db::startTrans();
        try {
            $messages = Message::where('id', 'in', $ids)
                ->where('user_id|recipient_id', $this->auth->id)
                ->select();
            foreach ($messages as $message) {
                $oppositeId = $message->user_id == $this->auth->id ? $message->recipient_id : $message->user_id;
                if ($message->del_user_id == $oppositeId) {
                    $message->delete();
                } else {
                    $message->del_user_id = $this->auth->id;
                    $message->save();
                }
            }
            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            if (env('app_debug', false)) throw $e;
            $this->error(__('Delete failed, please try again!'));
        }
        $this->success();
    }

    /**
     * 获取会员最近动态数据
     * @throws Throwable
     */
    private function userRecentData($userId): array
    {
        $limit  = $this->request->request('limit', 10);
        $recent = Recent::where('user_id', $userId)
            ->where('status', '1')
            ->order(['weigh' => 'desc', 'create_time' => 'desc'])
            ->paginate($limit)->each(function ($item) {
                $item->create_time = Date::human($item['create_time']);
            });
        return $recent->items();
    }
}