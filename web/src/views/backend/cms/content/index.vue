<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" :title="t('cms.content.table prompt')" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :key="tableKey"
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.content.quick Search Fields') })"
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
                        <el-dropdown-item @click="onChangeStatus('offline')">已下线</el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table :key="tableKey" ref="tableRef" />

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
import { isEmpty, trim } from 'lodash-es'
import { useRoute } from 'vue-router'
import { fields, statusChange } from '/@/api/backend/cms/content'
import { uuid } from '/@/utils/random'
import { columnBaseData } from './helper'
import createAxios from '/@/utils/axios'
import { buildValidatorData, buildValidatorParams } from '/@/utils/validate'

const { t } = useI18n()
const tableRef = ref()
const tableKey = ref(uuid())
const route = useRoute()
const modelTable = trim(route.path, '/').split('/')[3]
const optButtons = defaultOptButtons(['weigh-sort', 'edit', 'delete'])

fields(modelTable).then(({ data }) => {
    /**
     * 组装表格数据
     */

    //  列数据
    let column: TableColumn[] = [{ type: 'selection', align: 'center', operator: false }]

    // 表单验证规则数据
    let rules: anyObj = {
        title: [buildValidatorData({ name: 'required', title: t('cms.content.title') })],
        channel_id: [buildValidatorData({ name: 'required', title: t('cms.content.channel_id') })],
        content: [buildValidatorData({ name: 'editorRequired', title: t('cms.content.content') })],
        url: [buildValidatorData({ name: 'url', title: t('cms.content.url') })],
        views: [buildValidatorData({ name: 'number', title: t('cms.content.views') })],
        comments: [buildValidatorData({ name: 'number', title: t('cms.content.comments') })],
        likes: [buildValidatorData({ name: 'number', title: t('cms.content.likes') })],
        dislikes: [buildValidatorData({ name: 'number', title: t('cms.content.dislikes') })],
        publish_time: [buildValidatorData({ name: 'date', title: t('cms.content.publish_time') })],
        update_time: [buildValidatorData({ name: 'date', title: t('cms.content.update_time') })],
        create_time: [buildValidatorData({ name: 'date', title: t('cms.content.create_time') })],
    }

    let defaultItems: anyObj = {}
    for (const key in data.fields) {
        if (columnBaseData.has(data.fields[key].full_name)) {
            let columnData = columnBaseData.get(data.fields[key].full_name)
            let columnDataComSearch = columnBaseData.get(data.fields[key].full_name + '-com-search')

            // 根据数据开关状态，处理后台公共搜索、排序、显示状态
            if (!data.fields[key].backend_com_search || columnDataComSearch) columnData!.operator = false
            columnData!.sortable = data.fields[key].backend_sort == 1 ? 'custom' : false
            columnData!.show = data.fields[key].backend_show == 1 ? true : false

            // 合并开发者自定义的列属性
            columnData = { ...columnData, ...data.fields[key].backend_column_attr }

            column.push(columnData!)
            if (columnDataComSearch) column.push(columnDataComSearch!)
        } else {
            let columnData = {
                label: data.fields[key].title,
                prop: data.fields[key].full_name,
                operator: data.fields[key].backend_com_search ? '=' : false,
                sortable: data.fields[key].backend_sort == 1 ? 'custom' : false,
                show: data.fields[key].backend_show == 1 ? true : false,
                align: 'center',
                'column-key': data.fields[key].full_name,
                ...data.fields[key].backend_column_attr,
            }
            column.push(columnData)
        }

        if (!data.fields[key].main_field) {
            defaultItems[data.fields[key].full_name] = data.fields[key].default

            if (data.fields[key].rule) {
                let ruleStr = data.fields[key].rule.split(',')
                let ruleArr: anyObj = []
                ruleStr.forEach((item: string) => {
                    ruleArr.push(
                        buildValidatorData({
                            name: item as buildValidatorParams['name'],
                            title: data.fields[key].title,
                        })
                    )
                })
                rules = Object.assign(rules, {
                    [data.fields[key].full_name]: ruleArr,
                })
            }
        }
    }
    column.push({ label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false })
    baTable.table.column = column
    baTable.form.defaultItems = { ...baTable.form.defaultItems, ...defaultItems }
    tableKey.value = uuid()
    baTable.initComSearch()
    if (!isEmpty(route.query)) {
        baTable.setComSearchData(route.query)
        baTable.onTableAction('com-search', {})
    }
    baTable.form.extend!.modelInfo = data
    baTable.form.extend!.rules = rules
})

const baTable: baTableClass = new baTableClass(
    new baTableApi('/admin/cms.Content/'),
    {
        pk: 'id',
        column: [],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: {
            flag: ['new'],
            content: '',
            target: '_blank',
            currency: 'RMB',
            allow_visit_groups: 'all',
            allow_comment_groups: 'user',
            status: 'normal',
            title_style: {
                color: '#000000',
                bold: false,
            },
        },
        labelWidth: 110,
    },
    {
        postDel: ({ ids }) => {
            baTable.api.del = () => {
                return createAxios(
                    {
                        url: baTable.api.actionUrl.get('del'),
                        method: 'DELETE',
                        params: {
                            ids: ids,
                            content_model_table: modelTable,
                        },
                    },
                    {
                        showSuccessMessage: true,
                    }
                )
            }
            baTable.api.del(ids).then((res) => {
                baTable.onTableHeaderAction('refresh', {})
                baTable.runAfter('postDel', { res })
            })
            return false
        },
        requestEdit: ({ id }) => {
            baTable.form.loading = true
            baTable.form.items = {}
            baTable.api
                .edit({
                    [baTable.table.pk!]: id,
                    content_model_table: modelTable,
                })
                .then((res) => {
                    baTable.form.items = res.data.row
                    baTable.runAfter('requestEdit', { res })
                })
                .catch((err) => {
                    baTable.toggleForm()
                    baTable.runAfter('requestEdit', { err })
                })
                .finally(() => {
                    baTable.form.loading = false
                })
            return false
        },
        onSubmit: ({ formEl, operate, items }) => {
            // 表单验证通过后执行的api请求操作
            const submitCallback = () => {
                baTable.form.submitLoading = true
                baTable.api
                    .postData(operate, { ...items, content_model_table: modelTable })
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
        onTableAction: ({ event, data }) => {
            if (event == 'field-change') {
                if (data.field.render == 'switch') {
                    if (!data.field || !data.field.prop) return
                    data.row.loading = true
                    baTable.api
                        .postData('edit', {
                            [baTable.table.pk!]: data.row[baTable.table.pk!],
                            [data.field.prop]: data.value,
                            content_model_table: modelTable,
                        })
                        .then(() => {
                            data.row[data.field.prop] = data.value
                        })
                        .finally(() => {
                            data.row.loading = false
                        })
                }
                return false
            }
        },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.table.filter!.table = modelTable
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
    })
})

const onChangeStatus = (status: string) => {
    statusChange(baTable.getSelectionIds(), status).then(() => {
        baTable.onTableHeaderAction('refresh', {})
    })
}

defineOptions({
    name: 'cms/content',
})
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
</style>
