<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('ai.kbsContent.quick Search Fields') })"
        >
            <template #refreshAppend>
                <el-button @click="onShowBatchAdd" v-blur class="table-header-operate" type="primary">
                    <Icon color="#ffffff" name="fa fa-plus" />
                    <span class="table-header-operate-text">批量添加</span>
                </el-button>
            </template>
            <el-popconfirm
                @confirm="onEmbeddings(baTable.table.selection!)"
                confirm-button-text="确定"
                :cancel-button-text="t('Cancel')"
                confirmButtonType="primary"
                :width="260"
                title="知识点状态将转为自动判定，确认向量化或重新向量化所选项吗？"
                :disabled="baTable.table.selection!.length > 0 ? false : true"
            >
                <template #reference>
                    <div class="mlr-12">
                        <el-tooltip content="向量化/重新向量化" placement="top">
                            <el-button
                                v-blur
                                :disabled="baTable.table.selection!.length > 0 ? false : true"
                                class="table-header-operate"
                                type="success"
                                :loading="state.embeddingsLoading"
                            >
                                <Icon color="#ffffff" name="fa fa-random" />
                                <span class="table-header-operate-text">向量化</span>
                            </el-button>
                        </el-tooltip>
                    </div>
                </template>
            </el-popconfirm>
            <el-popconfirm
                v-if="state.redis"
                @confirm="onIndexSet()"
                confirm-button-text="确定"
                :cancel-button-text="t('Cancel')"
                confirmButtonType="primary"
                :width="260"
                title="通常在初始化或配置变动后才需要手动创建索引，确定现在重建吗？"
            >
                <template #reference>
                    <div class="mlr-12">
                        <el-tooltip content="创建 redis 索引" placement="top">
                            <el-button v-blur class="table-header-operate" type="success" :loading="state.embeddingsLoading">
                                <Icon color="#ffffff" name="fa fa-database" />
                                <span class="table-header-operate-text">redis 索引</span>
                            </el-button>
                        </el-tooltip>
                    </div>
                </template>
            </el-popconfirm>
            <el-popconfirm
                v-if="state.redis"
                @confirm="onCheckCache()"
                confirm-button-text="确定"
                :cancel-button-text="t('Cancel')"
                confirmButtonType="primary"
                :width="260"
                title="检查状态为`可用`和`转换成功`记录的 redis 缓存状态，并重新建立缓存（检查已选择的记录或无选择时检查全部）确定吗？"
            >
                <template #reference>
                    <div class="mlr-12">
                        <el-tooltip content="检查 redis 缓存" placement="top">
                            <el-button v-blur class="table-header-operate" type="success" :loading="state.embeddingsLoading">
                                <Icon color="#ffffff" name="fa fa-bullseye" />
                                <span class="table-header-operate-text">检查 redis 缓存</span>
                            </el-button>
                        </el-tooltip>
                    </div>
                </template>
            </el-popconfirm>
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #statuSlot>
                <el-table-column align="center" prop="status" label="向量化状态" width="160">
                    <template #default="scope">
                        <el-button v-if="scope.row['embeddingsLoading']" :loading="true">正在向量化</el-button>
                        <el-tooltip v-else placement="top" :content="getStatusTip(scope.row)">
                            <el-tag class="cursor-p" :type="getStatusType(scope.row['status'])">
                                {{ getStatusValue(scope.row['status']) }}
                            </el-tag>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </template>
            <template #tokenSlot>
                <el-table-column sortable="custom" align="center" prop="tokens" label="token数" width="120">
                    <template #default="scope">
                        <el-tooltip
                            placement="top"
                            :disabled="scope.row.extend?.batch ? false : true"
                            :content="scope.row.extend?.batch ? '批处理的Token数共计' : ''"
                        >
                            <div class="cursor-p">
                                {{ scope.row['tokens'] }}
                            </div>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </template>
        </Table>

        <!-- 表单 -->
        <PopupForm />

        <!-- 批量添加 -->
        <BatchAdd />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, reactive } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import createAxios from '/@/utils/axios'
import { ElNotification, TagProps } from 'element-plus'
import { embeddings, checkCache, indexSet } from '/@/api/backend/ai/index'
import { getArrayKey } from '/@/utils/common'
import BatchAdd from './batchAdd.vue'

defineOptions({
    name: 'ai/kbsContent',
})

const state = reactive({
    redis: false,
    embeddingsLoading: false,
})

const { t } = useI18n()
const tableRef = ref()
let optButtons: OptButton[] = [
    {
        render: 'confirmButton',
        name: 'embedding',
        title: 'ai.kbsContent.exec embedding',
        type: 'primary',
        icon: 'fa fa-random',
        popconfirm: {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            confirmButtonType: 'primary',
            title: '知识点状态将转为自动判定，确认向量化或重新向量化所选项吗？',
            width: 260,
        },
        disabledTip: false,
        click(row) {
            onEmbeddings([row.id.toString()])
        },
        disabled(row) {
            return row.embeddingsLoading
        },
    },
]
optButtons = optButtons.concat(defaultOptButtons(['weigh-sort', 'edit', 'delete']))

// 重写api，超时时间去掉
const apiUrl = '/admin/ai.KbsContent/'
const api = new baTableApi(apiUrl)
api.postData = (action: string, data: anyObj) => {
    return createAxios(
        {
            url: api.actionUrl.has(action) ? api.actionUrl.get(action) : apiUrl + action,
            method: 'post',
            data: data,
            timeout: 0,
        },
        {
            showSuccessMessage: true,
        }
    )
}
api.edit = (params: anyObj) => {
    return createAxios({
        url: api.actionUrl.get('edit'),
        method: 'get',
        params: params,
        timeout: 0,
    })
}

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    api,
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('ai.kbsContent.id'), prop: 'id', align: 'center', operatorPlaceholder: 'ID', width: 70, operator: '=', sortable: 'custom' },
            { label: t('ai.kbsContent.ai_kbs_ids'), prop: 'aiKbs.name', align: 'center', render: 'tags', operator: false },
            {
                label: t('ai.kbsContent.aikbs__name'),
                prop: 'ai_kbs_ids',
                align: 'center',
                operator: 'FIND_IN_SET',
                show: false,
                comSearchRender: 'remoteSelect',
                remote: { pk: 'kbs.id', field: 'name', remoteUrl: '/admin/ai.Kbs/index', multiple: true },
            },
            {
                label: t('ai.kbsContent.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: 'custom',
                replaceValue: { qa: t('ai.kbsContent.type qa'), text: t('ai.kbsContent.type text') },
                width: 100,
            },
            {
                label: t('ai.kbsContent.title'),
                showOverflowTooltip: true,
                width: 260,
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
            },
            {
                label: t('ai.kbsContent.model'),
                prop: 'model',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: 'custom',
                showOverflowTooltip: true,
            },
            { label: t('ai.kbsContent.hits'), width: 140, prop: 'hits', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('ai.kbsContent.tokens'), prop: 'tokens', align: 'center', operator: 'RANGE', show: false },
            { label: t('ai.kbsContent.tokens'), render: 'slot', slotName: 'tokenSlot', operator: false },
            {
                show: false,
                label: t('ai.kbsContent.status'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: {
                    pending: t('ai.kbsContent.status pending'),
                    fail: t('ai.kbsContent.status fail'),
                    success: t('ai.kbsContent.status success'),
                    usable: t('ai.kbsContent.status usable'),
                    offline: t('ai.kbsContent.status offline'),
                },
            },
            {
                label: t('ai.kbsContent.status'),
                render: 'slot',
                slotName: 'statuSlot',
                operator: false,
            },
            {
                label: t('Update time'),
                prop: 'update_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('ai.kbsContent.create_time'),
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
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: { type: 'qa', weigh: 0, hits: 0, tokens: 0, text_type: 'query', content_source: 'input', status: 'auto' },
    },
    {},
    {
        requestEdit() {
            baTable.form.items!.status = 'auto'
        },
        getIndex({ res }) {
            state.redis = res.data.redis
            baTable.table.extend!.text_type_switch = res.data.text_type_switch
            baTable.table.extend!.max_embedding_tokens = res.data.max_embedding_tokens
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

const customStatusType: anyObj = { pending: 'info', fail: 'warning', success: 'primary', usable: 'success', offline: 'info' }
const getStatusType = (value: string): TagProps['type'] => {
    return customStatusType[value as keyof typeof customStatusType] ? customStatusType[value as keyof typeof customStatusType] : ''
}

const statusReplaceValue = {
    pending: t('ai.kbsContent.status pending'),
    fail: t('ai.kbsContent.status fail'),
    success: t('ai.kbsContent.status success'),
    usable: t('ai.kbsContent.status usable'),
    offline: t('ai.kbsContent.status offline'),
}
const getStatusValue = (value: string) => {
    return statusReplaceValue[value as keyof typeof statusReplaceValue]
}

const statusTipValue = {
    pending: '尚未做向量转换，请手动开始',
    fail: '向量转换失败',
    success: '向量转换成功',
    usable: '向量数据已经可用',
    offline: '下线',
}
const getStatusTip = (row: TableRow) => {
    const error = ['fail', 'success'].includes(row['status']) ? '：' + row['extend']['error'] : ''
    return statusTipValue[row['status'] as keyof typeof statusTipValue] + error
}

const onEmbeddings = (ids: string[] | TableRow[]) => {
    if (!ids.length) return

    let idStringArr: string[] = []
    if (typeof ids[0] != 'string') {
        for (const key in ids) {
            idStringArr.push((ids[key] as TableRow).id.toString())
        }
    } else {
        idStringArr = ids as string[]
    }

    if (idStringArr.length > 10) {
        ElNotification({
            title: '温馨提示',
            message: '请选择10条以内的数据向量化~',
        })
        return
    }

    for (const key in idStringArr) {
        const dataIndex = getArrayKey(baTable.table.data, 'id', idStringArr[key])
        if (dataIndex !== false) {
            baTable.table.data![dataIndex].embeddingsLoading = true
        }
    }

    state.embeddingsLoading = true
    embeddings(idStringArr).finally(() => {
        baTable.onTableHeaderAction('refresh', {})
        state.embeddingsLoading = false
        for (const key in idStringArr) {
            const dataIndex = getArrayKey(baTable.table.data, 'id', idStringArr[key])
            if (dataIndex !== false) {
                baTable.table.data![dataIndex].embeddingsLoading = false
            }
        }
    })
}

const onIndexSet = () => {
    state.embeddingsLoading = true
    indexSet()
        .then(() => {})
        .finally(() => {
            state.embeddingsLoading = false
        })
}

const onCheckCache = () => {
    checkCache(baTable.getSelectionIds())
        .then(() => {})
        .finally(() => {
            baTable.onTableHeaderAction('refresh', {})
            state.embeddingsLoading = false
        })
}

const onShowBatchAdd = () => {
    baTable.form.extend!.showBatchAdd = true
}
</script>

<style scoped lang="scss">
.cursor-p {
    cursor: pointer;
}
.table-header-operate {
    margin-left: 12px;
}
</style>
