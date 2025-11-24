<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.channel.quick Search Fields') })"
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

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['weigh-sort', 'edit', 'delete'])
const baTable = new baTableClass(
    new baTableApi('/admin/cms.Channel/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            {
                label: t('cms.channel.name'),
                align: 'left',
                width: '200',
                prop: 'name',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('cms.channel.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom', show: false },
            {
                label: t('cms.channel.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                custom: {
                    cover: 'success',
                    list: '',
                    link: 'info',
                },
                replaceValue: { cover: t('cms.channel.type cover'), list: t('cms.channel.type list'), link: t('cms.channel.type link') },
            },
            {
                label: t('cms.channel.template'),
                prop: 'template',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                sortable: false,
                width: 140,
                render: 'tag',
            },
            {
                label: t('cms.channel.content_model_id'),
                prop: 'content_model_id',
                align: 'center',
                operator: '=',
                show: false,
                operatorPlaceholder: '模型ID',
                enableColumnDisplayControl: false,
            },
            {
                label: t('cms.channel.cms_content_model__name'),
                prop: 'cmsContentModel.name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            {
                label: t('cms.channel.seotitle'),
                prop: 'seotitle',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                show: false,
            },
            {
                label: t('cms.channel.allow_visit_groups'),
                prop: 'allow_visit_groups',
                align: 'center',
                render: 'tag',
                operator: '=',
                sortable: false,
                replaceValue: { all: t('cms.channel.allow_visit_groups all'), user: t('cms.channel.allow_visit_groups user') },
            },
            { label: t('cms.channel.weigh'), prop: 'weigh', align: 'center', operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.channel.frontend_contribute'),
                prop: 'frontend_contribute',
                align: 'center',
                render: 'switch',
                operator: '=',
                sortable: false,
                replaceValue: { 0: t('cms.channel.frontend_contribute 0'), 1: t('cms.channel.frontend_contribute 1') },
            },
            {
                label: t('cms.channel.index_rec'),
                prop: 'index_rec',
                align: 'center',
                render: 'tag',
                operator: 'LIKE',
                sortable: false,
                show: false,
            },
            {
                label: t('cms.channel.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: '=',
                sortable: false,
                replaceValue: { 0: t('cms.channel.status 0'), 1: t('cms.channel.status 1') },
            },
            {
                label: t('cms.channel.update_time'),
                prop: 'update_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('cms.channel.create_time'),
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
        dblClickNotEditColumn: [undefined, 'frontend_contribute', 'status'],
        defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: {
            type: 'list',
            template: 'default',
            target: '_self',
            allow_visit_groups: 'all',
            frontend_contribute: '1',
            status: '1',
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

defineOptions({
    name: 'cms/channel',
})
</script>

<style scoped lang="scss"></style>
