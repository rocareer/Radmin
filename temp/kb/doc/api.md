# 知识库系统 API 接口文档

## 概述

本文档描述了知识库系统的所有API接口，包括请求参数、响应格式和错误处理。

## 通用响应格式

### 成功响应
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        // 具体数据
    }
}
```

### 错误响应
```json
{
    "code": 0,
    "msg": "错误信息",
    "data": null
}
```

## 知识库管理接口

### 1. 获取知识库列表
- **URL**: `/admin/kb/kb/index`
- **Method**: GET
- **参数**:
  - `page`: 页码 (默认: 1)
  - `limit`: 每页数量 (默认: 10)
  - `keyword`: 搜索关键词
  - `kb_type_id`: 类型ID
  - `kb_category_id`: 分类ID
  - `status`: 状态
- **响应**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "kb_type_id": 1,
                "kb_category_id": 1,
                "name": "技术文档",
                "admin_id": 1,
                "user_id": null,
                "count": 10,
                "status": 1,
                "remark": "",
                "update_time": 1700870400,
                "create_time": 1700784000,
                "kbType": {
                    "id": 1,
                    "name": "技术文档"
                },
                "kbCategory": {
                    "id": 1,
                    "name": "技术"
                },
                "admin": {
                    "id": 1,
                    "username": "admin"
                }
            }
        ],
        "total": 1,
        "remark": "知识库管理"
    }
}
```

### 2. 添加知识库
- **URL**: `/admin/kb/kb/add`
- **Method**: POST
- **参数**:
  - `kb_type_id`: 类型ID
  - `kb_category_id`: 分类ID
  - `name`: 知识库名称
  - `admin_id`: 管理员ID
  - `user_id`: 用户ID
  - `status`: 状态
  - `remark`: 备注

### 3. 编辑知识库
- **URL**: `/admin/kb/kb/edit`
- **Method**: POST
- **参数**:
  - `id`: 知识库ID
  - 其他同添加接口

### 4. 删除知识库
- **URL**: `/admin/kb/kb/del`
- **Method**: DELETE
- **参数**:
  - `ids`: ID数组

## 内容管理接口

### 1. 获取内容列表
- **URL**: `/admin/kb/content/index`
- **Method**: GET
- **参数**:
  - `page`: 页码
  - `limit`: 每页数量
  - `keyword`: 搜索关键词
  - `kb_id`: 知识库ID
  - `type_id`: 类型ID
  - `category_id`: 分类ID
  - `status`: 状态
- **响应**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "kb_id": 1,
                "type_id": 1,
                "category_id": 1,
                "title": "文章标题",
                "excerpt": "文章摘要",
                "keywords": "关键词",
                "author": "作者",
                "status": 1,
                "is_top": 0,
                "views": 100,
                "likes": 10,
                "publish_time": 1700870400,
                "create_time": 1700784000,
                "update_time": 1700870400,
                "type": {
                    "id": 1,
                    "name": "技术文档"
                },
                "category": {
                    "id": 1,
                    "name": "技术"
                }
            }
        ],
        "total": 1
    }
}
```

### 2. 添加内容
- **URL**: `/admin/kb/content/add`
- **Method**: POST
- **参数**:
  - `kb_id`: 知识库ID
  - `type_id`: 类型ID
  - `category_id`: 分类ID
  - `title`: 标题
  - `content`: 内容
  - `excerpt`: 摘要
  - `keywords`: 关键词
  - `author`: 作者
  - `status`: 状态
  - `is_top`: 是否置顶
  - `publish_time`: 发布时间
  - `tag_ids`: 标签ID数组

### 3. 编辑内容
- **URL**: `/admin/kb/content/edit`
- **Method**: POST
- **参数**: 同添加接口，包含 `id`

### 4. 获取内容详情
- **URL**: `/admin/kb/content/detail`
- **Method**: GET
- **参数**:
  - `id`: 内容ID
- **响应**:
```json
{
    "code": 1,
    "data": {
        "id": 1,
        "title": "文章标题",
        "content": "文章内容",
        "tags": [
            {
                "id": 1,
                "name": "标签1",
                "color": "#1890ff"
            }
        ],
        "attachments": [
            {
                "id": 1,
                "name": "文件名",
                "url": "文件URL",
                "size": 1024
            }
        ]
    }
}
```

### 5. 更新浏览量
- **URL**: `/admin/kb/content/updateViews`
- **Method**: POST
- **参数**:
  - `id`: 内容ID

### 6. 搜索内容
- **URL**: `/admin/kb/content/search`
- **Method**: GET
- **参数**:
  - `keyword`: 搜索关键词
  - `limit`: 返回数量限制

## 分类管理接口

### 1. 获取分类列表
- **URL**: `/admin/kb/category/index`
- **Method**: GET
- **参数**:
  - `page`: 页码
  - `limit`: 每页数量
  - `keyword`: 搜索关键词

### 2. 添加分类
- **URL**: `/admin/kb/category/add`
- **Method**: POST
- **参数**:
  - `name`: 分类名称

### 3. 编辑分类
- **URL**: `/admin/kb/category/edit`
- **Method**: POST
- **参数**:
  - `id`: 分类ID
  - `name`: 分类名称

### 4. 删除分类
- **URL**: `/admin/kb/category/del`
- **Method**: DELETE
- **参数**:
  - `ids`: ID数组

## 标签管理接口

### 1. 获取标签列表
- **URL**: `/admin/kb/tag/index`
- **Method**: GET
- **参数**:
  - `page`: 页码
  - `limit`: 每页数量
  - `keyword`: 搜索关键词
  - `status`: 状态

### 2. 添加标签
- **URL**: `/admin/kb/tag/add`
- **Method**: POST
- **参数**:
  - `name`: 标签名称
  - `color`: 标签颜色
  - `status`: 状态

### 3. 编辑标签
- **URL**: `/admin/kb/tag/edit`
- **Method**: POST
- **参数**:
  - `id`: 标签ID
  - `name`: 标签名称
  - `color`: 标签颜色
  - `status`: 状态

### 4. 删除标签
- **URL**: `/admin/kb/tag/del`
- **Method**: DELETE
- **参数**:
  - `ids`: ID数组

## 类型管理接口

### 1. 获取类型列表
- **URL**: `/admin/kb/type/index`
- **Method**: GET
- **参数**:
  - `page`: 页码
  - `limit`: 每页数量
  - `keyword`: 搜索关键词
  - `status`: 状态

### 2. 添加类型
- **URL**: `/admin/kb/type/add`
- **Method**: POST
- **参数**:
  - `name`: 类型名称
  - `status`: 状态
  - `sort`: 排序

### 3. 编辑类型
- **URL**: `/admin/kb/type/edit`
- **Method**: POST
- **参数**:
  - `id`: 类型ID
  - `name`: 类型名称
  - `status`: 状态
  - `sort`: 排序

### 4. 删除类型
- **URL**: `/admin/kb/type/del`
- **Method**: DELETE
- **参数**:
  - `ids`: ID数组

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 0 | 通用错误 |
| 401 | 未授权 |
| 403 | 禁止访问 |
| 404 | 资源不存在 |
| 422 | 参数验证失败 |
| 500 | 服务器内部错误 |

## 注意事项

1. 所有接口都需要登录认证
2. 时间字段使用时间戳格式
3. 文件上传需要使用multipart/form-data格式
4. 删除操作会进行关联性检查
5. 搜索功能支持模糊匹配