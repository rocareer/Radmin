<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.friendlyLink.quick Search Fields') })"
        />

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

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['weigh-sort', 'edit', 'delete'])
const baTable = new baTableClass(
    new baTableApi('/admin/cms.FriendlyLink/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.friendlyLink.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.friendlyLink.user_id'),
                prop: 'user_id',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                show: false,
                operator: 'LIKE',
            },
            {
                label: t('cms.friendlyLink.user'),
                prop: 'user.username',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            {
                label: t('cms.friendlyLink.title'),
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.friendlyLink.link'),
                prop: 'link',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.friendlyLink.target'),
                prop: 'target',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                replaceValue: {
                    _blank: t('cms.friendlyLink.target _blank'),
                    _self: t('cms.friendlyLink.target _self'),
                    _top: t('cms.friendlyLink.target _top'),
                    _parent: t('cms.friendlyLink.target _parent'),
                },
            },
            { label: t('cms.friendlyLink.logo'), prop: 'logo', render: 'image', align: 'center', operator: false },
            { label: t('cms.friendlyLink.weigh'), prop: 'weigh', align: 'center', operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.friendlyLink.status'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                replaceValue: {
                    disable: t('cms.friendlyLink.status disable'),
                    enable: t('cms.friendlyLink.status enable'),
                    pending_trial: t('cms.friendlyLink.status pending_trial'),
                },
            },
            {
                label: t('cms.friendlyLink.update_time'),
                prop: 'update_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('cms.friendlyLink.create_time'),
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
        defaultItems: { target: '_blank', weigh: 0, status: 'enable' },
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

defineOptions({
    name: 'cms/friendlyLink',
})
</script>

<style scoped lang="scss"></style>
