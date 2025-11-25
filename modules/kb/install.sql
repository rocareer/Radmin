-- 知识库模块数据库结构

-- 知识库类型表
CREATE TABLE IF NOT EXISTS `__PREFIX__kb_type` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(100) NOT NULL DEFAULT '' COMMENT '类型名称',
    `description` varchar(500) NOT NULL DEFAULT '' COMMENT '类型描述',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
    `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `weigh` (`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='知识库类型表';

-- 知识库分类表
CREATE TABLE IF NOT EXISTS `__PREFIX__kb_category` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `type_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型ID',
    `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
    `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
    `description` varchar(500) NOT NULL DEFAULT '' COMMENT '分类描述',
    `icon` varchar(200) NOT NULL DEFAULT '' COMMENT '分类图标',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `weigh` int(10) NOT NULL DEFAULT 0 COMMENT '权重',
    `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `type_id` (`type_id`),
    KEY `pid` (`pid`),
    KEY `status` (`status`),
    KEY `weigh` (`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='知识库分类表';

-- 知识库内容表
CREATE TABLE IF NOT EXISTS `__PREFIX__kb_content` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `type_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型ID',
    `category_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分类ID',
    `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
    `content` text COMMENT '内容',
    `keywords` varchar(500) NOT NULL DEFAULT '' COMMENT '关键词',
    `summary` varchar(1000) NOT NULL DEFAULT '' COMMENT '摘要',
    `author` varchar(100) NOT NULL DEFAULT '' COMMENT '作者',
    `source` varchar(200) NOT NULL DEFAULT '' COMMENT '来源',
    `views` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
    `likes` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0=草稿,1=发布,2=审核中',
    `is_top` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否置顶',
    `is_recommend` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否推荐',
    `publish_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布时间',
    `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `type_id` (`type_id`),
    KEY `category_id` (`category_id`),
    KEY `status` (`status`),
    KEY `is_top` (`is_top`),
    KEY `is_recommend` (`is_recommend`),
    KEY `publish_time` (`publish_time`),
    FULLTEXT KEY `title_content` (`title`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='知识库内容表';

-- 知识库标签表
CREATE TABLE IF NOT EXISTS `__PREFIX__kb_tag` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '标签名称',
    `color` varchar(20) NOT NULL DEFAULT '#409EFF' COMMENT '标签颜色',
    `count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '使用次数',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
    `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='知识库标签表';

-- 知识库内容标签关联表
CREATE TABLE IF NOT EXISTS `__PREFIX__kb_content_tag` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `content_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '内容ID',
    `tag_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '标签ID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `content_tag` (`content_id`,`tag_id`),
    KEY `content_id` (`content_id`),
    KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='知识库内容标签关联表';

-- 知识库附件表
CREATE TABLE IF NOT EXISTS `__PREFIX__kb_attachment` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `content_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '内容ID',
    `name` varchar(200) NOT NULL DEFAULT '' COMMENT '附件名称',
    `path` varchar(500) NOT NULL DEFAULT '' COMMENT '附件路径',
    `size` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
    `mime_type` varchar(100) NOT NULL DEFAULT '' COMMENT '文件类型',
    `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `content_id` (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='知识库附件表';

-- 初始化数据
INSERT INTO `__PREFIX__kb_type` (`id`, `name`, `description`, `status`, `weigh`, `create_time`) VALUES
(1, '技术文档', '技术相关的文档和教程', 1, 100, UNIX_TIMESTAMP()),
(2, '产品手册', '产品使用手册和说明', 1, 90, UNIX_TIMESTAMP()),
(3, '常见问题', '常见问题解答', 1, 80, UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__kb_category` (`id`, `type_id`, `pid`, `name`, `description`, `status`, `weigh`, `create_time`) VALUES
(1, 1, 0, '前端开发', '前端技术相关文档', 1, 100, UNIX_TIMESTAMP()),
(2, 1, 0, '后端开发', '后端技术相关文档', 1, 90, UNIX_TIMESTAMP()),
(3, 2, 0, '用户手册', '用户使用手册', 1, 100, UNIX_TIMESTAMP());