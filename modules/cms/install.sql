CREATE TABLE IF NOT EXISTS `__PREFIX__cms_block`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `platform` enum('nuxt','uniapp') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nuxt' COMMENT '平台:uniapp=uni-app,nuxt=Nuxt',
    `type` enum('carousel','image','rich_text') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image' COMMENT '类型:carousel=轮播图,image=图片,rich_text=富文本',
    `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
    `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `image` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
    `link` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '链接',
    `target` enum('_blank','_self','_top','_parent', 'navigateTo', 'redirectTo', 'reLaunch', 'switchTab') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_blank' COMMENT '跳转方式:_blank=新标签页,_self=当前标签页,_top=当前窗体,_parent=父窗口,navigateTo=保留当前页面跳转,redirectTo=关闭当前页面跳转,reLaunch=关闭所有页面跳转,switchTab=关闭所有 tabBar 页面跳转',
    `rich_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '富文本',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `start_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '开始时间',
    `end_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '结束时间',
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'CMS区块管理';

BEGIN;
INSERT INTO `__PREFIX__cms_block` VALUES (1, 'nuxt', 'carousel', 'index-carousel', '首页轮播图片一', '', '', '_blank', '', 1, NULL, NULL, 1, 1685343654, 1680939362);
INSERT INTO `__PREFIX__cms_block` VALUES (2, 'nuxt', 'carousel', 'index-carousel', '首页轮播图二', '', '', '_blank', '', 2, NULL, NULL, 1, 1685161569, 1680939436);
INSERT INTO `__PREFIX__cms_block` VALUES (3, 'nuxt', 'carousel', 'index-carousel', '首页轮播图三', '', '', '_blank', '', 3, NULL, NULL, 1, 1685161577, 1680939460);
INSERT INTO `__PREFIX__cms_block` VALUES (4, 'nuxt', 'image', 'index-focus', '首页轮播旁的焦点图一', '', '', '_blank', '', 4, NULL, NULL, 1, 1685161651, 1680939537);
INSERT INTO `__PREFIX__cms_block` VALUES (5, 'nuxt', 'image', 'index-focus', '首页轮播旁的焦点图二', '', '', '_blank', '', 5, NULL, NULL, 1, 1685161656, 1680939563);
INSERT INTO `__PREFIX__cms_block` VALUES (6, 'nuxt', 'image', 'index-top-bar', '首页顶部通栏', '', '', '_blank', '', 6, NULL, NULL, 1, 1685623394, 1680939664);
INSERT INTO `__PREFIX__cms_block` VALUES (7, 'nuxt', 'image', 'right-side-bar', '右侧图片广告位一', '', '', '_blank', '', 7, NULL, NULL, 1, 1685161642, 1680939810);
INSERT INTO `__PREFIX__cms_block` VALUES (8, 'nuxt', 'image', 'right-side-bar', '右侧图片广告位二', '', '', '_blank', '', 8, NULL, NULL, 1, 1685161637, 1680939833);
INSERT INTO `__PREFIX__cms_block` VALUES (9, 'nuxt', 'carousel', 'news-carousel', '新闻中心轮播图一', '', '', '_blank', NULL, 9, NULL, NULL, 1, 1685643319, 1685643319);
INSERT INTO `__PREFIX__cms_block` VALUES (10, 'nuxt', 'image', 'news-carousel', '新闻中心轮播图二', '', '', '_blank', NULL, 10, NULL, NULL, 1, 1685643351, 1685643351);
INSERT INTO `__PREFIX__cms_block` VALUES (11, 'nuxt', 'image', 'news-focus', '新闻中心焦点图一', '', '', '_blank', NULL, 11, NULL, NULL, 1, 1685691496, 1685691496);
INSERT INTO `__PREFIX__cms_block` VALUES (12, 'nuxt', 'image', 'news-focus', '新闻中心焦点图二', '', '', '_blank', NULL, 12, NULL, NULL, 1, 1685691515, 1685691515);
INSERT INTO `__PREFIX__cms_block` VALUES (13, 'nuxt', 'image', 'news-focus', '新闻中心焦点图三', '', '', '_blank', NULL, 13, NULL, NULL, 1, 1685691540, 1685691540);
INSERT INTO `__PREFIX__cms_block` VALUES (14, 'nuxt', 'image', 'news-focus', '新闻中心焦点图四', '', '', '_blank', NULL, 14, NULL, NULL, 1, 1685691556, 1685691556);
INSERT INTO `__PREFIX__cms_block` VALUES (15, 'nuxt', 'image', 'products-focus', '产品中心焦点图一', '', '', '_blank', NULL, 15, NULL, NULL, 1, 1685780555, 1685780555);
INSERT INTO `__PREFIX__cms_block` VALUES (16, 'nuxt', 'image', 'products-focus', '产品中心焦点图二', '', '', '_blank', NULL, 16, NULL, NULL, 1, 1685780577, 1685780577);
INSERT INTO `__PREFIX__cms_block` VALUES (17, 'nuxt', 'image', 'products-focus', '产品中心焦点图三', '', '', '_blank', NULL, 17, NULL, NULL, 1, 1685780601, 1685780601);
INSERT INTO `__PREFIX__cms_block` VALUES (18, 'nuxt', 'image', 'products-focus', '产品中心焦点图四', '', '', '_blank', NULL, 18, NULL, NULL, 1, 1685780621, 1685780621);
INSERT INTO `__PREFIX__cms_block` VALUES (19, 'nuxt', 'carousel', 'products-carousel', '产品中心轮播图一', '', '', '_blank', NULL, 19, NULL, NULL, 1, 1685780671, 1685780671);
INSERT INTO `__PREFIX__cms_block` VALUES (20, 'nuxt', 'carousel', 'products-carousel', '产品中心轮播图二', '', '', '_blank', NULL, 20, NULL, NULL, 1, 1685780691, 1685780691);
INSERT INTO `__PREFIX__cms_block` VALUES (21, 'uniapp', 'carousel', 'uni-index-carousel', 'uni-app 首页轮播 1', '', '/pages/user/user', 'switchTab', '', 21, NULL, NULL, 1, 1743345860, 1743345860);
INSERT INTO `__PREFIX__cms_block` VALUES (22, 'uniapp', 'carousel', 'uni-index-carousel', 'uni-app 首页轮播图 2', '', '/pages/user/user', 'switchTab', '', 22, NULL, NULL, 1, 1743345898, 1743345898);
INSERT INTO `__PREFIX__cms_block` VALUES (23, 'uniapp', 'carousel', 'uni-news-carousel', 'uni-app 最新资讯轮播 1', '', '/pages/user/user', 'switchTab', '', 23, NULL, NULL, 1, 1743345860, 1743345860);
INSERT INTO `__PREFIX__cms_block` VALUES (24, 'uniapp', 'carousel', 'uni-news-carousel', 'uni-app 最新资讯轮播图 2', '', '/pages/user/user', 'switchTab', '', 24, NULL, NULL, 1, 1743345898, 1743345898);
COMMIT;


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_channel`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级频道',
    `type` enum('cover','list','link') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'list' COMMENT '类型:cover=封面频道,list=列表频道,link=跳转链接',
    `content_model_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '内容模型',
    `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '频道名称',
    `template` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '首页模板',
    `url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '跳转链接',
    `target` enum('_blank','_self','_top','_parent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self' COMMENT '跳转方式:_blank=新标签页,_self=当前标签页,_top=当前窗体,_parent=父窗口',
    `seotitle` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SEO标题',
    `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
    `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
    `allow_visit_groups` enum('all','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all' COMMENT '访问策略:all=全部,user=仅会员',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `frontend_contribute` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '允许投稿:0=关,1=开',
    `index_rec` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '推荐到首页',
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'CMS频道管理';

BEGIN;
INSERT INTO `__PREFIX__cms_channel` VALUES (1, 0, 'cover', 1, '新闻中心', 'news', '', '_self', '新闻中心-SEO标题', '新闻中心-SEO关键词', '新闻中心-SEO描述', 'all', 9, 1, '', 1, 1685610430, 1685164593);
INSERT INTO `__PREFIX__cms_channel` VALUES (2, 1, 'list', 1, '人工智能', 'doubleColumnList', '', '_self', '人工智能-SEO标题', '人工智能-SEO关键词', '人工智能-SEO描述', 'all', 9, 1, '推荐资讯', 1, 1685809723, 1685165000);
INSERT INTO `__PREFIX__cms_channel` VALUES (3, 1, 'list', 1, '开源安全', 'doubleColumnList', '', '_self', '开源安全-SEO标题', '开源安全-SEO关键词', '开源安全-SEO描述', 'all', 3, 1, '', 1, 1685809732, 1685164970);
INSERT INTO `__PREFIX__cms_channel` VALUES (4, 1, 'list', 1, '软件更新', 'doubleColumnList', '', '_self', '软件更新-SEO标题', '软件更新-SEO关键词', '软件更新-SEO描述', 'all', 4, 1, '', 1, 1685809729, 1685164934);
INSERT INTO `__PREFIX__cms_channel` VALUES (5, 1, 'list', 1, '官方动态', 'doubleColumnList', '', '_self', '官方动态-SEO标题', '官方动态-SEO关键词', '官方动态-SEO描述', 'all', 5, 1, '', 1, 1685809726, 1685164842);
INSERT INTO `__PREFIX__cms_channel` VALUES (6, 0, 'cover', 2, '产品中心', 'products', '', '_self', '产品中心-SEO标题', '产品中心-SEO关键词', '产品中心-SEO描述', 'all', 6, 1, '推荐产品', 1, 1685610536, 1685166492);
INSERT INTO `__PREFIX__cms_channel` VALUES (7, 6, 'list', 2, '出行穿戴', 'bigPicList', '', '_self', '出行穿戴-SEO标题', '出行穿戴-SEO关键词', '出行穿戴-SEO描述', 'all', 7, 1, '', 1, 1685809764, 1685166540);
INSERT INTO `__PREFIX__cms_channel` VALUES (8, 6, 'list', 2, '生活箱包', 'bigPicList', '', '_self', '生活箱包-SEO标题', '生活箱包-SEO关键词', '生活箱包-SEO描述', 'all', 8, 1, '', 1, 1685809760, 1685166575);
INSERT INTO `__PREFIX__cms_channel` VALUES (9, 6, 'list', 2, '智能硬件', 'bigPicList', '', '_self', '智能硬件-SEO标题', '智能硬件-SEO关键词', '智能硬件-SEO描述', 'all', 9, 1, '', 1, 1685809756, 1685166615);
INSERT INTO `__PREFIX__cms_channel` VALUES (10, 0, 'cover', 3, '下载中心', 'products', '', '_self', '下载中心-SEO标题', '下载中心-SEO关键词', '下载中心-SEO描述', 'all', 10, 1, '', 1, 1685809311, 1685166909);
INSERT INTO `__PREFIX__cms_channel` VALUES (11, 10, 'list', 3, '编程开发', 'bigPicList', '', '_self', '编程开发-SEO标题', '编程开发-SEO关键词', '编程开发-SEO描述', 'all', 11, 1, '', 1, 1685809769, 1685167375);
INSERT INTO `__PREFIX__cms_channel` VALUES (12, 10, 'list', 3, '视频图片', 'bigPicList', '', '_self', '视频图片-SEO标题', '视频图片-SEO关键词', '视频图片-SEO描述', 'all', 12, 1, '', 1, 1685809772, 1685167421);
INSERT INTO `__PREFIX__cms_channel` VALUES (13, 10, 'list', 3, '系统工具', 'bigPicList', '', '_self', '系统工具-SEO标题', '系统工具-SEO关键词', '系统工具-SEO描述', 'user', 13, 1, '', 1, 1686317629, 1685167449);
COMMIT;


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_comment`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员',
    `content_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论对象',
    `type` enum('content','page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'content' COMMENT '类型:content=内容评论,page=单页评论',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '富文本',
    `at_user` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'AT用户列表',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `status` enum('normal','unaudited','refused') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,unaudited=待审核,refused=已拒绝',
    `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'CMS评论管理';


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_config`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '变量名',
    `group` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '分组',
    `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '变量标题',
    `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '类型',
    `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '变量值',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'CMS配置';

BEGIN;
INSERT INTO `__PREFIX__cms_config` VALUES (1, 'index_seo_title', 'base', '首页SEO标题', 'string', 'CMS首页标题');
INSERT INTO `__PREFIX__cms_config` VALUES (2, 'index_seo_keywords', 'base', '首页SEO关键词', 'string', 'CMS首页-SEO关键词');
INSERT INTO `__PREFIX__cms_config` VALUES (3, 'index_seo_description', 'base', '首页SEO描述', 'string', 'CMS首页-SEO描述');
INSERT INTO `__PREFIX__cms_config` VALUES (4, 'content_language', 'base', '内容标记语言', 'radio', 'html');
INSERT INTO `__PREFIX__cms_config` VALUES (5, 'comment_language', 'base', '评论标记语言', 'radio', 'html');
INSERT INTO `__PREFIX__cms_config` VALUES (6, 'appreciation', 'base', '用户赞赏内容', 'radio', 'enable');
INSERT INTO `__PREFIX__cms_config` VALUES (7, 'appreciation_ratio', 'base', '用户赞赏分成比例', 'number', '100');
INSERT INTO `__PREFIX__cms_config` VALUES (8, 'buy_ratio', 'base', '用户购买分成比例', 'number', '70');
INSERT INTO `__PREFIX__cms_config` VALUES (9, 'wechat_payment_commission', 'base', '微信收款手续费', 'number', '0.54');
INSERT INTO `__PREFIX__cms_config` VALUES (10, 'comments_review', 'base', '评论需要审核', 'radio', 'no');
INSERT INTO `__PREFIX__cms_config` VALUES (11, 'comments_interval', 'base', '发布评论间隔', 'number', '10');
INSERT INTO `__PREFIX__cms_config` VALUES (12, 'agreement_language', 'uni', '协议内容标记语言', 'string', 'html');
INSERT INTO `__PREFIX__cms_config` VALUES (13, 'terms', 'uni', '服务条款', 'string', '服务条款');
INSERT INTO `__PREFIX__cms_config` VALUES (14, 'privacy', 'uni', '隐私政策', 'string', '隐私政策');
INSERT INTO `__PREFIX__cms_config` VALUES (15, 'about', 'uni', '关于我们', 'string', '关于我们');
INSERT INTO `__PREFIX__cms_config` VALUES (16, 'h5_domain', 'uni', 'H5 站点部署域名', 'string', '');
COMMIT;


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '投稿会员',
    `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布管理员',
    `channel_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '频道',
    `channel_ids` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '副频道',
    `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `title_style` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题样式',
    `flag` set('top','hot','recommend','new') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new' COMMENT '标志:top=置顶,hot=热门,recommend=推荐,new=最新',
    `images` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图片',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
    `seotitle` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SEO标题',
    `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
    `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
    `tags` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签',
    `url` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部链接',
    `target` enum('_blank','_self','_top','_parent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_blank' COMMENT '跳转方式:_blank=新标签页,_self=当前标签页,_top=当前窗体,_parent=父窗口',
    `views` int(10) NOT NULL DEFAULT 0 COMMENT '浏览量',
    `comments` int(10) NOT NULL DEFAULT 0 COMMENT '评论量',
    `likes` int(10) NOT NULL DEFAULT 0 COMMENT '点赞数',
    `dislikes` int(10) NOT NULL DEFAULT 0 COMMENT '点踩数',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `price` decimal(7, 2) NOT NULL DEFAULT 0.00 COMMENT '价格',
    `currency` enum('RMB','integral') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RMB' COMMENT '货币:RMB=人民币,integral=积分',
    `allow_visit_groups` enum('all','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all' COMMENT '允许访问:all=全部,user=仅会员',
    `allow_comment_groups` enum('disable','all','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user' COMMENT '评论:disable=禁止,all=全部,user=仅会员',
    `status` enum('normal','unaudited','refused','offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态:normal=正常,unaudited=待审核,refused=已拒绝,offline=已下线',
    `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '拒绝稿件原因',
    `publish_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '发布时间',
    `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'CMS内容管理';


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_model`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模型名称',
    `table` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表名',
    `info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '详情页模板',
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容模型管理';

BEGIN;
INSERT INTO `__PREFIX__cms_content_model` VALUES (1, '新闻', 'cms_news', 'default', 1, 1685266471, 1685163618);
INSERT INTO `__PREFIX__cms_content_model` VALUES (2, '产品', 'cms_products', 'carousel', 1, 1686302342, 1685166277);
INSERT INTO `__PREFIX__cms_content_model` VALUES (3, '下载', 'cms_downloads', 'download', 1, 1686210980, 1685166772);
COMMIT;


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_content_model_field_config`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `content_model_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '内容模型ID',
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
    `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
    `tip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提示信息',
    `rule` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证规则',
    `extend` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'formitem 扩展信息',
    `input_extend` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'input 扩展信息',
    `frontend_filter` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '前台筛选:0=否,1=是',
    `frontend_filter_dict` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '前台筛选字典',
    `frontend_contribute` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '前台投稿:0=否,1=是',
    `backend_show` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '表格显示:0=关,1=开',
    `backend_com_search` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '公共搜索:0=否,1=是',
    `backend_sort` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序:0=否,1=是',
    `backend_publish` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '发布可用:0=否,1=是',
    `backend_column_attr` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表格列属性',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容模型字段管理';

BEGIN;
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (1, 1, 'ID', 'id', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 1, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (2, 1, '投稿会员', 'user_id', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 2, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (3, 1, '发布管理员', 'admin_id', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 3, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (4, 1, '频道', 'channel_id', '', '', '', '', '', 0, '', 1, 1, 1, 0, 1, '', 4, 1, 1686503908, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (5, 1, '副频道', 'channel_ids', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 5, 1, 1686771864, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (6, 1, '标题', 'title', '', '', '', '', '', 0, '', 1, 1, 1, 0, 1, '', 6, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (7, 1, '标题样式', 'title_style', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 7, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (8, 1, '标志', 'flag', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 8, 1, 1686771859, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (9, 1, '封面图片', 'images', '', '', '', '', '', 0, '', 1, 1, 0, 0, 1, '', 9, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (10, 1, '内容', 'content', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 10, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (11, 1, 'SEO标题', 'seotitle', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 11, 1, 1686771856, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (12, 1, '关键词', 'keywords', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 12, 1, 1686771853, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (13, 1, '描述', 'description', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 13, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (14, 1, '标签', 'tags', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 14, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (15, 1, '外部链接', 'url', '', '', '', '', '', 0, '', 0, 0, 1, 0, 0, '', 15, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (16, 1, '跳转方式', 'target', '', '', '', '', '', 0, '_blank=新标签页\n_self=当前标签页\n_top=当前窗体\n_parent=父窗口\ntest1=测试1\ntest2=测试2\ntest3=测试3\ntest4=测试4', 0, 0, 0, 0, 0, '', 16, 1, 1686636254, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (17, 1, '浏览量', 'views', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 17, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (18, 1, '评论量', 'comments', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 18, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (19, 1, '点赞数', 'likes', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 19, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (20, 1, '点踩数', 'dislikes', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 20, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (21, 1, '权重', 'weigh', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 21, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (22, 1, '价格', 'price', '', '', '', '', '', 0, '', 1, 1, 1, 1, 1, '', 22, 1, 1686636251, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (23, 1, '货币', 'currency', '', '', '', '', '', 1, 'RMB=人民币\nintegral=积分', 1, 0, 1, 0, 1, '', 23, 1, 1686636252, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (24, 1, '允许访问', 'allow_visit_groups', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 24, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (25, 1, '评论', 'allow_comment_groups', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 25, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (26, 1, '状态', 'status', '', '', '', '', '', 0, '', 0, 1, 1, 0, 1, '', 26, 1, 1686632196, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (27, 1, '拒绝稿件原因', 'memo', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 27, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (28, 1, '发布时间', 'publish_time', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 28, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (29, 1, '修改时间', 'update_time', '', '', '', '', '', 0, '', 0, 0, 0, 1, 0, '', 29, 1, 1685163787, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (30, 1, '创建时间', 'create_time', '', '', '', '', '', 0, '', 0, 1, 1, 1, 0, '', 30, 1, NULL, 1685163618);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (31, 1, '新闻来源标题', 'source_title', 'string', '', '', '', '', 0, '', 1, 0, 1, 0, 1, '', 31, 1, 1686636250, 1685164130);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (32, 1, '新闻来源URL', 'source_url', 'string', '', '', '', '', 0, '', 1, 0, 1, 0, 1, '', 32, 1, 1685164174, 1685164166);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (33, 2, 'ID', 'id', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 33, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (34, 2, '投稿会员', 'user_id', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 34, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (35, 2, '发布管理员', 'admin_id', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 35, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (36, 2, '频道', 'channel_id', '', '', '', '', '', 0, '', 1, 1, 1, 0, 1, '', 36, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (37, 2, '副频道', 'channel_ids', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 37, 1, 1686771836, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (38, 2, '标题', 'title', '', '', '', '', '', 0, '', 1, 1, 1, 0, 1, '', 38, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (39, 2, '标题样式', 'title_style', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 39, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (40, 2, '标志', 'flag', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 40, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (41, 2, '封面图片', 'images', '', '', '', '', '', 0, '', 1, 1, 0, 0, 1, '', 41, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (42, 2, '内容', 'content', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 42, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (43, 2, 'SEO标题', 'seotitle', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 43, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (44, 2, '关键词', 'keywords', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 44, 1, 1686771829, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (45, 2, '描述', 'description', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 45, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (46, 2, '标签', 'tags', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 46, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (47, 2, '外部链接', 'url', '', '', '', '', '', 0, '', 0, 0, 1, 0, 0, '', 47, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (48, 2, '跳转方式', 'target', '', '', '', '', '', 0, '', 0, 0, 0, 0, 0, '', 48, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (49, 2, '浏览量', 'views', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 49, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (50, 2, '评论量', 'comments', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 50, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (51, 2, '点赞数', 'likes', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 51, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (52, 2, '点踩数', 'dislikes', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 52, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (53, 2, '权重', 'weigh', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 53, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (54, 2, '价格', 'price', '', '', '', '', '', 0, '', 1, 1, 1, 1, 1, '', 54, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (55, 2, '货币', 'currency', '', '', '', '', '', 0, '', 1, 0, 1, 0, 1, '', 55, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (56, 2, '允许访问', 'allow_visit_groups', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 56, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (57, 2, '评论', 'allow_comment_groups', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 57, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (58, 2, '状态', 'status', '', '', '', '', '', 0, '', 0, 1, 1, 0, 1, '', 58, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (59, 2, '拒绝稿件原因', 'memo', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 59, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (60, 2, '发布时间', 'publish_time', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 60, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (61, 2, '修改时间', 'update_time', '', '', '', '', '', 0, '', 0, 0, 0, 1, 0, '', 61, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (62, 2, '创建时间', 'create_time', '', '', '', '', '', 0, '', 0, 1, 1, 1, 0, '', 62, 1, NULL, 1685166277);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (63, 3, 'ID', 'id', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 63, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (64, 3, '投稿会员', 'user_id', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 64, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (65, 3, '发布管理员', 'admin_id', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 65, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (66, 3, '频道', 'channel_id', '', '', '', '', '', 0, '', 1, 1, 1, 0, 1, '', 66, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (67, 3, '副频道', 'channel_ids', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 67, 1, 1686771818, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (68, 3, '标题', 'title', '', '', '', '', '', 0, '', 1, 1, 1, 0, 1, '', 68, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (69, 3, '标题样式', 'title_style', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 69, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (70, 3, '标志', 'flag', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 70, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (71, 3, '封面图片', 'images', '', '', '', '', '', 0, '', 1, 1, 0, 0, 1, '', 71, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (72, 3, '内容', 'content', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 72, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (73, 3, 'SEO标题', 'seotitle', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 73, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (74, 3, '关键词', 'keywords', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 74, 1, 1686771812, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (75, 3, '描述', 'description', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 75, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (76, 3, '标签', 'tags', '', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 76, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (77, 3, '外部链接', 'url', '', '', '', '', '', 0, '', 0, 0, 1, 0, 0, '', 77, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (78, 3, '跳转方式', 'target', '', '', '', '', '', 0, '', 0, 0, 0, 0, 0, '', 78, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (79, 3, '浏览量', 'views', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 79, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (80, 3, '评论量', 'comments', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 80, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (81, 3, '点赞数', 'likes', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 81, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (82, 3, '点踩数', 'dislikes', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 82, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (83, 3, '权重', 'weigh', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 83, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (84, 3, '价格', 'price', '', '', '', '', '', 0, '', 1, 1, 1, 1, 1, '', 84, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (85, 3, '货币', 'currency', '', '', '', '', '', 0, '', 1, 0, 1, 0, 1, '', 85, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (86, 3, '允许访问', 'allow_visit_groups', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 86, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (87, 3, '评论', 'allow_comment_groups', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 87, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (88, 3, '状态', 'status', '', '', '', '', '', 0, '', 0, 1, 1, 0, 1, '', 88, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (89, 3, '拒绝稿件原因', 'memo', '', '', '', '', '', 0, '', 0, 0, 0, 0, 1, '', 89, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (90, 3, '发布时间', 'publish_time', '', '', '', '', '', 0, '', 0, 1, 1, 1, 1, '', 90, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (91, 3, '修改时间', 'update_time', '', '', '', '', '', 0, '', 0, 0, 0, 1, 0, '', 91, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (92, 3, '创建时间', 'create_time', '', '', '', '', '', 0, '', 0, 1, 1, 1, 0, '', 92, 1, NULL, 1685166772);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (93, 3, '下载链接', 'downloads', 'array', '', '', '', '', 0, '', 1, 0, 0, 0, 1, '', 93, 1, 1686511967, 1685166843);
INSERT INTO `__PREFIX__cms_content_model_field_config` VALUES (94, 3, '评论后下载', 'download_after_comment', 'radio', '启用时用户发表评论后才能查看下载链接', '', '', '', 0, '', 1, 0, 1, 0, 1, '', 94, 1, 1686511966, 1686210007);
COMMIT;


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_friendly_link`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员',
    `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
    `link` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链接',
    `target` enum('_blank','_self','_top','_parent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_blank' COMMENT '跳转方式:_blank=新标签页,_self=当前标签页,_top=当前窗体,_parent=父窗口',
    `logo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'LOGO',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `status` enum('disable','enable','pending_trial') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enable' COMMENT '状态:disable=禁用,enable=启用,pending_trial=待审',
    `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
    `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '友情链接';


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_pay_log`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员',
    `object_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '付款对象',
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `project` enum('admire','content') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admire' COMMENT '项目:admire=赞赏,content=内容付费',
    `amount` int(10) NOT NULL DEFAULT 0 COMMENT '金额',
    `type` enum('wx','alipay','balance','score') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'wx' COMMENT '支付方式:wx=微信,alipay=支付宝,balance=余额,score=积分',
    `sn` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付平台订单号',
    `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `pay_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '支付时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户支付记录';


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_search_log`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加用户',
    `search` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
    `count` int(10) NOT NULL DEFAULT 0 COMMENT '搜索次数',
    `hot` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '热门搜索:0=关,1=开',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户搜索记录';


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_statistics`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
    `content_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '内容ID',
    `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型:0=浏览,1=点赞,2=收藏',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '统计数据';


CREATE TABLE IF NOT EXISTS `__PREFIX__cms_tags`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
    `type` enum('default','success','info','warning','danger') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default' COMMENT '标签类型:default=default,success=success,info=info,warning=warning,danger=danger',
    `document_count` int(10) NOT NULL DEFAULT 0 COMMENT '文档数',
    `seotitle` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SEO标题',
    `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
    `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
    `inner_chain` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '自动内链:0=关,1=开',
    `weigh` int(10) NULL DEFAULT NULL COMMENT '权重',
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `update_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
    `create_time` bigint(16) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '标签管理';


-- ----------------------------
-- 模型表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_downloads`  (
    `id` int(10) UNSIGNED NOT NULL COMMENT 'ID(同主表)',
    `downloads` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '下载链接',
    `download_after_comment` enum('disable','enable') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'disable' COMMENT '评论后下载:disable=禁用,enable=启用',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'cms下载表';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_news`  (
    `id` int(10) UNSIGNED NOT NULL COMMENT 'ID(同主表)',
    `source_title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '新闻来源标题',
    `source_url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '新闻来源URL',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'cms新闻表';

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_products`  (
    `id` int(10) UNSIGNED NOT NULL COMMENT 'ID(同主表)',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'cms产品表';

BEGIN;
ALTER TABLE `__PREFIX__cms_content_model_field_config` MODIFY weigh int(10) NULL DEFAULT NULL COMMENT '权重';
COMMIT;

BEGIN;
ALTER TABLE `__PREFIX__cms_block` ADD COLUMN `platform` enum('nuxt','uniapp') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nuxt' COMMENT '平台:uniapp=uni-app,nuxt=Nuxt' AFTER `id`;
ALTER TABLE `__PREFIX__cms_block` MODIFY target enum('_blank','_self','_top','_parent', 'navigateTo', 'redirectTo', 'reLaunch', 'switchTab') NOT NULL DEFAULT '_blank' COMMENT '跳转方式:_blank=新标签页,_self=当前标签页,_top=当前窗体,_parent=父窗口,navigateTo=保留当前页面跳转,redirectTo=关闭当前页面跳转,reLaunch=关闭所有页面跳转,switchTab=关闭所有 tabBar 页面跳转';
COMMIT;