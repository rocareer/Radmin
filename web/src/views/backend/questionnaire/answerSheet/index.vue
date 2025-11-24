<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('questionnaire.answerSheet.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

        <!-- 查阅 -->
        <Look />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'

import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import Look from './look.vue'

defineOptions({
    name: 'questionnaire/answerSheet',
})

const { t } = useI18n()
const tableRef = ref()

let optButtons = defaultOptButtons(['delete'])

let newButton: OptButton[] = [
    {
        render: 'tipButton',
        name: 'info',
        title: 'questionnaire.answerSheet.look',
        type: 'primary',
        icon: 'fa fa-search-plus',
        class: 'table-row-info',
        disabledTip: false,
        // 自定义点击事件
        click: (row: TableRow) => {
            baTable.form.extend!.id = row.id
            baTable.form.extend!.showLook = true
        },
    },
]
optButtons = newButton.concat(optButtons)

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/questionnaire.AnswerSheet/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            {
                label: t('questionnaire.answerSheet.id'),
                prop: 'id',
                align: 'center',
                width: 70,
                operator: 'eq',
                sortable: 'custom',
                operatorPlaceholder: t('questionnaire.answerSheet.id'),
            },
            {
                label: t('questionnaire.answerSheet.title'),
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                showOverflowTooltip: true,
            },
            {
                label: t('questionnaire.answerSheet.platform'),
                prop: 'platform',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                operatorPlaceholder: t('Please select field'),
                replaceValue: {
                    '0': t('questionnaire.answerSheet.platform 0'),
                    '1': t('questionnaire.answerSheet.platform 1'),
                    '2': t('questionnaire.answerSheet.platform 2'),
                },
            },
            {
                label: t('questionnaire.answerSheet.user'),
                prop: 'user',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false,
            },
            {
                label: t('questionnaire.answerSheet.create_time'),
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
        defaultItems: { platform: '0' },
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
