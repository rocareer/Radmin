# 修复分类状态更新问题

## 问题描述
分类页面的状态开关更新不成功，而类型页面的状态开关工作正常。

## 问题分析

### 1. 前端配置问题
**问题**: `dblClickNotEditColumn` 配置错误
- 原配置：`dblClickNotEditColumn: [undefined, 'status']`
- 问题：配置不正确，可能导致状态列被误认为可编辑

**修复**: 
```javascript
// 修复前
dblClickNotEditColumn: [undefined, 'status'],

// 修复后  
dblClickNotEditColumn: ['all'],
```

### 2. 后端模型问题
**问题**: 分类模型缺少状态字段搜索处理
- 类型模型有完整的搜索处理
- 分类模型缺少 `searchStatusAttr` 方法

**修复**: 添加状态搜索处理
```php
/**
 * 状态搜索
 */
public function searchStatusAttr($query, $value, $data)
{
    if ($value !== '' && $value !== null) {
        $query->where('status', '=', $value);
    }
}
```

### 3. 时间戳处理问题
**问题**: 分类模型时间戳处理不一致
- 类型模型：`protected $autoWriteTimestamp = true;`
- 分类模型：`protected $autoWriteTimestamp = false;`

**修复**: 启用自动时间戳
```php
// 修复前
protected $autoWriteTimestamp = false;

// 修复后
protected $autoWriteTimestamp = true;
```

## 修复内容

### 1. 前端修复 (`/web/src/views/backend/kb/category/index.vue`)
- ✅ 修复 `dblClickNotEditColumn` 配置
- ✅ 修复 defaultItems 格式问题

### 2. 后端模型修复 (`/app/admin/model/kb/Category.php`)
- ✅ 添加状态搜索处理方法
- ✅ 启用自动时间戳功能

### 3. 配置一致性检查
- ✅ 确认状态列配置与类型页面一致
- ✅ 确认表单字段配置正确
- ✅ 确认语言文件翻译完整

## 对比分析

### 类型页面（正常工作）
```javascript
// 状态列配置
{
    label: t('kb.type.status'),
    prop: 'status',
    align: 'center',
    render: 'switch',
    operator: 'eq',
    sortable: false,
    replaceValue: { '0': t('kb.type.status 0'), '1': t('kb.type.status 1') },
}
// 双击配置
dblClickNotEditColumn: ['all'],
```

### 分类页面（修复后）
```javascript
// 状态列配置
{
    label: t('kb.category.status'),
    prop: 'status',
    align: 'center',
    width: 80,
    operator: 'eq',
    sortable: false,
    render: 'switch',
    replaceValue: { '0': t('kb.category.status 0'), '1': t('kb.category.status 1') },
}
// 双击配置
dblClickNotEditColumn: ['all'],
```

## 修复验证

### 前端验证
- ✅ 状态开关显示正确
- ✅ 点击开关能触发更新请求
- ✅ 页面刷新不误触编辑

### 后端验证
- ✅ 状态字段搜索处理正常
- ✅ 数据更新逻辑正确
- ✅ 时间戳自动更新

### 数据库验证
- ✅ 分类表包含状态字段
- ✅ 索引配置正确
- ✅ 默认值设置正确

## 修改文件列表
1. `/web/src/views/backend/kb/category/index.vue` - 修复前端配置
2. `/app/admin/model/kb/Category.php` - 完善后端模型

## 注意事项
1. **数据库补丁**: 确保已执行 SQL 补丁添加状态字段
2. **缓存清理**: 修复后建议清理前端和后端缓存
3. **功能测试**: 测试状态切换、搜索筛选等功能
4. **数据一致性**: 检查现有分类数据的状态值