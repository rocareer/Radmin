<?php

namespace app\api\controller\cms;

use app\exception\BusinessException;
use extend\ra\DateUtil;
use extend\ra\FileUtil;
use extend\ParseDownExt;
use app\common\model\User;
use app\admin\model\Admin;
use app\admin\model\cms\PayLog;
use modules\cms\library\Helper;
use app\admin\model\cms\Comment;
use app\common\controller\Frontend;
use app\admin\library\module\Server;
use app\admin\model\cms\Content as ContentModel;
use support\orm\Db;
use support\Response;

class Content extends Frontend
{
    protected ?ContentModel $info = null;

    protected array $modelInfo = [];

    protected array $noNeedLogin = ['info'];

    protected array $noNeedPermission = ['*'];

    public function initialize(): void
    {
        parent::initialize();

        $id = $this->request->input('id');
        if (!$id) {
            throw new BusinessException(__('Parameter error'));
        }
        $this->info = ContentModel::where('id', $id)
            ->where('status', 'normal')
            ->find();
        if (!$this->info) {
            throw new BusinessException(__('内容找不到啦！'));
        }

        if (!$this->info->cmsChannel
            || !$this->info->cmsChannel->content_model_id
        ) {
            throw new BusinessException(
                __('内容模型错误，请为内容设置所属频道，并设置频道内容模型！')
            );
        }

        // 附加表数据合入 $this->info
        $this->modelInfo = Db::name('cms_content_model')
            ->where('id', $this->info->cmsChannel->content_model_id)
            ->find();
        if (!$this->modelInfo) {
            throw new BusinessException(
                __('模型找不到啦！')
            );
        }

        $scheduleData      = Db::name($this->modelInfo['table'])
            ->where('id', $this->info->id)
            ->find();
        $fieldsConfig      = [];
        $modelFieldsConfig = Db::name('cms_content_model_field_config')
            ->where('content_model_id', $this->modelInfo['id'])
            ->where('type', '<>', '')
            ->where('status', 1)
            ->select()
            ->toArray();
        foreach ($modelFieldsConfig as $item) {
            $fieldsConfig[$item['name']] = $item;
        }
        foreach ($scheduleData as $key => $scheduleDatum) {
            if (array_key_exists($key, $fieldsConfig)) {
                $this->info->$key = ContentModel::modelDataOutput(
                    $scheduleDatum, $fieldsConfig[$key]['type']
                );
            }
        }
    }

    public function info(): Response
    {
        if ($this->info->allow_visit_groups == 'user' && !$this->member) {
            return $this->error(__('Please login first'), [
                'type' => 'need login'
            ], 303);
        }

        // 阅读量+1
        $this->info->inc('views')->save();

        // 浏览记录
        if ($this->member) {
            $statisticData = [
                'user_id'    => $this->member->id,
                'content_id' => $this->info->id,
                'type'       => '0',
            ];

            // 清理相同文章的浏览记录
            Db::name('cms_statistics')->where($statisticData)->delete();

            // 插入新的记录
            $statisticData['create_time'] = time();
            Db::name('cms_statistics')->insert($statisticData);
        }

        $template      = $this->request->get(
            "template/s", $this->modelInfo['info']
        );
        $defaultAvatar = config('buildadmin.default_avatar');

        // 面包屑
        $breadCrumbs = Helper::getParentChannel($this->info->cmsChannel->id);

        // 内容和评论格式化
        $cmsConfigArr  = [];
        $cmsConfigData = Db::name('cms_config')
            ->where('name', 'in', ['content_language', 'comment_language'])
            ->select();
        foreach ($cmsConfigData as $item) {
            $cmsConfigArr[$item['name']] = $item['value'];
        }

        $parseDown = new ParseDownExt();
        if ($cmsConfigArr['content_language'] == 'markdown') {
            // 解析 markdown，已关闭 ParseDown 的安全模式，但使用 clean_xss 过滤 xss
            $this->info->content = clean_xss(
                $parseDown->text($this->info->content)
            );
        }

        // 上一篇
        $prevArticle = ContentModel::where('status', 'normal')
            ->where('channel_id', $this->info->channel_id)
            ->where('id', '<', $this->info->id)
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->order('weigh', 'desc')
            ->find();

        // 下一篇
        $nextArticle = ContentModel::where('status', 'normal')
            ->where('channel_id', $this->info->channel_id)
            ->where('id', '>', $this->info->id)
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->order('weigh', 'desc')
            ->find();

        // 同频道文章4篇
        $channelIds = [];
        foreach ($breadCrumbs as $breadCrumb) {
            $channelIds[] = $breadCrumb['id'];
        }

        $notInIds = [$this->info->id];
        if ($prevArticle) {
            $notInIds[] = $prevArticle->id;
        }
        if ($nextArticle) {
            $notInIds[] = $nextArticle->id;
        }

        $hotContent = ContentModel::where('status', 'normal')
            ->where('channel_id', 'in', $channelIds)
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->where('id', 'NOT IN', $notInIds)
            ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
            ->order('weigh', 'desc')
            ->limit(4)
            ->select();

        // 作者信息
        $interactionInstalled = false;
        if ($this->info->user_id) {
            $this->info->author = User::field(
                'avatar,gender,nickname,motto,join_time,create_time'
            )->where('id', $this->info->user_id)->find();

            $dynamicCount = 0;
            $interaction  = Server::getIni(
                FileUtil::fsFit(root_path() . 'modules/interaction/')
            );
            if ($interaction && $interaction['state'] == 1) {
                $interactionInstalled = true;
                $dynamicCount         = Db::name('user_recent')
                    ->where('user_id', $this->info->user_id)
                    ->where('status', 1)
                    ->count();
            }

            $this->info->author->statistics = [
                'articleCount' => ContentModel::where(
                    'user_id', $this->info->user_id
                )->where('status', 'normal')->count(),
                'joinTime'     => DateUtil::human(
                    $this->info->author->join_time ??
                    $this->info->author->create_time
                ),
                'dynamicCount' => $dynamicCount,
            ];
        }
        if ($this->info->admin_id) {
            $this->info->author = Admin::field('id,avatar,nickname,motto')
                ->where('id', $this->info->admin_id)->find();
        }

        // 是否已经点赞和收藏
        if ($this->member) {
            $this->info->likeed    = Db::name('cms_statistics')
                ->where('user_id', $this->member->id)
                ->where('content_id', $this->info->id)
                ->where('type', '1')
                ->value('id');
            $this->info->collected = Db::name('cms_statistics')
                ->where('user_id', $this->member->id)
                ->where('content_id', $this->info->id)
                ->where('type', '2')
                ->value('id');
        }

        // 读取文章评论
        $comments = Comment::with(['user'])
            ->where('content_id', $this->info->id)
            ->where('type', 'content')
            ->where('status', 'normal')
            ->order('weigh', 'desc')
            ->order('id', 'desc')
            ->visible(['user.id', 'user.avatar', 'user.nickname'])
            ->paginate()->each(
                function ($item) use ($cmsConfigArr, $parseDown, $defaultAvatar
                ) {
                    if ($cmsConfigArr['comment_language'] == 'markdown') {
                        $item->content = clean_xss(
                            $parseDown->text($item->content)
                        );
                    }
                    $item->user->avatar = full_url(
                        htmlspecialchars_decode($item->user->avatar), true,
                        $defaultAvatar
                    );
                    $item->create_time  = DateUtil::human($item->create_time);
                }
            );

        if ((float)$this->info->price > 0) {
            // 查询订单
            $payId = PayLog::where('object_id', $this->info->id)
                ->where('user_id', $this->member->id)
                ->where('project', 'content')
                ->where('pay_time', '>', 0)
                ->value('id');
            if ($payId) {
                $this->info->price = 0;
            } else {
                unset($this->info->content, $this->info->downloads);
            }
        }

        if ($template == 'download') {
            $this->info->commented = true;
            if (isset($this->info->download_after_comment)
                && $this->info->download_after_comment == 'enable'
            ) {
                // 检查当前用户是否有评论
                $this->info->commented = Comment::where(
                    'content_id', $this->info->id
                )
                    ->where('user_id', $this->member->id)
                    ->where('type', 'content')
                    ->value('id');
                if (!$this->info->commented) {
                    unset($this->info->downloads);
                }
            }
        }

        // 计算审核通过的评论量
        $this->info->comments = Comment::where('content_id', $this->info->id)
            ->where('status', 'normal')
            ->where('type', 'content')
            ->count();

        // 收藏量
        $this->info->collects = Db::name('cms_statistics')
            ->where('content_id', $this->info->id)
            ->where('type', '2')
            ->count();

        return $this->success('', [
            'template'             => $template,
            'content'              => $this->info,
            'breadCrumbs'          => array_reverse($breadCrumbs),
            'hotContent'           => $hotContent,
            'prevArticle'          => $prevArticle,
            'nextArticle'          => $nextArticle,
            'comments'             => $comments,
            'interactionInstalled' => $interactionInstalled,
        ]);
    }

    public function loadComments(): Response
    {
        $defaultAvatar   = config('buildadmin.default_avatar');
        $commentLanguage = Db::name('cms_config')
            ->where('name', 'comment_language')
            ->value('value');
        $parseDown       = new ParseDownExt();
        $comments        = Comment::with(['user'])
            ->where('content_id', $this->info->id)
            ->where('type', 'content')
            ->where('status', 'normal')
            ->order('weigh', 'desc')
            ->order('id', 'desc')
            ->visible(['user.avatar', 'user.nickname'])
            ->paginate()->each(
                function ($item) use (
                    $commentLanguage, $parseDown, $defaultAvatar
                ) {
                    if ($commentLanguage == 'markdown') {
                        // 解析 markdown，已关闭 ParseDown 的安全模式，但使用 clean_xss 过滤 xss
                        $item->content = clean_xss(
                            $parseDown->text($item->content)
                        );
                    }
                    $item->user->avatar = full_url(
                        htmlspecialchars_decode($item->user->avatar), true,
                        $defaultAvatar
                    );
                    $item->create_time  = DateUtil::human($item->create_time);
                }
            );
        return $this->success('', $comments);
    }

    public function like(): Response
    {
        $statistics = Db::name('cms_statistics')
            ->where('user_id', $this->member->id)
            ->where('content_id', $this->info->id)
            ->where('type', '1')
            ->find();
        if ($statistics) {
            return $this->error(__('您已经点过赞啦！'));
        } else {
            Db::name('cms_statistics')->insert([
                'user_id'     => $this->member->id,
                'content_id'  => $this->info->id,
                'type'        => '1',
                'create_time' => time(),
            ]);
            $this->info->inc('likes')->save();
            return $this->success(__('点赞成功！'));
        }
    }

    public function collect(): Response
    {
        $statistics = Db::name('cms_statistics')
            ->where('user_id', $this->member->id)
            ->where('content_id', $this->info->id)
            ->where('type', '2')
            ->find();
        if ($statistics) {
            // 取消收藏
            Db::name('cms_statistics')
                ->where('user_id', $this->member->id)
                ->where('content_id', $this->info->id)
                ->where('type', '2')
                ->delete();
            return $this->success(__('取消收藏成功！'), [
                'collected' => false,
            ]);
        } else {
            // 收藏
            Db::name('cms_statistics')->insert([
                'user_id'     => $this->member->id,
                'content_id'  => $this->info->id,
                'type'        => '2',
                'create_time' => time(),
            ]);
            return $this->success(__('收藏成功！'), [
                'collected' => true,
            ]);
        }
    }

    public function comment(): Response
    {
        $atUser  = $this->request->post('atUser/a', []);
        $content = $this->request->post('content');
        if (!$content) {
            return $this->error(__('评论内容不能为空！'));
        }

        $commentsReview   = Db::name('cms_config')
            ->where('name', 'comments_review')
            ->value('value');
        $commentsInterval = Db::name('cms_config')
            ->where('name', 'comments_interval')
            ->value('value');

        $lastCommentTime = Comment::where('user_id', $this->member->id)
            ->where('type', 'content')
            ->order('create_time desc')
            ->value('create_time');
        if ($lastCommentTime && $commentsInterval) {
            $diff = time() - $lastCommentTime;
            if ($diff <= $commentsInterval) {
                return $this->error('频繁发表评论，请稍后再试~');
            }
        }

        $comment = Comment::create([
            'user_id'    => $this->member->id,
            'content_id' => $this->info->id,
            'type'       => 'content',
            'content'    => $content,
            'at_user'    => $atUser,
            'status'     => $commentsReview == 'yes' ? 'unaudited' : 'normal',
        ]);
        $this->info->comments++;
        $this->info->save();

        $interaction = Server::getIni(
            FileUtil::fsFit(root_path() . 'modules/interaction/')
        );

        if ($commentsReview == 'no' && $interaction
            && $interaction['state'] == 1
        ) {
            $commentLanguage = Db::name('cms_config')
                ->where('name', 'comment_language')
                ->value('value');
            if ($commentLanguage == 'markdown') {
                // 解析 markdown，已关闭 ParseDown 的安全模式，但使用 clean_xss 过滤 xss
                $parseDown        = new ParseDownExt();
                $comment->content = clean_xss(
                    $parseDown->text($comment->content)
                );
            }

            // at了用户，向对应用户发送消息
            if ($atUser) {
                foreach ($atUser as $userId) {
                    if ($userId == $this->member->id) {
                        continue;
                    }
                    $messageHtml = '我在 <a href="/cms/info/' . $this->info->id
                        . '">' . $this->info->title
                        . '</a> 的评论中@了你<br />';
                    $messageHtml .= '<div style="margin-top: 10px;padding: 10px;border-left:4px solid #dedfe0;">'
                        . $comment->content . '</div>';
                    Db::name('user_message')
                        ->insert([
                            'user_id'      => $this->member->id,
                            'recipient_id' => $userId,
                            'content'      => $messageHtml,
                            'create_time'  => time()
                        ]);
                }
            }

            // 用户动态
            $recentHtml = '在 <a href="/cms/info/' . $this->info->id . '">'
                . $this->info->title . '</a> 发表了评论<br />';
            $recentHtml .= '<div style="margin-top: 10px;padding: 10px;border-left:4px solid #dedfe0;">'
                . $comment->content . '</div>';
            \app\admin\model\user\Recent::create([
                'user_id' => $this->member->id,
                'content' => $recentHtml,
            ]);
        }

        return $this->success(
            __(
                '评论成功' . ($commentsReview == 'yes' ? '，审核成功后展示' : '')
                . '！'
            ), [
                'comment'        => $comment,
                'commentsReview' => $commentsReview
            ]
        );
    }
}