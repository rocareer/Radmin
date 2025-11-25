/*
 知识库模块权限菜单补丁 - 简化版本
 仅包含当前已实现的基础配置管理功能
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 知识库管理主菜单
-- ----------------------------
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(0, 'menu_dir', '知识库管理', 'kb', 'kb', 'fa fa-book', NULL, '', '', 0, 'none', '知识库管理模块', 79, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

-- 获取刚插入的知识库主菜单ID
SET @kb_parent_id = LAST_INSERT_ID();

-- ----------------------------
-- 基础配置管理
-- ----------------------------
INSERT INTO `ra_admin_rule` (`pid`, `type`, `title`, `name`, `path`, `icon`, `menu_type`, `url`, `component`, `keepalive`, `extend`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
(@kb_parent_id, 'menu_dir', '基础配置', 'kb/basic', 'kb/basic', 'fa fa-cogs', NULL, '', '', 0, 'none', '知识库基础配置', 78, '1', UNIX_TIMESTAMP() * 1000, UNIX_TIMESTAMP() * 1000);

SET @kb_basic_id = LAST_INSERT_ID();

-- ----------------------------
-- 知识库类型管理
-- ----------------------------
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

-- ----------------------------
-- 知识库分类管理
-- ----------------------------
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

-- ----------------------------
-- 知识库标签管理
-- ----------------------------
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

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- 菜单结构说明
-- ----------------------------
/*
知识库管理 (权重:79) - fa fa-book
└── 基础配置 (权重:78) - fa fa-cogs
    ├── 类型管理 (权重:77) - fa fa-tags
    │   ├── 查看、添加、编辑、删除、快速排序
    ├── 分类管理 (权重:76) - fa fa-folder  
    │   ├── 查看、添加、编辑、删除、快速排序
    └── 标签管理 (权重:75) - fa fa-tag
        ├── 查看、添加、编辑、删除、快速排序

当前已实现功能：
✅ 类型管理 - 完整的CRUD和状态切换功能
✅ 分类管理 - 完整的CRUD和状态切换功能  
✅ 标签管理 - 完整的CRUD和状态切换功能

图标说明：
- 知识库管理: fa fa-book (书本图标，代表知识库)
- 基础配置: fa fa-cogs (齿轮图标，代表配置管理)
- 类型管理: fa fa-tags (标签图标，代表内容类型)
- 分类管理: fa fa-folder (文件夹图标，代表分类层级)
- 标签管理: fa fa-tag (单标签图标，代表标签系统)

使用方法：
1. 执行此SQL文件添加菜单权限
2. 在角色管理中为相应角色分配知识库相关权限
3. 刷新后台页面即可看到知识库管理菜单
*/