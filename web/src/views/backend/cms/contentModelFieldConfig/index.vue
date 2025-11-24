<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.contentModelFieldConfig.quick Search Fields') })"
        >
            <FormItem
                v-if="baTable.comSearch.form.content_model_id"
                :label="t('cms.contentModelFieldConfig.content_model')"
                type="remoteSelect"
                v-model="baTable.comSearch.form.content_model_id"
                :input-attr="{
                    pk: 'content_model.id',
                    field: 'name',
                    remoteUrl: '/admin/cms.ContentModel/index',
                    onChange: onModelChange,
                }"
                class="table-header-operate model-remote-select"
            />
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, reactive, onMounted, markRaw } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { info } from '/@/api/backend/cms/contentModelFieldConfig'
import { useRoute } from 'vue-router'
import FormItem from '/@/components/formItem/index.vue'
import RenderType from './renderType.vue'
import RenderSwitch from './renderSwitch.vue'
import { cloneDeep } from 'lodash-es'

const { t } = useI18n()
const tableRef = ref()
const route = useRoute()
const optButtons = defaultOptButtons(['edit', 'delete'])
optButtons[1].display = (row) => {
    return !row.main_field
}

const baTable = new baTableClass(
    new baTableApi('/admin/cms.ContentModelFieldConfig/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.contentModelFieldConfig.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            { label: t('cms.contentModelFieldConfig.content_model_id'), prop: 'content_model_id', align: 'center', operator: '=', show: false },
            {
                label: t('cms.contentModelFieldConfig.cms_content_model__name'),
                prop: 'cmsContentModel.name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
                replaceValue: {},
            },
            {
                label: t('cms.contentModelFieldConfig.title'),
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.contentModelFieldConfig.name'),
                prop: 'name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                showOverflowTooltip: true,
            },
            {
                label: t('cms.contentModelFieldConfig.type'),
                prop: 'type',
                align: 'center',
                render: 'customRender',
                operator: '=',
                sortable: false,
                customRender: markRaw(RenderType),
            },
            {
                label: t('cms.contentModelFieldConfig.frontend_filter'),
                prop: 'frontend_filter',
                align: 'center',
                render: 'switch',
                operator: '=',
                sortable: false,
                width: 90,
                replaceValue: { 0: t('cms.contentModelFieldConfig.frontend_filter 0'), 1: t('cms.contentModelFieldConfig.frontend_filter 1') },
            },
            {
                width: 90,
                label: t('cms.contentModelFieldConfig.frontend_contribute'),
                prop: 'frontend_contribute',
                align: 'center',
                render: 'customRender',
                operator: '=',
                sortable: false,
                replaceValue: {
                    0: t('cms.contentModelFieldConfig.frontend_contribute 0'),
                    1: t('cms.contentModelFieldConfig.frontend_contribute 1'),
                },
                customRender: markRaw(RenderSwitch),
            },
            {
                width: 90,
                label: t('cms.contentModelFieldConfig.backend_publish'),
                prop: 'backend_publish',
                align: 'center',
                render: 'customRender',
                operator: '=',
                sortable: false,
                replaceValue: { 0: t('cms.contentModelFieldConfig.backend_publish 0'), 1: t('cms.contentModelFieldConfig.backend_publish 1') },
                customRender: markRaw(RenderSwitch),
            },
            {
                width: 90,
                label: t('cms.contentModelFieldConfig.backend_show'),
                prop: 'backend_show',
                align: 'center',
                render: 'customRender',
                operator: '=',
                sortable: false,
                replaceValue: { 0: t('cms.contentModelFieldConfig.backend_show 0'), 1: t('cms.contentModelFieldConfig.backend_show 1') },
                customRender: markRaw(RenderSwitch),
            },
            {
                width: 90,
                label: t('cms.contentModelFieldConfig.backend_com_search'),
                prop: 'backend_com_search',
                align: 'center',
                render: 'customRender',
                operator: '=',
                sortable: false,
                replaceValue: { 0: t('cms.contentModelFieldConfig.backend_com_search 0'), 1: t('cms.contentModelFieldConfig.backend_com_search 1') },
                customRender: markRaw(RenderSwitch),
            },
            {
                width: 70,
                label: t('cms.contentModelFieldConfig.backend_sort'),
                prop: 'backend_sort',
                align: 'center',
                render: 'customRender',
                operator: '=',
                sortable: false,
                replaceValue: { 0: t('cms.contentModelFieldConfig.backend_sort 0'), 1: t('cms.contentModelFieldConfig.backend_sort 1') },
                customRender: markRaw(RenderSwitch),
            },
            { label: t('Weigh'), prop: 'weigh', align: 'center', operator: 'RANGE', sortable: 'custom' },
            {
                width: 70,
                label: t('cms.contentModelFieldConfig.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: '=',
                sortable: false,
                replaceValue: { 0: t('cms.contentModelFieldConfig.status 0'), 1: t('cms.contentModelFieldConfig.status 1') },
            },
            {
                label: t('cms.contentModelFieldConfig.update_time'),
                prop: 'update_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
                show: false,
            },
            {
                label: t('cms.contentModelFieldConfig.create_time'),
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
        dblClickNotEditColumn: [
            undefined,
            'frontend_filter',
            'frontend_contribute',
            'backend_publish',
            'backend_com_search',
            'backend_sort',
            'status',
        ],
        defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: {
            frontend_filter: '0',
            frontend_contribute: '0',
            backend_publish: '1',
            backend_com_search: '0',
            backend_sort: '0',
            status: '1',
        },
    },
    {
        toggleForm: ({ operate }) => {
            if (operate == 'Add') {
                baTable.table.extend!.field = {
                    dict: `key1=value1
key2=value2`,
                }
            }
        },
        onSubmit: ({ formEl, operate, items }) => {
            const formItems = cloneDeep(items)

            // 表单验证通过后执行的api请求操作
            const submitCallback = () => {
                baTable.form.submitLoading = true
                formItems.input_extend = baTable.table.extend!.field.inputExtend
                delete baTable.table.extend!.field.inputExtend
                baTable.api
                    .postData(operate, { ...formItems, ...baTable.table.extend!.field })
                    .then((res) => {
                        baTable.onTableHeaderAction('refresh', {})
                        baTable.form.operateIds?.shift()
                        if (baTable.form.operateIds!.length > 0) {
                            baTable.toggleForm('edit', baTable.form.operateIds)
                        } else {
                            baTable.toggleForm()
                        }
                        baTable.runAfter('onSubmit', { res })
                    })
                    .finally(() => {
                        baTable.form.submitLoading = false
                    })
            }

            if (formEl) {
                baTable.form.ref = formEl
                formEl.validate((valid) => {
                    if (valid) {
                        submitCallback()
                    }
                })
            } else {
                submitCallback()
            }
            return false
        },
    },
    {
        requestEdit: ({ res }) => {
            baTable.table.extend!.dataEbak = {
                type: res.data.row.type,
                data_type: res.data.row.data_type,
                dict: res.data.row.dict,
            }
            baTable.table.extend!.field.name = res.data.row.name
            baTable.table.extend!.field.title = res.data.row.title
            baTable.table.extend!.field.type = res.data.row.type
            baTable.table.extend!.field.tip = res.data.row.tip
            baTable.table.extend!.field.rule = res.data.row.rule
            baTable.table.extend!.field.extend = res.data.row.extend
            baTable.table.extend!.field.inputExtend = res.data.row.input_extend
            baTable.table.extend!.field.dict = res.data.row.dict
        },
    }
)

provide('baTable', baTable)

const state: {
    info: { id?: string }
} = reactive({
    info: {},
})

const onModelChange = () => {
    getIndex(baTable.comSearch.form.content_model_id)
}

const getIndex = (id: string) => {
    info(id).then((res) => {
        state.info = res.data.info
        baTable.form.defaultItems!.content_model_id = res.data.info.id

        baTable.setComSearchData({ ...baTable.comSearch.form, content_model_id: res.data.info.id })
        baTable.table.filter!.search = baTable.getComSearchData()

        baTable.table.data = []
        baTable.getIndex()?.then(() => {
            baTable.initSort()
            baTable.dragSort()
        })
    })
}

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()

    getIndex(route.query.content_model_id ? (route.query.content_model_id as string) : '0')
})

defineOptions({
    name: 'cms/contentModelFieldConfig',
})
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 20px;
}
.model-remote-select {
    margin-bottom: 0;
    width: 140px;
}
</style>
