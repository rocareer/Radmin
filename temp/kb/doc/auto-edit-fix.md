# 修复类型页面自动编辑问题

## 问题描述
类型页面刷新时会自动触发编辑操作，这不符合列表页面的预期行为。只有点击 switch 开关时才应该更新单条数据状态。

## 问题原因
`dblClickNotEditColumn` 配置错误：
- 原配置：`dblClickNotEditColumn: [undefined]`
- 问题：当设置为 `[undefined]` 时，系统可能误判所有列为可双击编辑

## 解决方案
将 `dblClickNotEditColumn` 配置改为：
```javascript
dblClickNotEditColumn: ['all']
```

## 修复效果
- 页面刷新时不再自动触发编辑
- 只有用户主动点击 switch 开关才会更新状态
- switch 组件正常工作，通过 `field-change` 事件更新单条数据

## 技术说明
- `['all']` 表示禁用所有列的双击编辑功能
- switch 组件通过 `onTableAction('field-change')` 更新数据，不依赖双击编辑
- 保持了列表页面的正常浏览体验

## 修改文件
- `/web/src/views/backend/kb/type/index.vue`