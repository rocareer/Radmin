# 分类页面优化 - 参照类型设计

## 优化内容
按照类型页面的设计模式，对分类页面进行全面优化。

## 具体修改

### 1. 列表页面优化 (`/web/src/views/backend/kb/category/index.vue`)

#### 新增字段列：
- **状态列**：使用 switch 组件，支持直接切换状态
- **排序列**：支持数字排序和范围查询
- **创建时间列**：显示创建时间，支持时间范围查询

#### 修复问题：
- **自动编辑问题**：将 `dblClickNotEditColumn: [undefined]` 改为 `dblClickNotEditColumn: ['all']`
- **默认值设置**：添加 `defaultItems: { 'status': '1', 'sort': 0 }`

#### 完整列配置：
```javascript
column: [
    { type: 'selection', align: 'center', operator: false },
    { label: t('kb.category.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
    { label: t('kb.category.name'), prop: 'name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
    {
        label: t('kb.category.status'),
        prop: 'status',
        align: 'center',
        width: 80,
        operator: 'eq',
        sortable: false,
        render: 'switch',
        replaceValue: { '0': t('kb.category.status 0'), '1': t('kb.category.status 1') },
    },
    { label: t('kb.category.sort'), prop: 'sort', align: 'center', width: 80, operator: 'RANGE', sortable: 'custom' },
    { label: t('kb.category.kb_count'), prop: 'kb_count', align: 'center', width: 100, operator: false },
    {
        label: t('kb.category.create_time'),
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

### 2. 表单页面优化 (`/web/src/views/backend/kb/category/popupForm.vue`)

#### 新增表单字段：
- **状态字段**：使用 radio 组件，支持启用/禁用选择
- **排序字段**：数字输入框，支持排序值设置

#### 表单字段配置：
```javascript
<FormItem
    :label="t('kb.category.status')"
    type="radio"
    v-model="baTable.form.items!.status"
    prop="status"
    :data="{ content: { '1': t('kb.category.status 1'), '0': t('kb.category.status 0') } }"
/>
<FormItem
    :label="t('kb.category.sort')"
    type="number"
    v-model="baTable.form.items!.sort"
    prop="sort"
    :placeholder="t('Please input field', { field: t('kb.category.sort') })"
/>
```

### 3. 语言文件更新

#### 中文语言包 (`/web/src/lang/backend/zh-cn/kb/category.ts`)：
```javascript
export default {
    id: 'ID',
    name: '分类名称',
    status: '状态',
    'status 0': '禁用',
    'status 1': '启用',
    sort: '排序',
    kb_count: '知识库数量',
    create_time: '创建时间',
    'quick Search Fields': 'ID, 分类名称',
}
```

#### 英文语言包 (`/web/src/lang/backend/en/kb/category.ts`)：
```javascript
export default {
    id: 'ID',
    name: 'Category Name',
    status: 'Status',
    'status 0': 'Disabled',
    'status 1': 'Enabled',
    sort: 'Sort',
    kb_count: 'KB Count',
    create_time: 'Create Time',
    'quick Search Fields': 'ID, Category Name',
}
```

## 优化效果

### 功能增强：
- ✅ 状态管理：支持直接在列表页切换分类状态
- ✅ 排序功能：支持自定义排序顺序
- ✅ 时间显示：显示创建时间，支持时间筛选
- ✅ 搜索优化：支持状态筛选和排序查询

### 用户体验：
- ✅ 修复自动编辑问题，页面刷新不再误触编辑
- ✅ 默认状态为启用，符合业务预期
- ✅ Switch 组件操作流畅，状态实时更新
- ✅ 表单字段完整，数据录入便捷

### 一致性：
- ✅ 与类型页面保持完全一致的设计模式
- ✅ 遵循 BuildAdmin 框架规范
- ✅ 国际化支持完整

## 修改文件列表
1. `/web/src/views/backend/kb/category/index.vue` - 列表页面优化
2. `/web/src/views/backend/kb/category/popupForm.vue` - 表单页面优化  
3. `/web/src/lang/backend/zh-cn/kb/category.ts` - 中文语言包
4. `/web/src/lang/backend/en/kb/category.ts` - 英文语言包