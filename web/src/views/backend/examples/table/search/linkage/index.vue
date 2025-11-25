<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.search.linkage.quick Search Fields') })"
        >
            <template #userNameSlot>
                <BaInput
                    type="remoteSelect"
                    v-model="baTable.comSearch.form['user.username']"
                    :attr="{ pk: 'user.username', field: 'username', remoteUrl: '/admin/user.User/index' }"
                />
            </template>
            <template #adminNameSlot>
                <BaInput
                    type="remoteSelect"
                    v-model="baTable.comSearch.form['admin.username']"
                    :attr="{ pk: 'admin.username', field: 'username', remoteUrl: '/admin/auth.Admin/index' }"
                />
            </template>

            <!-- 示例核心代码(1/2) -->
            <!-- 根据 type 值动态渲染远程组件 -->
            <!-- 请注意 :key 属性，key 值改变组件会自动重新渲染 -->
            <template #groupNameSlot>
                <BaInput
                    v-if="baTable.comSearch.form['type']"
                    :key="baTable.comSearch.form['type']"
                    type="remoteSelect"
                    v-model="baTable.comSearch.form['group_id']"
                    :attr="{
                        pk: 'id',
                        field: 'name',
                        remoteUrl: baTable.comSearch.form['type'] == 'user' ? '/admin/user.Group/index' : '/admin/auth.Group/index',
                    }"
                />
                <div v-else>
                    <el-tag type="danger" class="ba-center">选择类型后可用</el-tag>
                </div>
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
import { onMounted, provide, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import BaInput from '/@/components/baInput/index.vue'

defineOptions({
    name: 'examples/table/search/linkage',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.search.Linkage/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.search.linkage.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.search.linkage.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: { user: t('examples.table.search.linkage.type user'), admin: t('examples.table.search.linkage.type admin') },
                operatorPlaceholder: '请选择类型',
            },
            {
                label: '会员',
                prop: 'user.username',
                align: 'center',
                render: 'tags',
                operator: '=',
                comSearchRender: 'slot',
                comSearchSlotName: 'userNameSlot',
            },
            {
                label: '管理员',
                prop: 'admin.username',
                align: 'center',
                render: 'tags',
                operator: '=',
                comSearchRender: 'slot',
                comSearchSlotName: 'adminNameSlot',
            },
            {
                label: t('examples.table.search.linkage.group__name'),
                prop: 'group_id',
                align: 'center',
                render: 'tags',
                operator: '=',
                // 示例核心代码(2/2)
                // 使用 slot 自定义公共搜索渲染
                comSearchRender: 'slot',
                comSearchSlotName: 'groupNameSlot',
                formatter(row) {
                    return row.type == 'user' ? row.userGroup.name : row.adminGroup.name
                },
            },
            {
                label: t('examples.table.search.linkage.create_time'),
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
        defaultItems: { type: 'user' },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
