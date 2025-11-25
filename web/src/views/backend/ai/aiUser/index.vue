<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('ai.aiUser.quick Search Fields') })"
        >
            <template #comSearchUserId>
                <BaInput
                    type="remoteSelect"
                    v-model="baTable.comSearch.form.user_id"
                    :key="baTable.comSearch.form.user_type"
                    :attr="{
                        pk: baTable.comSearch.form.user_type == 'user' ? 'user.id' : 'admin.id',
                        field: 'nickname',
                        remoteUrl: baTable.comSearch.form.user_type == 'user' ? '/admin/user.User/index' : '/admin/auth.Admin/index',
                    }"
                    placeholder="请先选择绑定用户类型"
                />
            </template>
        </TableHeader>

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
import BaInput from '/@/components/baInput/index.vue'

defineOptions({
    name: 'ai/aiUser',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/ai.AiUser/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('ai.aiUser.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('ai.aiUser.user_type'),
                prop: 'user_type',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: { user: t('ai.aiUser.user_type user'), admin: t('ai.aiUser.user_type admin') },
            },
            {
                label: '绑定用户ID',
                prop: 'user_id',
                align: 'center',
                render: 'tag',
                operator: '=',
                show: false,
                comSearchRender: 'slot',
                comSearchSlotName: 'comSearchUserId',
            },
            {
                label: '绑定用户昵称',
                prop: 'nickname',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: false,
            },
            { label: t('ai.aiUser.tokens'), prop: 'tokens', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('ai.aiUser.messages'), prop: 'messages', align: 'center', operator: 'RANGE', sortable: 'custom' },
            {
                label: t('ai.aiUser.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('ai.aiUser.status 0'), '1': t('ai.aiUser.status 1') },
            },
            {
                label: t('ai.aiUser.last_use_time'),
                prop: 'last_use_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('ai.aiUser.create_time'),
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
        dblClickNotEditColumn: [undefined, 'status'],
    },
    {
        defaultItems: { user_type: 'user', tokens: 0, messages: 0, status: '1' },
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
