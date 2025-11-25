<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('kb.tag.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <Table ref="tableRef"></Table>

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'

defineOptions({
    name: 'kb/tag',
})

const { t } = useI18n()
const tableRef = useTemplateRef('tableRef')
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/kb.Tag/'),
    {
        pk: 'id',
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
        ],
        dblClickNotEditColumn: ['all'],
    },
    {
        defaultItems: { status: '1', color: '#1890ff', sort: 0, count: 0 },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>