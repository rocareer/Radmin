# 知识库系统更新日志

## 2025-11-25

### 新增功能
- **腾讯云知识库集成**: 新增腾讯云知识库集成模块，支持本地文件上传管理和云端同步
  - 配置管理: 腾讯云API配置管理，支持多地域和连接测试
  - 文件上传: 支持拖拽上传，兼容 doc/docx/pdf/txt/md 格式
  - 内容同步: 本地内容与腾讯云知识库的双向同步
  - 任务管理: 上传任务和同步记录的完整管理

### 数据库更新
- 新增 `ra_kb_tencent_config` 表: 腾讯云配置管理
- 新增 `ra_kb_tencent_sync` 表: 同步记录管理  
- 新增 `ra_kb_tencent_upload` 表: 上传任务管理

### 文件更新
- 后端控制器:
  - `app/admin/controller/kb/TencentConfig.php` - 腾讯云配置管理
  - `app/admin/controller/kb/TencentCloud.php` - 腾讯云知识库管理
- 后端模型:
  - `app/admin/model/kb/TencentConfig.php` - 配置模型
  - `app/admin/model/kb/TencentSync.php` - 同步记录模型
  - `app/admin/model/kb/TencentUpload.php` - 上传任务模型
- 前端页面:
  - `web/src/views/backend/kb/tencent-config/index.vue` - 配置管理页面
  - `web/src/views/backend/kb/tencent-cloud/index.vue` - 腾讯云管理页面
- SQL文件:
  - `temp/kb/sql/tencent_cloud_kb.sql` - 腾讯云相关表结构
  - `temp/kb/sql/kb_menu_tencent_patch.sql` - 菜单权限补丁
- 文档:
  - `temp/kb/doc/tencent-cloud-integration.md` - 腾讯云集成文档

### 技术特性
- 支持多种文档格式上传 (doc/docx/pdf/txt/md)
- 实时上传进度显示
- 同步状态跟踪和错误处理
- 敏感信息安全处理 (SecretKey 隐藏显示)
- 响应式UI设计，支持拖拽操作

### 菜单结构调整
- 腾讯云知识库菜单调整为知识库的子菜单
- 菜单层级: 知识库 > 腾讯云知识库 > [配置管理/文件上传/内容同步]

### 待完善功能
- 腾讯云SDK集成 (当前为模拟实现)
- 队列处理机制
- 增量同步优化
- 批量操作支持
- 文档版本管理

## 2025-11-25 (之前版本)

### 基础功能
- 知识库类型管理
- 知识库分类管理  
- 知识库标签管理
- 知识库内容管理
- 附件管理

### 系统优化
- 修复自动编辑问题
- 统一设计模式
- 状态切换功能
- 国际化支持
- 权限菜单完善