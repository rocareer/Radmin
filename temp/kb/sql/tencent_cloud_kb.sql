-- 腾讯云知识库集成相关表结构

-- 腾讯云知识库配置表
DROP TABLE IF EXISTS `ra_kb_tencent_config`;
CREATE TABLE `ra_kb_tencent_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `secret_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '腾讯云SecretId',
  `secret_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '腾讯云SecretKey',
  `region` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ap-beijing' COMMENT '地域',
  `endpoint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '知识库API端点',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='腾讯云知识库配置';

-- 腾讯云知识库同步记录表
DROP TABLE IF EXISTS `ra_kb_tencent_sync`;
CREATE TABLE `ra_kb_tencent_sync` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `local_content_id` int unsigned NOT NULL COMMENT '本地内容ID',
  `tencent_doc_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '腾讯云文档ID',
  `tencent_doc_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '腾讯云文档标题',
  `tencent_doc_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '腾讯云文档URL',
  `sync_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '同步类型:1=上传到腾讯云,2=从腾讯云下载,3=双向同步',
  `sync_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '同步状态:0=待同步,1=同步中,2=同步成功,3=同步失败',
  `last_sync_time` bigint unsigned DEFAULT NULL COMMENT '最后同步时间',
  `sync_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '同步错误信息',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_local_content` (`local_content_id`),
  KEY `idx_tencent_doc_id` (`tencent_doc_id`),
  KEY `idx_sync_status` (`sync_status`),
  KEY `idx_last_sync_time` (`last_sync_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='腾讯云知识库同步记录';

-- 腾讯云知识库上传任务表
DROP TABLE IF EXISTS `ra_kb_tencent_upload`;
CREATE TABLE `ra_kb_tencent_upload` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `task_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务名称',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名',
  `file_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件类型',
  `tencent_doc_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '腾讯云文档ID',
  `upload_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '上传状态:0=待上传,1=上传中,2=上传成功,3=上传失败',
  `upload_progress` int unsigned NOT NULL DEFAULT '0' COMMENT '上传进度(0-100)',
  `upload_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传后的访问URL',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '错误信息',
  `admin_id` int unsigned DEFAULT NULL COMMENT '操作用户ID',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_upload_status` (`upload_status`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='腾讯云知识库上传任务';

-- 插入默认配置
INSERT INTO `ra_kb_tencent_config` (`name`, `region`, `endpoint`, `status`, `remark`, `create_time`) VALUES
('默认配置', 'ap-beijing', ' tcb.tencentcloudapi.com', 1, '腾讯云知识库默认配置', UNIX_TIMESTAMP());