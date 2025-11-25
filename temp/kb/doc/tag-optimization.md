# 标签页面优化 - 参照类型设计

## 优化内容
按照类型页面的设计模式，对标签页面进行全面优化。

## 具体修改

### 1. 列表页面优化 (`/web/src/views/backend/kb/tag/index.vue`)

#### 新增字段：
- **排序列**：在状态列后添加排序字段，支持数字排序和范围查询

#### 修复问题：
- **自动编辑问题**：将 `dblClickNotEditColumn: [undefined]` 改为 `dblClickNotEditColumn: ['all']`
- **默认值优化**：在 defaultItems 中添加 sort 默认值

#### 完整列配置：
```javascript
column: [
    { type: 'selection', align: 'center', operator: false },
    { label: t('kb.tag.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
    { label: t('kb.tag.name'), prop: 'name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
    { label: t('kb.tag.color'), prop: 'color', align: 'center', width: 100, operator: 'LIKE', sortable: false, render: 'color' },
    {
        label: t('kb.tag.status'),
        prop: 'status',
        align: 'center',
        width: 80,
        operator: 'eq',
        sortable: false,
        render: 'switch',
        replaceValue: { '0': t('kb.tag.status 0'), '1': t('kb.tag.status 1') },
    },
    { label: t('kb.tag.sort'), prop: 'sort', align: 'center', width: 80, operator: 'RANGE', sortable: 'custom' },
    { label: t('kb.tag.count'), prop: 'count', align: 'center', width: 80, operator: 'RANGE', sortable: 'custom' },
    {
        label: t('kb.tag.create_time'),
        prop: 'create_time',
        align: 'center',
        width: 160,
        operator: 'RANGE',
        sortable: 'custom',
        render: 'datetime',
    },
    { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
]
```

#### 默认值配置：
```javascript
defaultItems: { status: '1', color: '#1890ff', sort: 0, count: 0 }
```

### 2. 表单页面优化 (`/web/src/views/backend/kb/tag/popupForm.vue`)

#### 新增表单字段：
- **排序字段**：在状态字段后添加数字输入框，支持排序值设置

#### 表单字段配置：
```javascript
<FormItem
    :label="t('kb.tag.sort')"
    type="number"
    v-model="baTable.form.items!.sort"
    prop="sort"
    :placeholder="t('Please input field', { field: t('kb.tag.sort') })"
/>
```

### 3. 语言文件更新

#### 中文语言包 (`/web/src/lang/backend/zh-cn/kb/tag.ts`)：
```javascript
export default {
    id: 'ID',
    name: '标签名称',
    color: '标签颜色',
    status: '状态',
    'status 0': '禁用',
    'status 1': '启用',
    sort: '排序',
    count: '使用次数',
    create_time: '创建时间',
    update_time: '更新时间',
    'quick Search Fields': 'ID, 标签名称',
}
```

#### 英文语言包 (`/web/src/lang/backend/en/kb/tag.ts`)：
```javascript
export default {
    id: 'ID',
    name: 'Tag Name',
    color: 'Tag Color',
    status: 'Status',
    'status 0': 'Disabled',
    'status 1': 'Enabled',
    sort: 'Sort',
    count: 'Usage Count',
    create_time: 'Create Time',
    update_time: 'Update Time',
    'quick Search Fields': 'ID, Tag Name',
}
```

## 优化效果

### 功能增强：
- ✅ 排序功能：支持自定义标签排序顺序
- ✅ 状态管理：保持原有的 switch 状态切换功能
- ✅ 颜色展示：保持原有的颜色渲染功能
- ✅ 使用统计：显示标签使用次数

### 用户体验：
- ✅ 修复自动编辑问题，页面刷新不再误触编辑
- ✅ 默认值完整，包含状态、颜色、排序和使用次数
- ✅ Switch 组件操作流畅，状态实时更新
- ✅ 表单字段完整，数据录入便捷

### 一致性：
- ✅ 与类型、分类页面保持完全一致的设计模式
- ✅ 遵循 BuildAdmin 框架规范
- ✅ 国际化支持完整
- ✅ 列布局和字段顺序保持一致

## 标签页面特色功能

### 保留的原有功能：
- **颜色渲染**：标签颜色以色块形式展示
- **使用统计**：显示每个标签的使用次数
- **状态切换**：支持启用/禁用状态管理

### 新增功能：
- **排序管理**：支持自定义排序顺序
- **自动编辑修复**：解决页面刷新误触编辑问题

## 修改文件列表
1. `/web/src/views/backend/kb/tag/index.vue` - 列表页面优化
2. `/web/src/views/backend/kb/tag/popupForm.vue` - 表单页面优化  
3. `/web/src/lang/backend/zh-cn/kb/tag.ts` - 中文语言包
4. `/web/src/lang/backend/en/kb/tag.ts` - 英文语言包