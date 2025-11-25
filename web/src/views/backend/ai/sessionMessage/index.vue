<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('ai.sessionMessage.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

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

defineOptions({
    name: 'ai/sessionMessage',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/ai.SessionMessage/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('ai.sessionMessage.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('ai.sessionMessage.type'),
                prop: 'type',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tag',
                operator: 'LIKE',
                sortable: false,
                replaceValue: {
                    text: t('ai.sessionMessage.type text'),
                    image: t('ai.sessionMessage.type image'),
                    link: t('ai.sessionMessage.type link'),
                },
            },
            {
                label: t('ai.sessionMessage.session__title'),
                prop: 'session.title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                showOverflowTooltip: true,
                operator: 'LIKE',
            },
            {
                label: t('ai.sessionMessage.sender_type'),
                prop: 'sender_type',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                width: 100,
                replaceValue: { ai: t('ai.sessionMessage.sender_type ai'), user: t('ai.sessionMessage.sender_type user') },
            },
            {
                label: t('ai.sessionMessage.sender_id'),
                prop: 'sender_id',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('ai.sessionMessage.tokens'), prop: 'tokens', align: 'center', operator: 'RANGE', sortable: false },
            {
                label: t('ai.sessionMessage.kbs'),
                prop: 'kbsTable.title',
                align: 'center',
                render: 'tags',
                operator: false,
                formatter(row, column, cellValue) {
                    const values = []
                    for (const key in cellValue) {
                        values.push(cellValue[key])
                    }
                    return values
                },
            },
            {
                label: t('ai.sessionMessage.kbstable__title'),
                prop: 'kbs',
                align: 'center',
                operator: 'FIND_IN_SET',
                show: false,
                comSearchRender: 'remoteSelect',
                remote: { pk: 'kbs_content.id', field: 'title', remoteUrl: '/admin/ai.KbsContent/index', multiple: true },
            },
            {
                label: t('ai.sessionMessage.consumption_tokens'),
                prop: 'consumption_tokens',
                align: 'center',
                operator: 'RANGE',
                sortable: false,
            },
            {
                label: t('ai.sessionMessage.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { type: 'text', sender_type: 'ai', tokens: 0, consumption_tokens: 0 },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
