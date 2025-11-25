# 知识库系统设计文档

## 概述

知识库系统是基于现有框架开发的模块化知识管理平台，支持多知识库、分类管理、标签系统、附件管理等功能。

## 目录结构

```
temp/kb/                    # 临时工作目录
├── sql/                    # 数据库结构文件
│   └── r251124.sql        # 数据库表结构
├── doc/                    # 开发文档
│   ├── design.md          # 设计文档（本文件）
│   ├── api.md             # API接口文档
│   └── changelog.md       # 更新日志
└── backup/                # 备份文件

app/admin/controller/kb/    # 后台控制器目录
├── Kb.php                 # 知识库管理控制器
├── Content.php            # 内容管理控制器
├── Category.php           # 分类管理控制器
├── Tag.php                # 标签管理控制器
├── Type.php               # 类型管理控制器

app/admin/model/kb/         # 后台数据模型目录
├── Kb.php                 # 知识库模型
├── Content.php            # 内容模型
├── Category.php           # 分类模型
├── Tag.php                # 标签模型
├── Type.php               # 类型模型
└── Attachment.php         # 附件模型

web/src/views/backend/kb/   # 后台前端模板目录
├── kb/                    # 知识库管理页面
├── content/               # 内容管理页面
├── category/              # 分类管理页面
├── tag/                   # 标签管理页面
└── type/                  # 类型管理页面
```

## 数据库设计

### 核心表结构

#### 1. 知识库表 (ra_kb_kb)
- **用途**: 存储知识库基本信息
- **关键字段**:
  - `kb_type_id`: 知识库类型
  - `kb_category_id`: 知识库分类
  - `name`: 知识库名称
  - `admin_id`: 后台用户ID
  - `user_id`: 前台用户ID
  - `count`: 知识数量
  - `status`: 状态 (0=禁用,1=启用)

#### 2. 知识库内容表 (ra_kb_content)
- **用途**: 存储知识库具体内容
- **关键字段**:
  - `kb_id`: 所属知识库ID
  - `type_id`: 内容类型
  - `category_id`: 内容分类
  - `title`: 标题
  - `content`: 内容正文
  - `excerpt`: 摘要
  - `keywords`: 关键词
  - `status`: 状态 (0=草稿,1=发布,2=审核中)
  - `is_top`: 是否置顶
  - `views`: 浏览量
  - `likes`: 点赞数

#### 3. 分类表 (ra_kb_category)
- **用途**: 知识库分类管理
- **关键字段**:
  - `name`: 分类名称

#### 4. 类型表 (ra_kb_type)
- **用途**: 知识库类型管理
- **关键字段**:
  - `name`: 类型名称
  - `status`: 状态
  - `sort`: 排序

#### 5. 标签表 (ra_kb_tag)
- **用途**: 标签管理
- **关键字段**:
  - `name`: 标签名称
  - `color`: 标签颜色
  - `status`: 状态
  - `count`: 使用次数

#### 6. 内容标签关联表 (ra_kb_content_tag)
- **用途**: 内容与标签多对多关联
- **关键字段**:
  - `content_id`: 内容ID
  - `tag_id`: 标签ID

#### 7. 附件表 (ra_kb_attachment)
- **用途**: 内容附件管理
- **关键字段**:
  - `content_id`: 所属内容ID
  - `name`: 文件名
  - `path`: 文件路径
  - `url`: 访问URL
  - `mime_type`: MIME类型
  - `size`: 文件大小

## 功能模块

### 1. 知识库管理
- 知识库的增删改查
- 知识库状态管理
- 知识库权限控制

### 2. 内容管理
- 内容的增删改查
- 内容状态管理（草稿/发布/审核）
- 内容搜索功能
- 内容浏览量统计

### 3. 分类管理
- 分类的增删改查
- 分类层级结构

### 4. 标签管理
- 标签的增删改查
- 标签颜色设置
- 标签使用统计

### 5. 类型管理
- 类型的增删改查
- 类型排序功能

### 6. 附件管理
- 附件上传
- 附件预览
- 附件下载

## 开发规范

### 1. 命名规范
- 表名前缀: `ra_kb_`
- 控制器命名: PascalCase
- 模型命名: PascalCase
- 字段命名: snake_case

### 2. 代码规范
- 遵循PSR-4自动加载规范
- 使用ThinkPHP框架约定
- 控制器继承Backend基类
- 模型继承BaseModel基类

### 3. 数据库规范
- 主键统一使用 `id`
- 时间字段使用 `bigint` 类型存储时间戳
- 状态字段使用 `tinyint unsigned` 类型
- 必须添加适当的索引

## 版本信息

- **版本**: v1.0.0
- **更新日期**: 2025-11-25
- **开发者**: AI Assistant
- **框架**: ThinkPHP + Vue.js