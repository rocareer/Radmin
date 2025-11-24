<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.comment.quick Search Fields') })"
        >
            <el-dropdown :hide-on-click="true" :disabled="baTable.table.selection!.length > 0 ? false : true">
                <el-button v-blur :disabled="baTable.table.selection!.length > 0 ? false : true" class="table-header-operate" type="primary">
                    <Icon name="fa fa-pencil" />
                    <span class="table-header-operate-text">状态</span>
                </el-button>
                <template #dropdown>
                    <el-dropdown-menu>
                        <el-dropdown-item @click="onChangeStatus('normal')">正常</el-dropdown-item>
                        <el-dropdown-item @click="onChangeStatus('unaudited')">待审核</el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

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
import { statusChange } from '/@/api/backend/cms/comment'

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['weigh-sort', 'edit', 'delete'])
const baTable = new baTableClass(
    new baTableApi('/admin/cms.Comment/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.comment.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.comment.user__username'),
                prop: 'user.username',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            {
                label: t('cms.comment.cms_content__title'),
                prop: 'cmsContent.title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            {
                label: t('cms.comment.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                replaceValue: { content: t('cms.comment.type content'), page: t('cms.comment.type page') },
            },
            { label: t('cms.comment.weigh'), prop: 'weigh', align: 'center', operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.comment.status'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                replaceValue: {
                    normal: t('cms.comment.status normal'),
                    unaudited: t('cms.comment.status unaudited'),
                    refused: t('cms.comment.status refused'),
                },
            },
            {
                label: t('cms.comment.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: { type: 'content', content: '', weigh: 0, status: 'normal' },
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

const onChangeStatus = (status: string) => {
    statusChange(baTable.getSelectionIds(), status).then(() => {
        baTable.onTableHeaderAction('refresh', {})
    })
}

defineOptions({
    name: 'cms/comment',
})
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
</style>
