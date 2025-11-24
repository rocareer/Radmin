<?php

namespace app\api\controller\cms;

use ba\Date;
use ba\Tree;
use ba\Random;
use Throwable;
use ba\Captcha;
use ParseDownExt;
use think\facade\Db;
use think\facade\Config;
use app\admin\model\cms\Tags;
use modules\cms\library\Helper;
use app\admin\model\cms\PayLog;
use app\admin\model\cms\Content;
use think\exception\ValidateException;
use app\common\controller\Frontend;

class User extends Frontend
{
    protected array $noNeedLogin = ['agreement', 'mobileQuickLogin', 'wechatLogin', 'wechatLoginAgent'];

    protected array $noNeedPermission = ['*'];

    protected $userService;

    public function initialize(): void
    {
        parent::initialize();
        // 初始化userService
        $this->userService = new \support\member\role\user\UserService();
    }

    /**
     * uni-app 会员中心
     */
    public function index(): void
    {
        // 文章数
        $contentCount = Db::name('cms_content')
            ->where('user_id', $this->member->id)
            ->where('status', 'normal')
            ->count();

        // 评论数
        $commentCount = Db::name('cms_comment')
            ->alias('ct')
            ->join('cms_content c', 'ct.content_id=c.id')
            ->where('ct.user_id', $this->member->id)
            ->where('ct.type', 'content')
            ->count();

        $this->success('', [
            'userInfo'     => $this->userService->getUserInfo(),
            'contentCount' => $contentCount,
            'commentCount' => $commentCount,
        ]);
    }

    /**
     * 通过手机号和短信验证码直接完成登录或注册
     */
    public function mobileQuickLogin(): void
    {
        $openMemberCenter = config('buildadmin.open_member_center');
        if (!$openMemberCenter) {
            $this->error(__('Member center disabled'));
        }

        // 检查登录态
        if ($this->member->isLogin()) {
            $this->success(__('You have already logged in. There is no need to log in again~'), [
                'type' => $this->auth::LOGGED_IN
            ], $this->auth::LOGIN_RESPONSE_CODE);
        }

        if ($this->request->isPost()) {
            $params = $this->request->post(['mobile', 'code']);
            try {
                validate([
                    'mobile' => 'require|mobile',
                    'code'   => 'require'
                ])->message([
                    'mobile.require' => '请输入手机号',
                    'mobile.mobile'  => '请输入正确的手机号',
                    'code.require'   => '请输入验证码'
                ])->check($params);
            } catch (ValidateException $e) {
                $this->error($e->getMessage());
            }

            // 检查手机验证码
            $captchaObj = new Captcha();
            if (!$captchaObj->check($params['code'], $params['mobile'] . 'user_mobile_verify')) {
                $this->error(__('Please enter the correct verification code'));
            }

            $userId = Db::name('user')->where('mobile', $params['mobile'])->value('id');

            if ($userId) {
                $res = $this->auth->direct($userId);
            } else {
                $res = $this->auth->register('cms' . $params['mobile'], Random::build(), $params['mobile']);
            }

            if ($res === true) {
                $this->success(__('Login succeeded!'), [
                    'userInfo'  => $this->auth->getUserInfo(),
                    'routePath' => '/user'
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ?: __('Check in failed, please try again or contact the website administrator~');
                $this->error($msg);
            }
        }
    }

    /**
     * 微信登录，服务号 H5
     * CMS 的 uni-app 专用，解决用户授权后回调到 nuxt 站点的问题
     * @throws Throwable
     */
    public function wechatLogin(): \think\response\Redirect|string
    {
        $type  = $this->request->get('type');
        $name  = $this->request->get('name');
        $token = $this->request->get('token', '');

        if (!$type) {
            $this->error(__('Parameter error'));
        }

        $config = config('oauth');
        if (!isset($config[$name]) || !self::checkState($config[$name])) {
            $this->error(__('Unlocked authorized login mode'));
        }

        $captchaObj = new Captcha();

        $config  = $config[$name];
        $state   = Random::uuid();
        $captcha = $captchaObj->create($state);
        $state   = "{$name}__{$state}__{$captcha}__{$type}__$token";
        $url     = '';

        $h5Domain = Db::name('cms_config')->where('name', 'h5_domain')->value('value');

        if ($name == 'wechat_mp') {
            // H5 端回调 URL
            $callBackUrl = $h5Domain . '/#/pages/user/login';

            $OAuth = new \Yurun\OAuthLogin\Weixin\OAuth2($config['app_id'], $config['app_secret'], $callBackUrl);

            // 设置回调代理
            $OAuth->loginAgentUrl = request()->domain() . '/index.php/api/cms.User/wechatLoginAgent';

            $url = $OAuth->getWeixinAuthUrl($callBackUrl, $state);
        }
        if ($url) {
            return redirect($url);
        } else {
            $this->error(__('Failed to generate authorization URL'));
        }
        return '';
    }

    public function wechatLoginAgent(): void
    {
        $wxOAuth = new \Yurun\OAuthLogin\Weixin\OAuth2();
        $wxOAuth->displayLoginAgent();
    }

    /**
     * 用户操作【收藏、点赞、浏览】记录
     */
    public function operateRecord(): void
    {
        if (!$this->auth->check('cms/operateRecord')) {
            $this->error(__('You have no permission'), [], 401);
        }

        $limit   = $this->request->request('limit');
        $type    = $this->request->request('type', 'collect');
        $typeMap = [
            'view'    => 0,
            'like'    => 1,
            'collect' => 2,
        ];

        if (!array_key_exists($type, $typeMap)) {
            $this->error(__('Parameter error'));
        }

        $res = Db::name('cms_statistics')
            ->field('c.id,c.title,c.title_style,c.tags,c.description,c.images,c.publish_time,c.create_time,c.channel_id,c.views,ch.name')
            ->alias('s')
            ->join('cms_content c', 's.content_id=c.id')
            ->join('cms_channel ch', 'c.channel_id=ch.id')
            ->where('s.user_id', $this->auth->id)
            ->where('s.type', $typeMap[$type])
            ->order('s.create_time', 'desc')
            ->paginate($limit)
            ->each(function ($item) {
                $item['publish_time']    = Date::human($item['publish_time'] ?? $item['create_time']);
                $item['title_style']     = Content::getTitleStyleAttr($item['title_style']);
                $item['images']          = Content::getImagesAttr($item['images']);
                $item['cmsTags']['name'] = Tags::whereIn('id', $item['tags'])->column('name', 'id');
                return $item;
            });

        $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

    public function order(): void
    {
        if (!$this->auth->check('cms/order')) {
            $this->error(__('You have no permission'), [], 401);
        }

        $limit = $this->request->request('limit');
        $res   = Db::name('cms_pay_log')
            ->field('p.*, c.id, c.title, c.images, c.description, c.channel_id, c.tags')
            ->alias('p')
            ->join('cms_content c', 'p.object_id=c.id', 'LEFT')
            ->where('p.user_id', $this->auth->id)
            ->where('p.project', 'content')
            ->where('p.pay_time', 'not null')
            ->order('p.pay_time', 'desc')
            ->paginate($limit)
            ->each(function ($item) {
                $item['pay_time']        = Date::human($item['pay_time']);
                $item['amount']          = (float)bcdiv($item['amount'], 100, 2);
                $item['images']          = explode(',', $item['images']);
                $item['cmsTags']['name'] = Tags::whereIn('id', $item['tags'])->column('name', 'id');
                $item['cmsChannel']      = Db::name('cms_channel')->where('id', $item['channel_id'])->find();
                return $item;
            });
        $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

    public function comment(): void
    {
        if (!$this->auth->check('cms/comment')) {
            $this->error(__('You have no permission'), [], 401);
        }

        $commentLanguage = Db::name('cms_config')
            ->where('name', 'comment_language')
            ->value('value');
        $parseDown       = new ParseDownExt();
        $limit           = $this->request->request('limit');
        $res             = Db::name('cms_comment')
            ->alias('ct')
            ->field('ct.*, c.title')
            ->join('cms_content c', 'ct.content_id=c.id')
            ->where('ct.user_id', $this->auth->id)
            ->where('ct.type', 'content')
            ->order('ct.weigh desc,ct.create_time desc')
            ->paginate($limit)
            ->each(function ($item) use ($commentLanguage, $parseDown) {
                $item['content'] = !$item['content'] ? '' : htmlspecialchars_decode($item['content']);
                if ($commentLanguage == 'markdown') {
                    // 解析 markdown，已关闭 ParseDown 的安全模式，但使用 clean_xss 过滤 xss
                    $item['content'] = clean_xss($parseDown->text($item['content']));
                }
                $item['create_time'] = Date::human($item['create_time']);
                return $item;
            });
        $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

    public function content(): void
    {
        if (!$this->auth->check('cms/content')) {
            $this->error(__('You have no permission'), [], 401);
        }

        $limit = $this->request->request('limit');
        $res   = Db::name('cms_content')
            ->alias('c')
            ->field('c.id,c.title,c.title_style,c.images,c.price,c.publish_time,c.create_time,c.channel_id,c.views,c.comments,c.likes,c.status,c.memo,ch.name as channelName')
            ->join('cms_channel ch', 'c.channel_id=ch.id', 'LEFT')
            ->where('user_id', $this->auth->id)
            ->order('create_time desc')
            ->paginate($limit)->each(function ($item) {
                $item['publish_time'] = Date::human($item['publish_time'] ?? $item['create_time']);
                $item['title_style']  = Content::getTitleStyleAttr($item['title_style']);
                $item['sales']        = Db::name('cms_pay_log')
                    ->where('project', 'content')
                    ->where('object_id', $item['id'])
                    ->count();
                $item['images']       = Content::getImagesAttr($item['images']);
                return $item;
            });

        $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

    /**
     * 文章统计信息
     * @throws Throwable
     */
    public function contentStatistics(): void
    {
        if (!$this->auth->check('cms/content')) {
            $this->error(__('You have no permission'), [], 401);
        }

        $id   = $this->request->request('id');
        $info = Content::where('id', $id)
            ->where('user_id', $this->auth->id)
            ->find();

        if (!$info) {
            $this->error('文章数据异常！');
        }

        // 销量
        $info['sales'] = PayLog::where('object_id', $id)
            ->where('project', 'content')
            ->where('pay_time', '>', 0)
            ->count();

        $admireSum = PayLog::where('object_id', $id)
            ->where('project', 'admire')
            ->where('pay_time', '>', 0)
            ->sum('amount');

        $info['admireSum'] = $admireSum > 0 ? (float)bcdiv($admireSum, 100, 2) : 0;

        $this->success('', [
            'info' => $info,
        ]);
    }

    public function delContent(): void
    {
        if (!$this->auth->check('cms/content')) {
            $this->error(__('You have no permission'), [], 401);
        }

        $id = $this->request->request('id');

        $modelInfo = Db::name('cms_content')
            ->alias('co')
            ->field('co.id,ch.id,cm.*')
            ->join('cms_channel ch', 'co.channel_id=ch.id')
            ->join('cms_content_model cm', 'ch.content_model_id=cm.id')
            ->where('co.id', $id)
            ->where('co.user_id', $this->auth->id)
            ->find();
        if (!$modelInfo) {
            $this->error('文章数据异常！');
        }

        Db::name('cms_content')
            ->where('id', $id)
            ->where('user_id', $this->auth->id)
            ->delete();
        Db::name($modelInfo['table'])->where('id', $id)->delete();

        $this->success();
    }

    public function delComment(): void
    {
        if (!$this->auth->check('cms/comment')) {
            $this->error(__('You have no permission'), [], 401);
        }

        $id   = $this->request->request('id');
        $info = Db::name('cms_comment')
            ->where('id', $id)
            ->where('user_id', $this->auth->id)
            ->find();

        if (!$info) {
            $this->error('评论找不到了！');
        }

        $ret = false;
        Db::startTrans();
        try {
            Db::name('cms_content')
                ->where('id', $info['content_id'])
                ->dec('comments')
                ->update();
            $ret = Db::name('cms_comment')->where('id', $id)->delete();
            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        if ($ret) {
            $this->success();
        } else {
            $this->error('没有记录被删除！');
        }
    }

    public function buyLog(): void
    {
        $id    = $this->request->request('id');
        $limit = $this->request->request('limit');
        $res   = Db::name('cms_pay_log')
            ->alias('p')
            ->field('p.*,u.nickname')
            ->join('user u', 'p.user_id=u.id', 'LEFT')
            ->where('p.object_id', $id)
            ->where('p.project', 'content')
            ->where('p.pay_time', 'not null')
            ->order('p.pay_time', 'desc')
            ->paginate($limit)
            ->each(function ($item) {
                $item['pay_time'] = Date::human($item['pay_time']);
                $item['amount']   = (float)bcdiv($item['amount'], 100, 2);
                return $item;
            });
        $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

    private function getContributeChannel(): array
    {
        $channel = Db::name('cms_channel')
            ->where('frontend_contribute', 1)
            ->where('status', 1)
            ->select();
        $tree    = Tree::instance();
        return $tree->assembleTree($tree->getTreeArray($tree->assembleChild($channel->toArray())));
    }

    public function contributeChannel(): void
    {
        $this->success('', [
            'channel' => $this->getContributeChannel()
        ]);
    }

    public function contribute(): void
    {
        $id        = $this->request->param('id');
        $channelId = $this->request->param('channel_id');

        $contentModel = new Content;

        $info = false;
        if ($id) {
            $info      = $contentModel::where('id', $id)
                ->where('user_id', $this->auth->id)
                ->find();
            $channelId = $info->channel_id ?? $channelId;
        }

        if (!$channelId) {
            $this->error('请选择投稿频道！');
        }

        $modelInfo = Db::name('cms_content_model')
            ->alias('m')
            ->field('m.*,ch.frontend_contribute,ch.status as ch_status')
            ->join('cms_channel ch', 'm.id=ch.content_model_id')
            ->where('ch.id', $channelId)
            ->where('m.status', 1)
            ->find();
        if (!$modelInfo) {
            $this->error('频道信息错误，请联系网站管理员！');
        }
        // 已经投递的稿件，不检测频道状态
        if (!$info && $modelInfo['frontend_contribute'] == 0) {
            $this->error('频道投稿已关闭，请重新选择频道！');
        }
        if (!$info && $modelInfo['ch_status'] == 0) {
            $this->error('频道已被禁用，请重新选择频道！');
        }

        $fieldsConfig      = [];
        $attachedFields    = Helper::getTableFields($modelInfo['table']);
        $modelFieldsConfig = Db::name('cms_content_model_field_config')
            ->field('id,name,title,tip,rule,input_extend,extend,type')
            ->where('content_model_id', $modelInfo['id'])
            ->where('frontend_contribute', 1)
            ->where('status', 1)
            ->order(['weigh' => 'asc', 'id' => 'asc'])
            ->select()
            ->toArray();
        foreach ($modelFieldsConfig as $item) {
            if ($item['name'] == 'id') continue;
            $item['main_field'] = true;
            if (array_key_exists($item['name'], $attachedFields)) {
                $item['main_field']   = false;
                $item['extend']       = str_attr_to_array($item['extend']);
                $item['input_extend'] = str_attr_to_array($item['input_extend']);
                $item['default']      = Helper::restoreDefault($attachedFields[$item['name']]['COLUMN_DEFAULT'], $item['type']);
                $item['content']      = Helper::restoreDict($attachedFields[$item['name']]['COLUMN_COMMENT']);
            }
            $fieldsConfig[$item['name']] = $item;
        }

        if ($this->request->isPost()) {
            $this->request->filter('clean_xss');
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }
            $modelTableData = [];
            foreach ($data as $key => $datum) {
                if ($key == 'id') continue;
                if (array_key_exists($key, $attachedFields)) {
                    $modelTableData[$key] = $contentModel::setModelTableData($datum, isset($fieldsConfig[$key]) ? $fieldsConfig[$key]['type'] : '');
                }
            }

            $result = false;
            Db::startTrans();
            try {
                $data['status']  = 'unaudited';
                $data['user_id'] = $this->auth->id;

                // 自动新建标签
                if (isset($data['tags'])) {
                    $data['tags'] = \modules\cms\library\Helper::autoCreateTags($data['tags']);
                    foreach ($data['tags'] as $tag) {
                        Tags::where('id', $tag)->inc('document_count')->save();
                    }
                }

                if ($info) {
                    unset($data['status']);
                    $result = $info->save($data);
                    Db::name($modelInfo['table'])->where('id', $info->id)->update($modelTableData);
                } else {
                    unset($data['id']);
                    $result               = $contentModel::create($data);
                    $modelTableData['id'] = $result->id;
                    Db::name($modelInfo['table'])->insert($modelTableData);
                }
                Db::commit();
            } catch (Throwable $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            if ($result) {
                $this->success('提交成功，正在回到列表...');
            } else {
                $this->error('保存失败，请重试！');
            }
        }

        if ($info) {
            $modelTableData = Db::name($modelInfo['table'])->find($info->id);
            if ($modelTableData) {
                foreach ($modelTableData as $key => $modelTableDatum) {
                    if ($key == 'id') continue;
                    $info->$key = $contentModel::getModelTableData($modelTableDatum, isset($fieldsConfig[$key]) ? $fieldsConfig[$key]['type'] : '');
                }
            }
        }

        $this->success('', [
            'info'    => $info,
            'fields'  => $fieldsConfig,
            'channel' => $this->getContributeChannel(),
        ]);
    }

    public function agreement(): void
    {
        $type = $this->request->param('type');
        if (!in_array($type, ['terms', 'privacy', 'about'])) {
            $this->error(__('Parameter error'));
        }

        $cmsConfigArr  = [];
        $cmsConfigData = Db::name('cms_config')
            ->where('name', 'in', ['agreement_language', $type])
            ->select();
        foreach ($cmsConfigData as $item) {
            $cmsConfigArr[$item['name']] = $item['value'];
        }

        if ($cmsConfigArr['agreement_language'] == 'markdown') {
            $parseDown = new ParseDownExt();
            // 解析 markdown，已关闭 ParseDown 的安全模式，但使用 clean_xss 过滤 xss
            $cmsConfigArr[$type] = clean_xss($parseDown->text($cmsConfigArr[$type]));
        }

        $this->success('', [
            'content' => $cmsConfigArr[$type]
        ]);
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