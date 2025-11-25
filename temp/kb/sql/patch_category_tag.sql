/*
 知识库分类和标签表结构补丁
 添加状态、排序、创建时间等缺失字段
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 为 ra_kb_category 表添加缺失字段
-- ----------------------------
ALTER TABLE `ra_kb_category` 
ADD COLUMN `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用' AFTER `name`,
ADD COLUMN `sort` int NOT NULL DEFAULT '0' COMMENT '排序' AFTER `status`,
ADD COLUMN `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间' AFTER `sort`,
ADD COLUMN `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间' AFTER `create_time`;

-- ----------------------------
-- 为 ra_kb_category 表添加索引
-- ----------------------------
ALTER TABLE `ra_kb_category` 
ADD INDEX `idx_status` (`status`),
ADD INDEX `idx_sort` (`sort`),
ADD INDEX `idx_create_time` (`create_time`);

-- ----------------------------
-- 为 ra_kb_tag 表添加排序字段
-- ----------------------------
ALTER TABLE `ra_kb_tag` 
ADD COLUMN `sort` int NOT NULL DEFAULT '0' COMMENT '排序' AFTER `count`;

-- ----------------------------
-- 为 ra_kb_tag 表添加索引
-- ----------------------------
ALTER TABLE `ra_kb_tag` 
ADD INDEX `idx_sort` (`sort`);

-- ----------------------------
-- 更新现有数据（如果存在）
-- ----------------------------
-- 为分类表设置默认值
UPDATE `ra_kb_category` SET 
    `status` = 1,
    `sort` = 0,
    `create_time` = UNIX_TIMESTAMP() * 1000,
    `update_time` = UNIX_TIMESTAMP() * 1000
WHERE `status` IS NULL OR `sort` IS NULL OR `create_time` IS NULL;

-- 为标签表设置默认排序值
UPDATE `ra_kb_tag` SET 
    `sort` = 0
WHERE `sort` IS NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- 补丁说明
-- ----------------------------
-- 1. ra_kb_category 表新增字段：
--    - status: 状态字段（0=禁用,1=启用），默认启用
--    - sort: 排序字段，默认0
--    - create_time: 创建时间
--    - update_time: 更新时间
--
-- 2. ra_kb_tag 表新增字段：
--    - sort: 排序字段，默认0
--
-- 3. 新增索引优化查询性能
--
-- 4. 兼容现有数据，自动设置默认值