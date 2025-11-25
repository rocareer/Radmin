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

 Date: 25/11/2025 18:30:00
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ra_kb_attachment
-- ----------------------------
DROP TABLE IF EXISTS `ra_kb_attachment`;
CREATE TABLE `ra_kb_attachment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `content_id` int unsigned NOT NULL COMMENT '内容ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名',
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原始文件名',
  `path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '访问URL',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'MIME类型',
  `size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(字节)',
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件扩展名',
  `sort` int unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_content_id` (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='知识库附件';

-- ----------------------------
-- Table structure for ra_kb_category
-- ----------------------------
DROP TABLE IF EXISTS `ra_kb_category`;
CREATE TABLE `ra_kb_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='知识库分类';

-- ----------------------------
-- Table structure for ra_kb_content
-- ----------------------------
DROP TABLE IF EXISTS `ra_kb_content`;
CREATE TABLE `ra_kb_content` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `kb_id` int unsigned NOT NULL COMMENT '知识库ID',
  `type_id` int unsigned DEFAULT NULL COMMENT '类型ID',
  `category_id` int unsigned DEFAULT NULL COMMENT '分类ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '内容',
  `excerpt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '摘要',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `author` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作者',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态:0=草稿,1=发布,2=审核中',
  `is_top` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶:0=否,1=是',
  `views` int unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `likes` int unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `publish_time` bigint unsigned DEFAULT NULL COMMENT '发布时间',
  `admin_id` int unsigned DEFAULT NULL COMMENT '创建者ID',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_kb_id` (`kb_id`),
  KEY `idx_type_id` (`type_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_publish_time` (`publish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='知识库内容';

-- ----------------------------
-- Table structure for ra_kb_content_tag
-- ----------------------------
DROP TABLE IF EXISTS `ra_kb_content_tag`;
CREATE TABLE `ra_kb_content_tag` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `content_id` int unsigned NOT NULL COMMENT '内容ID',
  `tag_id` int unsigned NOT NULL COMMENT '标签ID',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_content_tag` (`content_id`,`tag_id`),
  KEY `idx_content_id` (`content_id`),
  KEY `idx_tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='内容标签关联';

-- ----------------------------
-- Table structure for ra_kb_kb
-- ----------------------------
DROP TABLE IF EXISTS `ra_kb_kb`;
CREATE TABLE `ra_kb_kb` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `kb_type_id` int unsigned DEFAULT NULL COMMENT '类型',
  `kb_category_id` int unsigned DEFAULT NULL COMMENT '分类',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `admin_id` int unsigned DEFAULT NULL COMMENT '后台用户',
  `user_id` int unsigned DEFAULT NULL COMMENT '前台用户',
  `count` int DEFAULT NULL COMMENT '知识数量',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '修改时间',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='知识库管理';

-- ----------------------------
-- Table structure for ra_kb_tag
-- ----------------------------
DROP TABLE IF EXISTS `ra_kb_tag`;
CREATE TABLE `ra_kb_tag` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签名称',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1890ff' COMMENT '标签颜色',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `count` int unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='知识库标签';

-- ----------------------------
-- Table structure for ra_kb_type
-- ----------------------------
DROP TABLE IF EXISTS `ra_kb_type`;
CREATE TABLE `ra_kb_type` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `sort` int DEFAULT NULL COMMENT '权重',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='知识库类型';

SET FOREIGN_KEY_CHECKS = 1;