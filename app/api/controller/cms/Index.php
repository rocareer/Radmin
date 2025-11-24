<?php

namespace app\api\controller\cms;

use extend\ba\ClickCaptcha;
use extend\ba\Tree;
use extend\ra\DateUtil;
use extend\ra\SystemUtil;
use support\orm\Db;
use support\Response;
use Throwable;
use think\facade\Validate;
use app\admin\model\cms\Tags;
use modules\cms\library\Helper;
use app\admin\model\cms\Channel;
use app\admin\model\cms\Content;
use app\admin\model\cms\SearchLog;
use app\common\controller\Frontend;
use app\admin\model\cms\FriendlyLink;
use app\admin\model\cms\ContentModel;

class Index extends Frontend
{
    /**
     * @var ?Tree
     */
    protected ?Tree $tree = null;

    protected array $noNeedLogin = ['*'];

    public function initialize(): void
    {
        parent::initialize();
        $this->tree = Tree::instance();
    }

    /**
     * CMS初始化接口
     */
    public function init(): Response
    {
        // CMS配置
        $cmsConfigArr  = [];
        $cmsConfigData = Db::name('cms_config')->select();
        foreach ($cmsConfigData as $item) {
            $cmsConfigArr[$item['name']] = $item['value'];
        }

        // 右栏数据配置-s
        $recommendChannel = Db::name('cms_channel')
            ->where('status', 1)
            ->where('index_rec', '<>', '')
            ->order('weigh', 'desc')
            ->select()
            ->toArray();
        foreach ($recommendChannel as $item) {
            $channelIds         = Helper::getChannelChildrenIds($item['id']);
            $channelIds[]       = $item['id'];
            $recommendContent[] = [
                'recTitle' => $item['index_rec'],
                'content'  => Content::where('status', 'normal')
                    ->where('channel_id', 'in', $channelIds)
                    ->where(function ($query) {
                        if (!$this->member) {
                            $query->where('allow_visit_groups', 'all');
                        }
                    })
                    ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
                    ->order('weigh', 'desc')
                    ->select()
                    ->toArray(),
            ];
        }

        // 右栏数据穿插的广告图片
        $rightSideBarAd = Helper::getBlock('right-side-bar');

        // 右栏热门标签
        $hotTags = Db::name('cms_tags')
            ->order([
                'weigh desc',
                'document_count desc',
            ])
            ->limit(30)
            ->select()
            ->toArray();

        // 组合右栏数据
        $rightSideBar = [];
        for ($i = 0; $i < 10; $i++) {
            if (isset($recommendContent[$i])) {
                $rightSideBar[] = [
                    'type'  => 'recommendContent',
                    'data'  => $recommendContent[$i]['content'],
                    'title' => $recommendContent[$i]['recTitle'],
                ];
            }
            if (isset($rightSideBarAd[$i])) {
                $rightSideBar[] = [
                    'type' => 'ad',
                    'data' => $rightSideBarAd[$i],
                ];
            }
            if (!isset($recommendContent[$i]) && !isset($rightSideBarAd[$i])) break;
        }

        array_splice($rightSideBar, 2, 0, [
            [
                'type' => 'tags',
                'data' => $hotTags,
            ]
        ]);

        $cmsConfigArr['right_sidebar'] = $rightSideBar;
        // 右栏数据-e

        // 友情链接
        $cmsConfigArr['friendly_link'] = FriendlyLink::where('status', 'enable')
            ->order('weigh', 'desc')
            ->select();

        return $this->success('', [
            'config' => $cmsConfigArr,
        ]);
    }

    /**
     * 首页
     */
    public function index(): Response
    {
        // 标记最新的文章
        $newContent = Content::where('status', 'normal')
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->where('flag', 'find in set', 'new')
            ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
            ->order('weigh', 'desc')
            ->select()
            ->toArray();
        if (count($newContent) % 2 != 0) {
            array_pop($newContent);
        }
        foreach ($newContent as &$item) {
            $item['create_time'] = DateUtil::human($item['create_time']);
        }

        // 按更新时间排序的文章
        $newPublishContent = Content::where('status', 'normal')
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->where('flag', 'not like', '%new%')
            ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
            ->order('publish_time desc,create_time desc')
            ->limit(16)
            ->select()
            ->toArray();
        if (count($newPublishContent) % 2 != 0) {
            array_pop($newPublishContent);
        }
        foreach ($newPublishContent as &$item) {
            $item['create_time'] = DateUtil::human($item['create_time']);
        }

        // 封面频道前五
        $coverChannel = Db::name('cms_channel')
            ->where('type', 'cover')
            ->where('status', 1)
            ->order('weigh', 'desc')
            ->limit(5)
            ->select();

        return $this->success('', [
            'indexTopBar'       => Helper::getBlock('index-top-bar'),
            'indexCarousel'     => Helper::getBlock('index-carousel'),
            'indexFocus'        => Helper::getBlock('index-focus'),
            'newContent'        => $newContent,
            'newPublishContent' => $newPublishContent,
            'coverChannel'      => $coverChannel,
        ]);
    }

    /**
     * uni-app 初始化接口
     * @throws Throwable
     */
    public function unInit(): Response
    {
        $h5Domain = Db::name('cms_config')->where('name', 'h5_domain')->value('value');
        return $this->success('', [
            'site' => [
                'siteName' => SystemUtil::get_sys_config('site_name'),
                'version'  => SystemUtil::get_sys_config('version'),
                'cdnUrl'   => full_url(),
                'upload'   => keys_to_camel_case(get_upload_config(), ['max_size', 'save_name', 'allowed_suffixes', 'allowed_mime_types']),
                'h5Domain' => $h5Domain,
            ],
        ]);
    }

    /**
     * uni-app 首页接口
     * @throws Throwable
     */
    public function unIndex(): Response
    {
        $recChannelId = 'recommend';
        $page         = $this->request->input('page', 1);
        $channel      = $this->request->input('channel');

        // 加载下一页
        if ($page > 1) {
            return $this->success('', [
                'content' => Helper::getUnIndexContents($this->member, $channel == $recChannelId ? false : $channel),
            ]);
        }

        $channels[] = [
            'channel' => [
                'id'   => $recChannelId,
                'name' => '推荐',
            ],
            'content' => Helper::getUnIndexContents($this->member, false),
        ];

        // 封面频道
        $coverChannel = Db::name('cms_channel')
            ->where('type', 'cover')
            ->where('status', 1)
            ->order('weigh', 'desc')
            ->select();

        foreach ($coverChannel as $item) {
            $channels[] = [
                'channel' => $item,
                'content' => Helper::getUnIndexContents($this->member, $item['id']),
            ];
        }

        return $this->success('', [
            'channels'      => $channels,
            'indexCarousel' => Helper::getBlock('uni-index-carousel'),
        ]);
    }

    /**
     * uni-app 最新资讯接口
     * @throws Throwable
     */
    public function uniNews(): Response
    {
        $limit       = request()->input('limit');
        $newContents = Content::where('status', 'normal')
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->where('flag', 'find in set', 'new')
            ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
            ->order('weigh', 'desc')
            ->paginate($limit);
        return $this->success('', [
            'contents'    => $newContents,
            'newCarousel' => Helper::getBlock('uni-news-carousel'),
        ]);
    }

    /**
     * uni-app 产品接口
     * @throws Throwable
     */
    public function uniProduct(): Response
    {
        $limit = request()->input('limit');

        $productModelId = ContentModel::where('table', 'cms_products')
            ->where('status', 1)
            ->value('id');
        if (!$productModelId) {
            return $this->error('未找到产品模型和频道，请联系管理员！');
        }

        // 所有产品频道
        $channels = Channel::where('content_model_id', $productModelId)
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->where('status', 1)
            ->column('id');

        if (!$channels) {
            return $this->error('未找到产品频道，请联系管理员！');
        }

        $contents = Content::where('status', 'normal')
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->where('channel_id', 'in', $channels)
            ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
            ->order('weigh', 'desc')
            ->paginate($limit);
        return $this->success('', [
            'contents' => $contents,
        ]);
    }

    /**
     * 文章列表、频道首页
     */
    public function articleList(): Response
    {
        $tagId        = $this->request->input('tag');
        $channelId    = $this->request->input('channel');
        $userId       = $this->request->input('user');
        $keywords     = $this->request->input("keywords/s", '');
        $order        = $this->request->input("order/s", '');
        $sort         = $this->request->input("sort/s", '');
        $limit        = $this->request->input("limit/d", 16);
        $filter       = $this->request->input("filter/s", '');
        $template     = $this->request->input("template/s", 'doubleColumnList');
        $breadcrumb   = [];
        $where        = [];
        $title        = [];
        $info         = [];
        $filterConfig = [];

        // 频道
        if ($channelId) {
            $info = Channel::where('id', $channelId)
                ->where('status', 1)
                ->find();
            if ($info) {
                if ($info['allow_visit_groups'] == 'user' && !$this->member) {
                    return $this->error(__('Please login first'), [
                        'type' => Auth::NEED_LOGIN
                    ], Auth::LOGIN_RESPONSE_CODE);
                }
                if ($info['type'] == 'cover') {
                    // 封面频道需要不同的数据
                    $this->coverChannel($channelId, $info);
                }
                $title[]  = $info['name'];
                $template = $template == 'doubleColumnList' ? $info['template'] : $template;

                // 通过对应模型，取得前端筛选数据
                $modelInfo = Db::name('cms_content_model')
                    ->where('id', $info['content_model_id'])
                    ->where('status', 1)
                    ->find();
                if ($modelInfo) {
                    $filterConfig = Db::name('cms_content_model_field_config')
                        ->field('id,name,title,type,frontend_filter_dict')
                        ->where('content_model_id', $modelInfo['id'])
                        ->where('frontend_filter', 1)
                        ->where('status', 1)
                        ->order('weigh desc')
                        ->select()
                        ->toArray();
                    $filter       = json_decode(htmlspecialchars_decode_improve($filter), true);
                    foreach ($filterConfig as &$item) {
                        $item['frontend_filter_dict'] = str_attr_to_array($item['frontend_filter_dict']);
                        if ($filter && array_key_exists($item['name'], $filter) && array_key_exists($filter[$item['name']], $item['frontend_filter_dict'])) {
                            $where[] = [$item['name'], '=', $filter[$item['name']]];
                        }
                    }
                }
            }

            // 同时查询子频道内容
            $channelIds   = Helper::getChannelChildrenIds($channelId);
            $channelIds[] = $channelId;
            $where[]      = ['channel_id', 'in', $channelIds];

            // 面包屑
            $breadcrumb = Helper::getParentChannel($channelId);
            foreach ($breadcrumb as &$item) {
                $item['type'] = 'channel';
            }
            $breadcrumb = array_reverse($breadcrumb);
        }
        // 标签
        if ($tagId) {
            $where[] = ['tags', 'find in set', $tagId];

            $info = Tags::where('id', $tagId)
                ->where('status', 1)
                ->find();
            if ($info) {
                $title[]      = $info['name'];
                $info['type'] = 'tag';
                $breadcrumb[] = $info;
            }
        }
        if ($userId) {
            $where[] = ['user_id', '=', $userId];

            $info = Db::name('user')->field('id,nickname as name,avatar,gender')->where('status', 'enable')->find();
            if ($info) {
                $title[]      = $info['name'];
                $info['type'] = 'user';
                $breadcrumb[] = $info;
            }
        }
        // 关键词
        if ($keywords) {
            $where[]      = ['title|description', 'like', "%{$keywords}%"];
            $title[]      = $keywords;
            $breadcrumb[] = ['name' => $keywords, 'type' => 'search'];

            // 记录关键词
            $searchLog = SearchLog::where('search', $keywords)->find();
            if ($searchLog) {
                $searchLog->inc('count')->save();
            } else {
                SearchLog::create([
                    'user_id' => $this->member ? $this->member->id : 0,
                    'search'  => $keywords,
                    'count'   => 1,
                    'hot'     => 0,
                    'status'  => 0,
                ]);
            }
        }

        // 排序
        if ($order) {
            $order = explode(',', $order);
            if (isset($order[0]) && isset($order[1]) && ($order[1] == 'asc' || $order[1] == 'desc')) {
                $order = [$order[0] => $order[1]];
            }

            if ($sort) {
                $order = [$order[0] => $sort];
            }
        } else {
            $order = ['weigh' => 'desc'];
        }

        $content = Content::where('status', 'normal')
            ->where($where)
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->order($order)
            ->paginate($limit)->each(function ($item) {
                $item->create_time = DateUtil::human($item->create_time);
            });

        // 多个标题，加上双引号
        if (count($title) > 1) {
            foreach ($title as &$item) {
                $item = '“' . $item . '”';
            }
        }

        return $this->success('', [
            'list'         => $content->items(),
            'total'        => $content->total(),
            'info'         => $info,
            'title'        => $title,
            'template'     => $template,
            'breadcrumb'   => $breadcrumb,
            'filterConfig' => $filterConfig,
        ]);
    }

    /**
     * 封面频道
     */
    public function coverChannel($id, $info = []): Response
    {
        if (!$info) {
            $info = Channel::where('id', $id)
                ->where('status', 1)
                ->where(function ($query) {
                    if (!$this->member) {
                        $query->where('allow_visit_groups', 'all');
                    }
                })
                ->find();
        }
        if (!$info) {
            return $this->error('频道不存在');
        }
        $focus    = Helper::getBlock($info['template'] . '-focus');
        $carousel = Helper::getBlock($info['template'] . '-carousel');

        // 获取子栏目数据
        $children = Db::name('cms_channel')
            ->where('pid', $id)
            ->order('weigh', 'desc')
            ->select()
            ->toArray();
        $content  = [];
        foreach ($children as $child) {
            $channelIds   = Helper::getChannelChildrenIds($child['id']);
            $channelIds[] = $child['id'];
            $contentList  = Content::where('status', 'normal')
                ->where('channel_id', 'in', $channelIds)
                ->where(function ($query) {
                    if (!$this->member) {
                        $query->where('allow_visit_groups', 'all');
                    }
                })
                ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
                ->order('weigh', 'desc')
                ->limit(7)
                ->select()
                ->toArray();
            if ($contentList) {
                foreach ($contentList as &$item) {
                    $item['create_time'] = DateUtil::human($item['create_time']);
                }
                $content[] = [
                    'info'        => $child,
                    'contentList' => $contentList,
                ];
            }
        }

        return $this->success('', [
            'info'     => $info,
            'focus'    => $focus,
            'carousel' => $carousel,
            'template' => $info['template'],
            'content'  => $content,
        ]);
    }

    /**
     * 继续加载新发布文章
     */
    public function getNewPublish(): Response
    {
        $page = $this->request->input('page', 2);
        // 按更新时间排序的文章
        $newPublishContent = Content::where('status', 'normal')
            ->where(function ($query) {
                if (!$this->member) {
                    $query->where('allow_visit_groups', 'all');
                }
            })
            ->where('flag', 'not like', '%new%')
            ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
            ->order('publish_time desc,create_time desc')
            ->limit(($page - 1) * 16, 16)
            ->select()
            ->toArray();
        if (count($newPublishContent) % 2 != 0) {
            array_pop($newPublishContent);
        }
        foreach ($newPublishContent as &$item) {
            $item['create_time'] = DateUtil::human($item['create_time']);
        }
        return $this->success('', [
            'newPublishContent' => $newPublishContent,
        ]);
    }

    public function applyFriendlyLink(): Response
    {
        if ($this->request->isPost()) {
            $data     = $this->request->post();
            $validate = Validate::rule([
                'title'       => 'require',
                'link'        => 'require',
                'logo'        => 'require',
                'captchaId'   => 'require',
                'captchaInfo' => 'require'
            ])->message([
                'title'       => 'title require',
                'link'        => 'link require',
                'logo'        => 'logo require',
                'captchaId'   => 'Captcha error',
                'captchaInfo' => 'Captcha error',
            ]);
            if (!$validate->check($data)) {
                return $this->error(__($validate->getError()));
            }

            $clickCaptcha = new ClickCaptcha();
            if (!$clickCaptcha->check($data['captchaId'], $data['captchaInfo'])) {
                return $this->error(__('Captcha error'));
            }

            $data['user_id'] = $this->member->id;
            $data['status']  = 'pending_trial';
            $data['remark']  = $data['remark'] . ($data['contact'] ? ' 联系人：' . $data['contact'] : '');
            FriendlyLink::create($data);
            return $this->success(__('Submission successful, please wait for review'));
        }
        return $this->error();
    }

    /**
     * 搜索页数据（非搜索）
     */
    public function search(): Response
    {
        $hotKeywords = SearchLog::where('hot', 1)
            ->where('status', 1)
            ->order('weigh', 'desc')
            ->select();
        foreach ($hotKeywords as $key => $hotKeyword) {
            $hotKeywords[$key]['rank'] = $key + 1;
        }
        return $this->success('', [
            'hotKeywords' => $hotKeywords
        ]);
    }

    /**
     * 标签下拉
     */
    public function tags(): Response
    {
        $limit       = $this->request->input("limit/d", 10);
        $quickSearch = $this->request->input("quick_search/s", '');
        $initValue   = $this->request->input("initValue/a", '');
        $where       = [];

        // 快速搜索
        if ($quickSearch) {
            $where[] = ['name', 'LIKE', "%{$quickSearch}%"];
        }
        if ($initValue) {
            $where[] = ['id', 'in', $initValue];
            $limit   = 999999;
        }

        $res = Db::name('cms_tags')
            ->where($where)
            ->where('status', 1)
            ->order('id desc')
            ->paginate($limit);

        return $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

    /**
     * 频道下拉
     */
    public function channel(): Response
    {
        $quickSearch = $this->request->input("quick_search/s", '');
        $initValue   = $this->request->input("initValue/a", '');
        $where       = [];

        // 快速搜索
        if ($quickSearch) {
            $where[] = ['name', 'LIKE', "%{$quickSearch}%"];
        }
        if ($initValue) {
            $where[] = ['id', 'in', $initValue];
        }

        $res = Db::name('cms_channel')
            ->where($where)
            ->where('frontend_contribute', 1)
            ->where('status', 1)
            ->order('weigh desc')
            ->select()
            ->toArray();
        $res = $this->tree->assembleTree($this->tree->getTreeArray($this->tree->assembleChild($res)));

        return $this->success('', [
            'list' => $res,
        ]);
    }
}