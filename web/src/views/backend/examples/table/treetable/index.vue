<!-- 树状表格必看 -->
<!-- 1. 不需要分页，所以第 19 行实现了隐藏分页组件 -->
<!-- 2. 为了显示需要，通常将 `树状` 字段设置为第一个字段，并且该字段对齐方式为 `left`，请参考第 42 行 -->
<!-- 3. 请注意第 13 行，其中的 unfold 可以在表头增加一个 `展开/收缩` 树状表格的按钮 -->
<!-- 4. 本文件内，除以上注意事项外，树状表格与普通表格无差别 -->

<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.treetable.quick Search Fields') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

const tableRef = ref()
const { t } = useI18n()
const optButtons = defaultOptButtons(['edit', 'delete'])
const baTable = new baTableClass(new baTableApi('/admin/examples.table.Treetable/'), {
    column: [
        { type: 'selection', align: 'center', operator: false },
        { label: t('examples.table.treetable.name'), prop: 'name', operator: 'LIKE', align: 'left' },
        { label: t('Id'), prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query'), width: 70 },
        {
            label: t('Create time'),
            prop: 'create_time',
            align: 'center',
            render: 'datetime',
            sortable: 'custom',
            operator: 'RANGE',
            width: 160,
        },
        { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
    ],
    dblClickNotEditColumn: [undefined],
})

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()
})

defineOptions({
    name: 'examples/table/treetable',
})
</script>

<style scoped lang="scss"></style>
