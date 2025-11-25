/*
 知识库模块权限菜单补丁
 包含知识库管理的完整菜单结构和权限按钮
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 知识库管理主菜单
-- ----------------------------
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(0, 'menu_dir', '知识库管理', 'kb', 'kb', 'fa fa-book', NULL, '', '', 0, 'none', '知识库管理模块', 79, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- 获取刚插入的知识库主菜单ID (假设为296，实际使用时需要获取)
SET @kb_parent_id = LAST_INSERT_ID();

-- ----------------------------
-- 基础配置管理
-- ----------------------------
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_parent_id, 'menu_dir', '基础配置', 'kb/basic', 'kb/basic', 'fa fa-cogs', NULL, '', '', 0, 'none', '知识库基础配置', 78, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_basic_id = LAST_INSERT_ID();

-- 知识库类型管理
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_basic_id, 'menu', '类型管理', 'kb/type', 'kb/type', 'fa fa-tags', 'tab', '', '/src/views/backend/kb/type/index.vue', 1, 'none', '知识库类型管理', 77, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_type_id = LAST_INSERT_ID();

-- 类型管理按钮权限
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_type_id, 'button', '查看', 'kb/type/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_type_id, 'button', '添加', 'kb/type/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_type_id, 'button', '编辑', 'kb/type/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_type_id, 'button', '删除', 'kb/type/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_type_id, 'button', '快速排序', 'kb/type/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- 知识库分类管理
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_basic_id, 'menu', '分类管理', 'kb/category', 'kb/category', 'fa fa-folder', 'tab', '', '/src/views/backend/kb/category/index.vue', 1, 'none', '知识库分类管理', 76, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_category_id = LAST_INSERT_ID();

-- 分类管理按钮权限
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_category_id, 'button', '查看', 'kb/category/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_category_id, 'button', '添加', 'kb/category/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_category_id, 'button', '编辑', 'kb/category/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_category_id, 'button', '删除', 'kb/category/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_category_id, 'button', '快速排序', 'kb/category/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- 知识库标签管理
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_basic_id, 'menu', '标签管理', 'kb/tag', 'kb/tag', 'fa fa-tag', 'tab', '', '/src/views/backend/kb/tag/index.vue', 1, 'none', '知识库标签管理', 75, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_tag_id = LAST_INSERT_ID();

-- 标签管理按钮权限
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_tag_id, 'button', '查看', 'kb/tag/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_tag_id, 'button', '添加', 'kb/tag/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_tag_id, 'button', '编辑', 'kb/tag/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_tag_id, 'button', '删除', 'kb/tag/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_tag_id, 'button', '快速排序', 'kb/tag/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- ----------------------------
-- 内容管理
-- ----------------------------
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_parent_id, 'menu_dir', '内容管理', 'kb/content', 'kb/content', 'fa fa-file-text-o', NULL, '', '', 0, 'none', '知识库内容管理', 74, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_content_id = LAST_INSERT_ID();

-- 知识库管理
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_content_id, 'menu', '知识库管理', 'kb/kb', 'kb/kb', 'fa fa-book', 'tab', '', '/src/views/backend/kb/kb/index.vue', 1, 'none', '知识库主管理', 73, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_kb_id = LAST_INSERT_ID();

-- 知识库管理按钮权限
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_kb_id, 'button', '查看', 'kb/kb/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_kb_id, 'button', '添加', 'kb/kb/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_kb_id, 'button', '编辑', 'kb/kb/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_kb_id, 'button', '删除', 'kb/kb/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- 文章内容管理
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_content_id, 'menu', '文章管理', 'kb/content/article', 'kb/content/article', 'fa fa-pencil', 'tab', '', '/src/views/backend/kb/content/index.vue', 1, 'none', '知识库文章内容管理', 72, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_article_id = LAST_INSERT_ID();

-- 文章管理按钮权限
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_article_id, 'button', '查看', 'kb/content/article/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_article_id, 'button', '添加', 'kb/content/article/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_article_id, 'button', '编辑', 'kb/content/article/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_article_id, 'button', '删除', 'kb/content/article/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_article_id, 'button', '置顶', 'kb/content/article/top', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_article_id, 'button', '发布', 'kb/content/article/publish', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- ----------------------------
-- 统计分析
-- ----------------------------
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_parent_id, 'menu_dir', '统计分析', 'kb/statistics', 'kb/statistics', 'fa fa-bar-chart', NULL, '', '', 0, 'none', '知识库统计分析', 71, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_statistics_id = LAST_INSERT_ID();

-- 访问统计
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_statistics_id, 'menu', '访问统计', 'kb/statistics/views', 'kb/statistics/views', 'fa fa-eye', 'tab', '', '/src/views/backend/kb/statistics/views.vue', 1, 'none', '知识库访问统计', 70, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_views_id = LAST_INSERT_ID();

-- 访问统计按钮权限
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_views_id, 'button', '查看', 'kb/statistics/views/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_views_id, 'button', '导出', 'kb/statistics/views/export', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- 热门内容
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_statistics_id, 'menu', '热门内容', 'kb/statistics/hot', 'kb/statistics/hot', 'fa fa-fire', 'tab', '', '/src/views/backend/kb/statistics/hot.vue', 1, 'none', '知识库热门内容统计', 69, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_hot_id = LAST_INSERT_ID();

-- 热门内容按钮权限
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_hot_id, 'button', '查看', 'kb/statistics/hot/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000),
(@kb_hot_id, 'button', '导出', 'kb/statistics/hot/export', '', '', NULL, '', '', 0, 'none', '', 0, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- 菜单结构说明
-- ----------------------------
/*
知识库管理 (权重:79)
├── 基础配置 (权重:78)
│   ├── 类型管理 (权重:77) - fa fa-tags
│   │   ├── 查看、添加、编辑、删除、快速排序
│   ├── 分类管理 (权重:76) - fa fa-folder  
│   │   ├── 查看、添加、编辑、删除、快速排序
│   └── 标签管理 (权重:75) - fa fa-tag
│       ├── 查看、添加、编辑、删除、快速排序
├── 内容管理 (权重:74)
│   ├── 知识库管理 (权重:73) - fa fa-book
│   │   ├── 查看、添加、编辑、删除
│   └── 文章管理 (权重:72) - fa fa-pencil
│       ├── 查看、添加、编辑、删除、置顶、发布
└── 统计分析 (权重:71)
    ├── 访问统计 (权重:70) - fa fa-eye
    │   ├── 查看、导出
    └── 热门内容 (权重:69) - fa fa-fire
        ├── 查看、导出

图标选择说明：
- 主菜单: fa fa-book (书本图标，代表知识库)
- 基础配置: fa fa-cogs (齿轮图标，代表配置)
- 类型管理: fa fa-tags (标签图标，代表分类标签)
- 分类管理: fa fa-folder (文件夹图标，代表分类)
- 标签管理: fa fa-tag (单标签图标，代表标签)
- 内容管理: fa fa-file-text-o (文档图标，代表内容)
- 知识库管理: fa fa-book (书本图标，代表知识库)
- 文章管理: fa fa-pencil (笔图标，代表文章编写)
- 统计分析: fa fa-bar-chart (图表图标，代表统计)
- 访问统计: fa fa-eye (眼睛图标，代表访问)
- 热门内容: fa fa-fire (火焰图标，代表热门)

权限说明：
- 每个菜单都配置了完整的CRUD权限
- 特殊功能如置顶、发布、导出等单独配置权限
- 权重按照模块重要性递减排列
- 所有菜单默认启用状态
*/