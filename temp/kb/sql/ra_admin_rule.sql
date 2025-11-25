/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80044
 Source Host           : 127.0.0.1:3306
 Source Schema         : r251124

 Target Server Type    : MySQL
 Target Server Version : 80044
 File Encoding         : 65001

 Date: 25/11/2025 19:21:33
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ra_admin_rule
-- ----------------------------
DROP TABLE IF EXISTS `ra_admin_rule`;
CREATE TABLE `ra_admin_rule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `type` enum('menu_dir','menu','button') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '类型:menu_dir=菜单目录,menu=菜单项,button=页面按钮',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由路径',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `menu_type` enum('tab','link','iframe') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Url',
  `component` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
  `keepalive` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '缓存:0=关闭,1=开启',
  `extend` enum('none','add_rules_only','add_menu_only') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='菜单和权限规则表';

-- ----------------------------
-- Records of ra_admin_rule
-- ----------------------------
BEGIN;
INSERT INTO `ra_admin_rule` VALUES (1, 0, 'menu', '控制台', 'dashboard', 'dashboard', 'fa fa-dashboard', 'tab', '', '/src/views/backend/dashboard.vue', 1, 'none', 'Remark lang', 1001, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (2, 0, 'menu_dir', '权限管理', 'auth', 'auth', 'fa fa-group', NULL, '', '', 0, 'none', '', 102, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (3, 2, 'menu', '角色组管理', 'auth/group', 'auth/group', 'fa fa-group', 'tab', '', '/src/views/backend/auth/group/index.vue', 1, 'none', 'Remark lang', 101, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (4, 3, 'button', '查看', 'auth/group/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (5, 3, 'button', '添加', 'auth/group/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (6, 3, 'button', '编辑', 'auth/group/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (7, 3, 'button', '删除', 'auth/group/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (8, 2, 'menu', '管理员管理', 'auth/admin', 'auth/admin', 'el-icon-UserFilled', 'tab', '', '/src/views/backend/auth/admin/index.vue', 1, 'none', '', 100, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (9, 8, 'button', '查看', 'auth/admin/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (10, 8, 'button', '添加', 'auth/admin/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (11, 8, 'button', '编辑', 'auth/admin/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (12, 8, 'button', '删除', 'auth/admin/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (13, 2, 'menu', '菜单规则管理', 'auth/rule', 'auth/rule', 'el-icon-Grid', 'tab', '', '/src/views/backend/auth/rule/index.vue', 1, 'none', '', 99, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (14, 13, 'button', '查看', 'auth/rule/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (15, 13, 'button', '添加', 'auth/rule/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (16, 13, 'button', '编辑', 'auth/rule/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (17, 13, 'button', '删除', 'auth/rule/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (18, 13, 'button', '快速排序', 'auth/rule/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (19, 2, 'menu', '管理员日志管理', 'auth/adminLog', 'auth/adminLog', 'el-icon-List', 'tab', '', '/src/views/backend/auth/adminLog/index.vue', 1, 'none', '', 98, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (20, 19, 'button', '查看', 'auth/adminLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (21, 0, 'menu_dir', '会员管理', 'user', 'user', 'fa fa-drivers-license', NULL, '', '', 0, 'none', '', 97, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (22, 21, 'menu', '会员管理', 'user/user', 'user/user', 'fa fa-user', 'tab', '', '/src/views/backend/user/user/index.vue', 1, 'none', '', 96, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (23, 22, 'button', '查看', 'user/user/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (24, 22, 'button', '添加', 'user/user/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (25, 22, 'button', '编辑', 'user/user/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (26, 22, 'button', '删除', 'user/user/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (27, 21, 'menu', '会员分组管理', 'user/group', 'user/group', 'fa fa-group', 'tab', '', '/src/views/backend/user/group/index.vue', 1, 'none', '', 95, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (28, 27, 'button', '查看', 'user/group/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (29, 27, 'button', '添加', 'user/group/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (30, 27, 'button', '编辑', 'user/group/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (31, 27, 'button', '删除', 'user/group/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (32, 21, 'menu', '会员规则管理', 'user/rule', 'user/rule', 'fa fa-th-list', 'tab', '', '/src/views/backend/user/rule/index.vue', 1, 'none', '', 94, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (33, 32, 'button', '查看', 'user/rule/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (34, 32, 'button', '添加', 'user/rule/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (35, 32, 'button', '编辑', 'user/rule/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (36, 32, 'button', '删除', 'user/rule/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (37, 32, 'button', '快速排序', 'user/rule/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (38, 21, 'menu', '会员余额管理', 'user/moneyLog', 'user/moneyLog', 'el-icon-Money', 'tab', '', '/src/views/backend/user/moneyLog/index.vue', 1, 'none', '', 93, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (39, 38, 'button', '查看', 'user/moneyLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (40, 38, 'button', '添加', 'user/moneyLog/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (41, 21, 'menu', '会员积分管理', 'user/scoreLog', 'user/scoreLog', 'el-icon-Discount', 'tab', '', '/src/views/backend/user/scoreLog/index.vue', 1, 'none', '', 92, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (42, 41, 'button', '查看', 'user/scoreLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (43, 41, 'button', '添加', 'user/scoreLog/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (44, 0, 'menu_dir', '常规管理', 'routine', 'routine', 'fa fa-cogs', NULL, '', '', 0, 'none', '', 90, '1', 1764069614, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (45, 44, 'menu', '系统配置', 'routine/config', 'routine/config', 'el-icon-Tools', 'tab', '', '/src/views/backend/routine/config/index.vue', 1, 'none', '', 88, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (46, 45, 'button', '查看', 'routine/config/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (47, 45, 'button', '编辑', 'routine/config/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (48, 44, 'menu', '附件管理', 'routine/attachment', 'routine/attachment', 'fa fa-folder', 'tab', '', '/src/views/backend/routine/attachment/index.vue', 1, 'none', 'Remark lang', 87, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (49, 48, 'button', '查看', 'routine/attachment/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (50, 48, 'button', '编辑', 'routine/attachment/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (51, 48, 'button', '删除', 'routine/attachment/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (52, 44, 'menu', '个人资料', 'routine/adminInfo', 'routine/adminInfo', 'fa fa-user', 'tab', '', '/src/views/backend/routine/adminInfo.vue', 1, 'none', '', 86, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (53, 52, 'button', '查看', 'routine/adminInfo/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (54, 52, 'button', '编辑', 'routine/adminInfo/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (55, 0, 'menu_dir', '数据安全管理', 'security', 'security', 'fa fa-shield', NULL, '', '', 0, 'none', '', 85, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (56, 55, 'menu', '数据回收站', 'security/dataRecycleLog', 'security/dataRecycleLog', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycleLog/index.vue', 1, 'none', '', 84, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (57, 56, 'button', '查看', 'security/dataRecycleLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (58, 56, 'button', '删除', 'security/dataRecycleLog/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (59, 56, 'button', '还原', 'security/dataRecycleLog/restore', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (60, 56, 'button', '查看详情', 'security/dataRecycleLog/info', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (61, 55, 'menu', '敏感数据修改记录', 'security/sensitiveDataLog', 'security/sensitiveDataLog', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveDataLog/index.vue', 1, 'none', '', 83, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (62, 61, 'button', '查看', 'security/sensitiveDataLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (63, 61, 'button', '删除', 'security/sensitiveDataLog/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (64, 61, 'button', '回滚', 'security/sensitiveDataLog/rollback', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (65, 61, 'button', '查看详情', 'security/sensitiveDataLog/info', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (66, 55, 'menu', '数据回收规则管理', 'security/dataRecycle', 'security/dataRecycle', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycle/index.vue', 1, 'none', 'Remark lang', 82, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (67, 66, 'button', '查看', 'security/dataRecycle/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (68, 66, 'button', '添加', 'security/dataRecycle/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (69, 66, 'button', '编辑', 'security/dataRecycle/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (70, 66, 'button', '删除', 'security/dataRecycle/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (71, 55, 'menu', '敏感字段规则管理', 'security/sensitiveData', 'security/sensitiveData', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveData/index.vue', 1, 'none', 'Remark lang', 81, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (72, 71, 'button', '查看', 'security/sensitiveData/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (73, 71, 'button', '添加', 'security/sensitiveData/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (74, 71, 'button', '编辑', 'security/sensitiveData/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (75, 71, 'button', '删除', 'security/sensitiveData/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (76, 0, 'menu', 'Radmin', 'radmin/radmin', 'radmin', 'local-logo', 'link', 'https://radmin.rocareer.com', '', 0, 'none', '', 0, '0', 1763976174, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (77, 45, 'button', '添加', 'routine/config/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (78, 0, 'menu', '模块市场', 'moduleStore/moduleStore', 'moduleStore', 'el-icon-GoodsFilled', 'tab', '', '/src/views/backend/module/index.vue', 0, 'none', '', 86, '0', 1764069624, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (79, 78, 'button', '查看', 'moduleStore/moduleStore/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (80, 78, 'button', '安装', 'moduleStore/moduleStore/install', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (81, 78, 'button', '调整状态', 'moduleStore/moduleStore/changeState', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (82, 78, 'button', '卸载', 'moduleStore/moduleStore/uninstall', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (83, 78, 'button', '更新', 'moduleStore/moduleStore/update', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (84, 0, 'menu', 'CRUD代码生成', 'crud/crud', 'crud/crud', 'fa fa-code', 'tab', '', '/src/views/backend/crud/index.vue', 1, 'none', '', 80, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (85, 84, 'button', '查看', 'crud/crud/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (86, 84, 'button', '生成', 'crud/crud/generate', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (87, 84, 'button', '删除', 'crud/crud/delete', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (88, 45, 'button', '删除', 'routine/config/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967889, 1763967889);
INSERT INTO `ra_admin_rule` VALUES (89, 1, 'button', '查看', 'dashboard/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763967890, 1763967890);
INSERT INTO `ra_admin_rule` VALUES (90, 0, 'menu_dir', 'CMS管理', 'cms', 'cms', 'fa fa-newspaper-o', 'tab', '', '', 1, 'none', '', 0, '0', 1764069632, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (91, 90, 'menu_dir', '内容管理', 'cms/content', 'cms/content', 'fa fa-file-text-o', 'tab', '', '', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (92, 91, 'menu', '新闻管理', 'cms/content/cms_news', 'cms/content/cms_news', 'fa fa-circle-o', 'tab', '', '/src/views/backend/cms/content/index.vue', 1, 'none', '', 1, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (93, 92, 'button', '查看', 'cms/content/cms_news/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (94, 92, 'button', '添加', 'cms/content/cms_news/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (95, 92, 'button', '编辑', 'cms/content/cms_news/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (96, 92, 'button', '删除', 'cms/content/cms_news/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (97, 91, 'menu', '产品管理', 'cms/content/cms_products', 'cms/content/cms_products', 'fa fa-circle-o', 'tab', '', '/src/views/backend/cms/content/index.vue', 1, 'none', '', 1, '0', 1764025426, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (98, 97, 'button', '查看', 'cms/content/cms_products/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (99, 97, 'button', '添加', 'cms/content/cms_products/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (100, 97, 'button', '编辑', 'cms/content/cms_products/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (101, 97, 'button', '删除', 'cms/content/cms_products/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (102, 91, 'menu', '下载管理', 'cms/content/cms_downloads', 'cms/content/cms_downloads', 'fa fa-circle-o', 'tab', '', '/src/views/backend/cms/content/index.vue', 1, 'none', '', 1, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (103, 102, 'button', '查看', 'cms/content/cms_downloads/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (104, 102, 'button', '添加', 'cms/content/cms_downloads/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (105, 102, 'button', '编辑', 'cms/content/cms_downloads/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (106, 102, 'button', '删除', 'cms/content/cms_downloads/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (107, 91, 'menu', '内容模型管理', 'cms/contentModel', 'cms/contentModel', 'fa fa-cubes', 'tab', '', '/src/views/backend/cms/contentModel/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (108, 107, 'button', '查看', 'cms/contentModel/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (109, 107, 'button', '添加', 'cms/contentModel/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (110, 107, 'button', '编辑', 'cms/contentModel/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (111, 107, 'button', '删除', 'cms/contentModel/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (112, 91, 'menu', '内容模型字段管理', 'cms/contentModelFieldConfig', 'cms/contentModelFieldConfig', 'fa fa-cube', 'tab', '', '/src/views/backend/cms/contentModelFieldConfig/index.vue', 1, 'none', '一个模型对应一个数据表，然后您可以在此管理该表的字段，同时可配置对应模型的管理功能本身', -1, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (113, 112, 'button', '查看', 'cms/contentModelFieldConfig/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (114, 112, 'button', '添加', 'cms/contentModelFieldConfig/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (115, 112, 'button', '编辑', 'cms/contentModelFieldConfig/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (116, 112, 'button', '删除', 'cms/contentModelFieldConfig/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (117, 112, 'button', '快速排序', 'cms/contentModelFieldConfig/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (118, 90, 'menu', '标签管理', 'cms/tags', 'cms/tags', 'fa fa-tags', 'tab', '', '/src/views/backend/cms/tags/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (119, 118, 'button', '查看', 'cms/tags/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (120, 118, 'button', '添加', 'cms/tags/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (121, 118, 'button', '编辑', 'cms/tags/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (122, 118, 'button', '删除', 'cms/tags/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (123, 118, 'button', '快速排序', 'cms/tags/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (124, 90, 'menu', '频道管理', 'cms/channel', 'cms/channel', 'fa fa-th-large', 'tab', '', '/src/views/backend/cms/channel/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (125, 124, 'button', '查看', 'cms/channel/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (126, 124, 'button', '添加', 'cms/channel/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (127, 124, 'button', '编辑', 'cms/channel/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (128, 124, 'button', '删除', 'cms/channel/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (129, 124, 'button', '快速排序', 'cms/channel/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (130, 90, 'menu', '区块管理', 'cms/block', 'cms/block', 'fa fa-columns', 'tab', '', '/src/views/backend/cms/block/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (131, 130, 'button', '查看', 'cms/block/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (132, 130, 'button', '添加', 'cms/block/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (133, 130, 'button', '编辑', 'cms/block/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (134, 130, 'button', '删除', 'cms/block/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (135, 130, 'button', '快速排序', 'cms/block/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (136, 90, 'menu', 'CMS配置', 'cms/config', 'cms/config', 'fa fa-gear', 'tab', '', '/src/views/backend/cms/config/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (137, 136, 'button', '查看', 'cms/config/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (138, 136, 'button', '保存', 'cms/config/save', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (139, 90, 'menu', '友情链接', 'cms/friendlyLink', 'cms/friendlyLink', 'fa fa-unlink', 'tab', '', '/src/views/backend/cms/friendlyLink/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (140, 139, 'button', '查看', 'cms/friendlyLink/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (141, 139, 'button', '添加', 'cms/friendlyLink/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (142, 139, 'button', '编辑', 'cms/friendlyLink/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (143, 139, 'button', '删除', 'cms/friendlyLink/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (144, 139, 'button', '快速排序', 'cms/friendlyLink/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (145, 90, 'menu', '评论管理', 'cms/comment', 'cms/comment', 'fa fa-comment-o', 'tab', '', '/src/views/backend/cms/comment/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (146, 145, 'button', '查看', 'cms/comment/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (147, 145, 'button', '添加', 'cms/comment/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (148, 145, 'button', '编辑', 'cms/comment/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (149, 145, 'button', '删除', 'cms/comment/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (150, 145, 'button', '快速排序', 'cms/comment/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (151, 90, 'menu', '用户支付记录', 'cms/payLog', 'cms/payLog', 'fa fa-file-text-o', 'tab', '', '/src/views/backend/cms/payLog/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (152, 151, 'button', '查看', 'cms/payLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (153, 151, 'button', '添加', 'cms/payLog/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (154, 151, 'button', '编辑', 'cms/payLog/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (155, 151, 'button', '删除', 'cms/payLog/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (156, 151, 'button', '快速排序', 'cms/payLog/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (157, 90, 'menu', '用户搜索记录', 'cms/searchLog', 'cms/searchLog', 'fa fa-file-text-o', 'tab', '', '/src/views/backend/cms/searchLog/index.vue', 1, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (158, 157, 'button', '查看', 'cms/searchLog/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (159, 157, 'button', '添加', 'cms/searchLog/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (160, 157, 'button', '编辑', 'cms/searchLog/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (161, 157, 'button', '删除', 'cms/searchLog/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (162, 157, 'button', '快速排序', 'cms/searchLog/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763981324, 1763981324);
INSERT INTO `ra_admin_rule` VALUES (181, 21, 'menu', '会员消息管理', 'user/message', 'user/message', 'fa fa-commenting-o', 'tab', '', '/src/views/backend/user/message/index.vue', 1, 'none', '', 10, '1', 1763982303, 1763982303);
INSERT INTO `ra_admin_rule` VALUES (182, 181, 'button', '查看', 'user/message/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982303, 1763982303);
INSERT INTO `ra_admin_rule` VALUES (183, 181, 'button', '添加', 'user/message/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982303, 1763982303);
INSERT INTO `ra_admin_rule` VALUES (184, 181, 'button', '编辑', 'user/message/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982303, 1763982303);
INSERT INTO `ra_admin_rule` VALUES (185, 181, 'button', '删除', 'user/message/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982303, 1763982303);
INSERT INTO `ra_admin_rule` VALUES (186, 181, 'button', '快速排序', 'user/message/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982303, 1763982303);
INSERT INTO `ra_admin_rule` VALUES (187, 21, 'menu', '会员名片组件管理', 'user/cardComponent', 'user/cardComponent', 'fa fa-drivers-license-o', 'tab', '', '/src/views/backend/user/cardComponent/index.vue', 1, 'none', '', 9, '1', 1763982303, 1763982303);
INSERT INTO `ra_admin_rule` VALUES (188, 187, 'button', '查看', 'user/cardComponent/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (189, 187, 'button', '添加', 'user/cardComponent/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (190, 187, 'button', '编辑', 'user/cardComponent/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (191, 187, 'button', '删除', 'user/cardComponent/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (192, 187, 'button', '快速排序', 'user/cardComponent/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (193, 21, 'menu', '会员最近动态管理', 'user/recent', 'user/recent', 'fa fa-podcast', 'tab', '', '/src/views/backend/user/recent/index.vue', 1, 'none', '', 8, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (194, 193, 'button', '查看', 'user/recent/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (195, 193, 'button', '添加', 'user/recent/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (196, 193, 'button', '编辑', 'user/recent/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (197, 193, 'button', '删除', 'user/recent/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (198, 193, 'button', '快速排序', 'user/recent/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1763982304, 1763982304);
INSERT INTO `ra_admin_rule` VALUES (199, 0, 'menu_dir', '问卷调查', 'questionnaire', 'questionnaire', 'el-icon-Edit', 'tab', '', '', 0, 'none', '', 0, '0', 1764069632, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (200, 199, 'menu', '问卷管理', 'questionnaire/examination', 'questionnaire/examination', 'el-icon-Files', 'tab', '', '/src/views/backend/questionnaire/examination/index.vue', 1, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (201, 200, 'button', '查看', 'questionnaire/examination/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (202, 200, 'button', '添加', 'questionnaire/examination/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (203, 200, 'button', '编辑', 'questionnaire/examination/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (204, 200, 'button', '预览', 'questionnaire/examination/preview', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (205, 200, 'button', '二维码', 'questionnaire/examination/getQrCode', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (206, 200, 'button', '更新二维码', 'questionnaire/examination/updateQrCode', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (207, 199, 'menu', '问题管理', 'questionnaire/question', 'questionnaire/question', 'fa fa-navicon', 'tab', '', '/src/views/backend/questionnaire/question/index.vue', 1, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (208, 207, 'button', '查看', 'questionnaire/question/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (209, 207, 'button', '添加', 'questionnaire/question/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (210, 207, 'button', '编辑', 'questionnaire/question/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (211, 207, 'button', '快速排序', 'questionnaire/question/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (212, 207, 'button', '获取题目', 'questionnaire/question/getQuestions', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (213, 199, 'menu', '答卷管理', 'questionnaire/answerSheet', 'questionnaire/answerSheet', 'el-icon-Document', 'tab', '', '/src/views/backend/questionnaire/answerSheet/index.vue', 1, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (214, 213, 'button', '查看', 'questionnaire/answerSheet/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (215, 213, 'button', '删除', 'questionnaire/answerSheet/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (216, 213, 'button', '阅卷', 'questionnaire/answerSheet/look', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (217, 199, 'menu', '答题分析', 'questionnaire/answer', 'questionnaire/answer', 'fa fa-bar-chart-o', 'tab', '', '/src/views/backend/questionnaire/answer/index.vue', 1, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (218, 217, 'button', '查看', 'questionnaire/answer/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (219, 217, 'button', '分析-填空', 'questionnaire/answer/gap', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (220, 217, 'button', '分析-单/多选', 'questionnaire/answer/analyse', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (221, 199, 'menu', '配置管理', 'questionnaire/config', 'questionnaire/config', 'fa fa-gears', 'tab', '', '/src/views/backend/questionnaire/config/index.vue', 1, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (222, 221, 'button', '查看', 'questionnaire/config/getConfig', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (223, 221, 'button', '修改', 'questionnaire/config/saveConfig', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764008247, 1764008247);
INSERT INTO `ra_admin_rule` VALUES (224, 0, 'menu_dir', 'AI管理', 'ai', 'ai', 'fa fa-dropbox', NULL, '', '', 1, 'none', '', 0, '1', 1764069630, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (225, 224, 'menu', '对话调试', 'ai/chat', 'ai/chat', 'fa fa-twitch', 'tab', '', '/src/views/backend/ai/chat/index.vue', 1, 'none', '', 101, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (226, 224, 'menu', '向量搜索', 'ai/search', 'ai/search', 'fa fa-search', 'tab', '', '/src/views/backend/ai/search/index.vue', 1, 'none', '', 100, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (227, 224, 'menu', '知识库配置', 'ai/config', 'ai/config', 'fa fa-gear', 'tab', '', '/src/views/backend/ai/config/index.vue', 1, 'none', '', 99, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (228, 227, 'button', '查看', 'ai/config/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (229, 227, 'button', '保存', 'ai/config/save', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (230, 227, 'button', '清理redis缓存和索引', 'ai/config/clear', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (231, 224, 'menu', '知识点管理', 'ai/kbsContent', 'ai/kbsContent', '', 'tab', '', '/src/views/backend/ai/kbsContent/index.vue', 0, 'none', '', 98, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (232, 231, 'button', '查看', 'ai/kbsContent/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (233, 231, 'button', '添加', 'ai/kbsContent/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (234, 231, 'button', '编辑', 'ai/kbsContent/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (235, 231, 'button', '删除', 'ai/kbsContent/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (236, 231, 'button', '快速排序', 'ai/kbsContent/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (237, 231, 'button', '重建索引', 'ai/kbsContent/indexSet', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (238, 231, 'button', '重建缓存', 'ai/kbsContent/checkCache', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (239, 224, 'menu', '知识库管理', 'ai/kbs', 'ai/kbs', 'fa fa-square-o', 'tab', '', '/src/views/backend/ai/kbs/index.vue', 1, 'none', '', 97, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (240, 239, 'button', '查看', 'ai/kbs/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (241, 239, 'button', '添加', 'ai/kbs/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (242, 239, 'button', '编辑', 'ai/kbs/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (243, 239, 'button', '删除', 'ai/kbs/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (244, 239, 'button', '快速排序', 'ai/kbs/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (245, 224, 'menu', '对话模型管理', 'ai/chatModel', 'ai/chatModel', 'fa fa-connectdevelop', 'tab', '', '/src/views/backend/ai/chatModel/indexSuspense.vue', 1, 'none', '', 96, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (246, 245, 'button', '查看', 'ai/chatModel/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (247, 245, 'button', '添加', 'ai/chatModel/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (248, 245, 'button', '编辑', 'ai/chatModel/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (249, 245, 'button', '删除', 'ai/chatModel/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (250, 245, 'button', '快速排序', 'ai/chatModel/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (251, 224, 'menu', '会话管理', 'ai/session', 'ai/session', 'fa fa-commenting-o', 'tab', '', '/src/views/backend/ai/session/index.vue', 1, 'none', '', 95, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (252, 251, 'button', '查看', 'ai/session/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (253, 251, 'button', '添加', 'ai/session/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (254, 251, 'button', '编辑', 'ai/session/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (255, 251, 'button', '删除', 'ai/session/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (256, 251, 'button', '快速排序', 'ai/session/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (257, 224, 'menu', '会话消息管理', 'ai/sessionMessage', 'ai/sessionMessage', '', 'tab', '', '/src/views/backend/ai/sessionMessage/index.vue', 1, 'none', '', 94, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (258, 257, 'button', '查看', 'ai/sessionMessage/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (259, 257, 'button', '添加', 'ai/sessionMessage/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (260, 257, 'button', '编辑', 'ai/sessionMessage/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (261, 257, 'button', '删除', 'ai/sessionMessage/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (262, 257, 'button', '快速排序', 'ai/sessionMessage/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (263, 224, 'menu', 'AI会员管理', 'ai/aiUser', 'ai/aiUser', 'fa fa-user-o', 'tab', '', '/src/views/backend/ai/aiUser/index.vue', 1, 'none', '', 93, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (264, 263, 'button', '查看', 'ai/aiUser/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (265, 263, 'button', '添加', 'ai/aiUser/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (266, 263, 'button', '编辑', 'ai/aiUser/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (267, 263, 'button', '删除', 'ai/aiUser/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (268, 263, 'button', '快速排序', 'ai/aiUser/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (269, 224, 'menu', 'AI会员tokens记录', 'ai/userTokens', 'ai/userTokens', '', 'tab', '', '/src/views/backend/ai/userTokens/index.vue', 1, 'none', '', 92, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (270, 269, 'button', '查看', 'ai/userTokens/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (271, 269, 'button', '添加', 'ai/userTokens/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764057542, 1764057542);
INSERT INTO `ra_admin_rule` VALUES (295, 0, 'menu_dir', '知识库管理', 'kb', 'kb', 'fa fa-book', NULL, '', '', 0, 'none', '知识库管理模块', 89, '1', 1764069614, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (296, 295, 'menu_dir', '基础配置', 'kb/basic', 'kb/basic', 'fa fa-cogs', NULL, '', '', 0, 'none', '知识库基础配置', 78, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (297, 296, 'menu', '类型管理', 'kb/type', 'kb/type', 'fa fa-tags', 'tab', '', '/src/views/backend/kb/type/index.vue', 1, 'none', '知识库类型管理', 77, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (298, 297, 'button', '查看', 'kb/type/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (299, 297, 'button', '添加', 'kb/type/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (300, 297, 'button', '编辑', 'kb/type/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (301, 297, 'button', '删除', 'kb/type/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (302, 297, 'button', '快速排序', 'kb/type/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (303, 296, 'menu', '分类管理', 'kb/category', 'kb/category', 'fa fa-folder', 'tab', '', '/src/views/backend/kb/category/index.vue', 1, 'none', '知识库分类管理', 76, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (304, 303, 'button', '查看', 'kb/category/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (305, 303, 'button', '添加', 'kb/category/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (306, 303, 'button', '编辑', 'kb/category/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (307, 303, 'button', '删除', 'kb/category/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (308, 303, 'button', '快速排序', 'kb/category/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (309, 296, 'menu', '标签管理', 'kb/tag', 'kb/tag', 'fa fa-tag', 'tab', '', '/src/views/backend/kb/tag/index.vue', 1, 'none', '知识库标签管理', 75, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (310, 309, 'button', '查看', 'kb/tag/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (311, 309, 'button', '添加', 'kb/tag/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (312, 309, 'button', '编辑', 'kb/tag/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (313, 309, 'button', '删除', 'kb/tag/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (314, 309, 'button', '快速排序', 'kb/tag/sortable', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (315, 295, 'menu_dir', '内容管理', 'kb/content', 'kb/content', 'fa fa-file-text-o', NULL, '', '', 0, 'none', '知识库内容管理', 74, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (316, 315, 'menu', '知识库管理', 'kb/kb', 'kb/kb', 'fa fa-book', 'tab', '', '/src/views/backend/kb/kb/index.vue', 1, 'none', '知识库主管理', 73, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (317, 316, 'button', '查看', 'kb/kb/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (318, 316, 'button', '添加', 'kb/kb/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (319, 316, 'button', '编辑', 'kb/kb/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (320, 316, 'button', '删除', 'kb/kb/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (321, 315, 'menu', '文章管理', 'kb/content/article', 'kb/content/article', 'fa fa-pencil', 'tab', '', '/src/views/backend/kb/content/index.vue', 1, 'none', '知识库文章内容管理', 72, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (322, 321, 'button', '查看', 'kb/content/article/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (323, 321, 'button', '添加', 'kb/content/article/add', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (324, 321, 'button', '编辑', 'kb/content/article/edit', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (325, 321, 'button', '删除', 'kb/content/article/del', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (326, 321, 'button', '置顶', 'kb/content/article/top', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (327, 321, 'button', '发布', 'kb/content/article/publish', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (328, 295, 'menu_dir', '统计分析', 'kb/statistics', 'kb/statistics', 'fa fa-bar-chart', NULL, '', '', 0, 'none', '知识库统计分析', 71, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (329, 328, 'menu', '访问统计', 'kb/statistics/views', 'kb/statistics/views', 'fa fa-eye', 'tab', '', '/src/views/backend/kb/statistics/views.vue', 1, 'none', '知识库访问统计', 70, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (330, 329, 'button', '查看', 'kb/statistics/views/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (331, 329, 'button', '导出', 'kb/statistics/views/export', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (332, 328, 'menu', '热门内容', 'kb/statistics/hot', 'kb/statistics/hot', 'fa fa-fire', 'tab', '', '/src/views/backend/kb/statistics/hot.vue', 1, 'none', '知识库热门内容统计', 69, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (333, 332, 'button', '查看', 'kb/statistics/hot/index', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (334, 332, 'button', '导出', 'kb/statistics/hot/export', '', '', NULL, '', '', 0, 'none', '', 0, '1', 1764067465000, 1764067465000);
INSERT INTO `ra_admin_rule` VALUES (335, 295, 'menu', '腾讯云知识库', 'TencentCloud', 'tencentCloud', 'fa fa-cloud', NULL, '', '', 0, 'none', '腾讯云知识库集成管理，作为知识库的子菜单', 91, '1', 1764069614, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (336, 335, 'menu', '配置管理', 'TencentConfig', 'tencentConfig', 'fa fa-cog', NULL, '', '/src/views/backend/kb/tencent-config/index.vue', 0, 'none', '腾讯云API配置，包括SecretId、SecretKey等认证信息管理', 88, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (337, 336, 'button', '配置列表', 'TencentConfig/index', '', 'fa fa-list', NULL, '', '', 0, 'none', '腾讯云配置列表', 87, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (338, 336, 'button', '添加配置', 'TencentConfig/add', '', 'fa fa-plus', NULL, '', '', 0, 'none', '添加腾讯云配置', 86, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (339, 336, 'button', '编辑配置', 'TencentConfig/edit', '', 'fa fa-edit', NULL, '', '', 0, 'none', '编辑腾讯云配置', 85, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (340, 336, 'button', '删除配置', 'TencentConfig/del', '', 'fa fa-trash', NULL, '', '', 0, 'none', '删除腾讯云配置', 84, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (341, 336, 'button', '测试连接', 'TencentConfig/testConnection', '', 'fa fa-plug', NULL, '', '', 0, 'none', '测试腾讯云连接', 83, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (342, 335, 'menu', '文件上传', 'TencentUpload', 'tencentUpload', 'fa fa-upload', NULL, '', '/src/views/backend/kb/tencent-cloud/index.vue', 0, 'none', '文件上传到腾讯云知识库，支持多种文档格式', 82, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (343, 342, 'button', '上传文件', 'TencentCloud/upload', '', 'fa fa-cloud-upload', NULL, '', '', 0, 'none', '上传文件到腾讯云', 81, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (344, 342, 'button', '上传列表', 'TencentCloud/uploadList', '', 'fa fa-list', NULL, '', '', 0, 'none', '上传任务列表', 80, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (345, 335, 'menu', '内容同步', 'TencentSync', 'tencentSync', 'fa fa-sync', NULL, '', '/src/views/backend/kb/tencent-cloud/index.vue', 0, 'none', '本地内容与腾讯云知识库的双向同步', 79, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (346, 345, 'button', '同步到腾讯云', 'TencentCloud/syncToTencent', '', 'fa fa-cloud-upload', NULL, '', '', 0, 'none', '同步内容到腾讯云', 78, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (347, 345, 'button', '从腾讯云下载', 'TencentCloud/downloadFromTencent', '', 'fa fa-cloud-download', NULL, '', '', 0, 'none', '从腾讯云下载内容', 77, '1', 1764068598, 1764068598);
INSERT INTO `ra_admin_rule` VALUES (348, 345, 'button', '同步记录', 'TencentCloud/syncList', '', 'fa fa-history', NULL, '', '', 0, 'none', '同步记录列表', 76, '1', 1764068598, 1764068598);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
