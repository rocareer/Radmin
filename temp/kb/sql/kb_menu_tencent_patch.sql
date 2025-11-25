-- 腾讯云知识库菜单权限补丁

-- 插入腾讯云知识库主菜单 (作为知识库的子菜单，知识库ID为295)
INSERT INTO `ra_admin_rule` (`type`, `pid`, `name`, `title`, `path`, `icon`, `component`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
('menu', 295, 'TencentCloud', '腾讯云知识库', 'tencentCloud', 'fa fa-cloud', '', '腾讯云知识库管理', 89, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 使用变量存储菜单ID，避免嵌套查询错误
SET @tencent_cloud_id = (SELECT id FROM ra_admin_rule WHERE name = 'TencentCloud' LIMIT 1);

-- 插入配置管理菜单
INSERT INTO `ra_admin_rule` (`type`, `pid`, `name`, `title`, `path`, `icon`, `component`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
('menu', @tencent_cloud_id, 'TencentConfig', '配置管理', 'tencentConfig', 'fa fa-cog', '/src/views/backend/kb/tencent-config/index.vue', '腾讯云API配置管理', 88, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 使用变量存储配置管理菜单ID
SET @tencent_config_id = (SELECT id FROM ra_admin_rule WHERE name = 'TencentConfig' LIMIT 1);

-- 插入配置管理相关按钮
INSERT INTO `ra_admin_rule` (`type`, `pid`, `name`, `title`, `path`, `icon`, `component`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
('button', @tencent_config_id, 'TencentConfig/index', '配置列表', '', 'fa fa-list', '', '腾讯云配置列表', 87, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('button', @tencent_config_id, 'TencentConfig/add', '添加配置', '', 'fa fa-plus', '', '添加腾讯云配置', 86, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('button', @tencent_config_id, 'TencentConfig/edit', '编辑配置', '', 'fa fa-edit', '', '编辑腾讯云配置', 85, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('button', @tencent_config_id, 'TencentConfig/del', '删除配置', '', 'fa fa-trash', '', '删除腾讯云配置', 84, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('button', @tencent_config_id, 'TencentConfig/testConnection', '测试连接', '', 'fa fa-plug', '', '测试腾讯云连接', 83, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入文件上传菜单
INSERT INTO `ra_admin_rule` (`type`, `pid`, `name`, `title`, `path`, `icon`, `component`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
('menu', @tencent_cloud_id, 'TencentUpload', '文件上传', 'tencentUpload', 'fa fa-upload', '/src/views/backend/kb/tencent-cloud/index.vue', '腾讯云文件上传管理', 82, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 使用变量存储文件上传菜单ID
SET @tencent_upload_id = (SELECT id FROM ra_admin_rule WHERE name = 'TencentUpload' LIMIT 1);

-- 插入文件上传相关按钮
INSERT INTO `ra_admin_rule` (`type`, `pid`, `name`, `title`, `path`, `icon`, `component`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
('button', @tencent_upload_id, 'TencentCloud/upload', '上传文件', '', 'fa fa-cloud-upload', '', '上传文件到腾讯云', 81, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('button', @tencent_upload_id, 'TencentCloud/uploadList', '上传列表', '', 'fa fa-list', '', '上传任务列表', 80, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入内容同步菜单
INSERT INTO `ra_admin_rule` (`type`, `pid`, `name`, `title`, `path`, `icon`, `component`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
('menu', @tencent_cloud_id, 'TencentSync', '内容同步', 'tencentSync', 'fa fa-sync', '/src/views/backend/kb/tencent-cloud/index.vue', '腾讯云内容同步管理', 79, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 使用变量存储内容同步菜单ID
SET @tencent_sync_id = (SELECT id FROM ra_admin_rule WHERE name = 'TencentSync' LIMIT 1);

-- 插入内容同步相关按钮
INSERT INTO `ra_admin_rule` (`type`, `pid`, `name`, `title`, `path`, `icon`, `component`, `remark`, `weigh`, `status`, `create_time`, `update_time`) VALUES
('button', @tencent_sync_id, 'TencentCloud/syncToTencent', '同步到腾讯云', '', 'fa fa-cloud-upload', '', '同步内容到腾讯云', 78, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('button', @tencent_sync_id, 'TencentCloud/downloadFromTencent', '从腾讯云下载', '', 'fa fa-cloud-download', '', '从腾讯云下载内容', 77, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('button', @tencent_sync_id, 'TencentCloud/syncList', '同步记录', '', 'fa fa-history', '', '同步记录列表', 76, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 更新现有知识库菜单权重，确保腾讯云菜单显示在合适位置
UPDATE `ra_admin_rule` SET `weigh` = 100 WHERE `name` = 'Kb';

-- 添加菜单备注
UPDATE `ra_admin_rule` SET `remark` = '腾讯云知识库集成管理，作为知识库的子菜单' WHERE `name` = 'TencentCloud';
UPDATE `ra_admin_rule` SET `remark` = '腾讯云API配置，包括SecretId、SecretKey等认证信息管理' WHERE `name` = 'TencentConfig';
UPDATE `ra_admin_rule` SET `remark` = '文件上传到腾讯云知识库，支持多种文档格式' WHERE `name` = 'TencentUpload';
UPDATE `ra_admin_rule` SET `remark` = '本地内容与腾讯云知识库的双向同步' WHERE `name` = 'TencentSync';