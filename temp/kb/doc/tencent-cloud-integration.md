# 腾讯云知识库集成文档

## 功能概述

本模块为知识库系统集成了腾讯云知识库功能，支持本地文件上传管理和与腾讯云知识库的双向同步。

## 主要功能

### 1. 配置管理
- **腾讯云API配置**: 管理腾讯云SecretId、SecretKey等认证信息
- **地域设置**: 支持多个腾讯云地域选择
- **连接测试**: 验证腾讯云API连接状态

### 2. 文件上传
- **拖拽上传**: 支持拖拽文件到上传区域
- **格式支持**: 支持 doc/docx/pdf/txt/md 格式
- **大小限制**: 单个文件最大 50MB
- **上传进度**: 实时显示上传进度和状态
- **任务管理**: 查看和管理上传任务列表

### 3. 内容同步
- **上传到腾讯云**: 将本地知识库内容同步到腾讯云
- **从腾讯云下载**: 从腾讯云知识库下载内容到本地
- **双向同步**: 支持本地和云端内容的双向同步
- **同步记录**: 查看同步历史和状态

## 数据库结构

### 腾讯云配置表 (ra_kb_tencent_config)
```sql
CREATE TABLE `ra_kb_tencent_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '配置名称',
  `secret_id` varchar(255) NOT NULL DEFAULT '' COMMENT '腾讯云SecretId',
  `secret_key` varchar(255) NOT NULL DEFAULT '' COMMENT '腾讯云SecretKey',
  `region` varchar(50) NOT NULL DEFAULT 'ap-beijing' COMMENT '地域',
  `endpoint` varchar(255) NOT NULL DEFAULT '' COMMENT '知识库API端点',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
);
```

### 同步记录表 (ra_kb_tencent_sync)
```sql
CREATE TABLE `ra_kb_tencent_sync` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `local_content_id` int unsigned NOT NULL COMMENT '本地内容ID',
  `tencent_doc_id` varchar(255) NOT NULL DEFAULT '' COMMENT '腾讯云文档ID',
  `tencent_doc_title` varchar(255) NOT NULL DEFAULT '' COMMENT '腾讯云文档标题',
  `tencent_doc_url` varchar(500) NOT NULL DEFAULT '' COMMENT '腾讯云文档URL',
  `sync_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '同步类型:1=上传到腾讯云,2=从腾讯云下载,3=双向同步',
  `sync_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '同步状态:0=待同步,1=同步中,2=同步成功,3=同步失败',
  `last_sync_time` bigint unsigned DEFAULT NULL COMMENT '最后同步时间',
  `sync_error` text COMMENT '同步错误信息',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_local_content` (`local_content_id`),
  KEY `idx_tencent_doc_id` (`tencent_doc_id`),
  KEY `idx_sync_status` (`sync_status`)
);
```

### 上传任务表 (ra_kb_tencent_upload)
```sql
CREATE TABLE `ra_kb_tencent_upload` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `task_name` varchar(255) NOT NULL DEFAULT '' COMMENT '任务名称',
  `file_path` varchar(500) NOT NULL DEFAULT '' COMMENT '文件路径',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `file_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `file_type` varchar(50) NOT NULL DEFAULT '' COMMENT '文件类型',
  `tencent_doc_id` varchar(255) NOT NULL DEFAULT '' COMMENT '腾讯云文档ID',
  `upload_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '上传状态:0=待上传,1=上传中,2=上传成功,3=上传失败',
  `upload_progress` int unsigned NOT NULL DEFAULT '0' COMMENT '上传进度(0-100)',
  `upload_url` varchar(500) NOT NULL DEFAULT '' COMMENT '上传后的访问URL',
  `error_message` text COMMENT '错误信息',
  `admin_id` int unsigned DEFAULT NULL COMMENT '操作用户ID',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_upload_status` (`upload_status`),
  KEY `idx_admin_id` (`admin_id`)
);
```

## API 接口

### 配置管理
- `GET /admin/kb.TencentConfig/` - 获取配置列表
- `POST /admin/kb.TencentConfig/add` - 添加配置
- `PUT /admin/kb.TencentConfig/edit` - 编辑配置
- `DELETE /admin/kb.TencentConfig/del` - 删除配置
- `POST /admin/kb.TencentConfig/testConnection` - 测试连接

### 文件上传
- `POST /admin/kb.TencentCloud/upload` - 上传文件
- `GET /admin/kb.TencentCloud/uploadList` - 获取上传任务列表

### 内容同步
- `POST /admin/kb.TencentCloud/syncToTencent` - 同步内容到腾讯云
- `GET /admin/kb.TencentCloud/syncList` - 获取同步记录列表
- `POST /admin/kb.TencentCloud/downloadFromTencent` - 从腾讯云下载内容

## 前端页面

### 配置管理页面
- 路径: `/web/src/views/backend/kb/tencent-config/index.vue`
- 功能: 管理腾讯云API配置，支持增删改查和连接测试
- 菜单位置: 知识库 > 腾讯云知识库 > 配置管理

### 腾讯云管理页面
- 路径: `/web/src/views/backend/kb/tencent-cloud/index.vue`
- 功能: 文件上传、内容同步、任务管理
- 菜单位置: 知识库 > 腾讯云知识库

## 使用说明

### 1. 配置腾讯云API
1. 在腾讯云控制台获取 SecretId 和 SecretKey
2. 在系统中添加配置，填入相关信息
3. 选择合适的地域和API端点
4. 测试连接确保配置正确

### 2. 上传文件
1. 访问腾讯云管理页面
2. 拖拽或点击选择文件
3. 系统自动上传并创建任务记录
4. 在任务列表中查看上传状态

### 3. 同步内容
1. 在同步管理区域选择要同步的内容
2. 点击"同步内容到腾讯云"按钮
3. 系统创建同步任务并执行
4. 在同步记录中查看同步状态

## 注意事项

1. **安全性**: SecretKey 等敏感信息在编辑时会显示为 `******`，避免泄露
2. **文件限制**: 目前仅支持常见文档格式，单个文件不超过 50MB
3. **网络环境**: 确保服务器能够访问腾讯云API端点
4. **权限管理**: 需要为相关用户配置腾讯云知识库的访问权限

## 开发计划

1. **SDK集成**: 集成腾讯云官方SDK，替换模拟API调用
2. **队列处理**: 使用消息队列处理上传和同步任务
3. **增量同步**: 实现增量同步功能，提高同步效率
4. **批量操作**: 支持批量上传和同步操作
5. **版本管理**: 支持文档版本控制和历史记录

## 更新日志

- 2025-11-25: 创建腾讯云知识库集成模块
- 2025-11-25: 完成基础数据结构和前后端页面
- 2025-11-25: 实现文件上传和内容同步基础功能