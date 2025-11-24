<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('questionnaire.examination.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

        <!-- 表单 -->
        <PopupForm />
        <!-- 预览 -->
        <Preview />
        <!-- 二维码 -->
        <Qrcode />
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
import Preview from './preview.vue'
import Qrcode from './qrcode.vue'
import { ElNotification } from 'element-plus'

defineOptions({
    name: 'questionnaire/examination',
})

const { t } = useI18n()
const tableRef = ref()
let optButtons = defaultOptButtons(['edit'])

let newButton: OptButton[] = [
    {
        render: 'tipButton',
        name: 'preview',
        title: 'questionnaire.examination.preview',
        type: 'primary',
        icon: 'fa fa-search-plus',
        class: 'table-row-info',
        disabledTip: false,
        // 自定义点击事件
        click: (row: TableRow) => {
            baTable.form.extend!.id = row.id
            baTable.form.extend!.showPreview = true
        },
    },
    {
        render: 'tipButton',
        name: 'link',
        title: 'questionnaire.examination.link',
        type: 'primary',
        icon: 'fa fa-unlink',
        class: 'table-row-info',
        disabledTip: false,
        // 自定义点击事件
        click: (row: TableRow) => {
            let link = row.link

            if (!link) {
                ElNotification({
                    message: t('questionnaire.examination.no_link'),
                    type: 'warning',
                })
            } else {
                navigator.clipboard.writeText(link).then(() => {
                    ElNotification({
                        message: t('questionnaire.examination.copy_success'),
                        type: 'success',
                    })
                })
            }
        },
    },
    {
        render: 'tipButton',
        name: 'qrcode',
        title: 'questionnaire.examination.qrcode',
        type: 'primary',
        icon: 'fa fa-qrcode',
        class: 'table-row-info',
        disabledTip: false,
        // 自定义点击事件
        click: (row: TableRow) => {
            baTable.form.extend!.id = row.id
            baTable.form.extend!.showQrcode = true
        },
    },
]
optButtons = newButton.concat(optButtons)

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/questionnaire.Examination/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            {
                label: t('questionnaire.examination.id'),
                prop: 'id',
                align: 'center',
                width: 70,
                operator: 'eq',
                sortable: 'custom',
                operatorPlaceholder: t('questionnaire.examination.id'),
            },
            {
                label: t('questionnaire.examination.title'),
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                showOverflowTooltip: true,
            },
            {
                label: t('questionnaire.examination.begin_time'),
                prop: 'begin_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('questionnaire.examination.end_time'),
                prop: 'end_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('questionnaire.examination.num'),
                prop: 'num',
                align: 'center',
                operator: false,
            },
            {
                label: t('questionnaire.examination.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                operatorPlaceholder: t('Please select field'),
                replaceValue: { '0': t('questionnaire.examination.status 0'), '1': t('questionnaire.examination.status 1') },
            },
            {
                label: t('questionnaire.examination.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 160, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'status', 'default'],
    },
    {
        defaultItems: { begin_time: null, end_time: null },
    },
    {},
    {
        onTableAction: ({ event, data }) => {
            if (event === 'field-change') {
                if (data.column.columnKey === 'default') {
                    setTimeout(function () {
                        baTable.onTableHeaderAction('refresh', {})
                    }, 500)
                }
            }
        },
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
