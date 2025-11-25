<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('ai.userTokens.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, watch } from 'vue'
import baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { useRoute } from 'vue-router'

defineOptions({
    name: 'ai/userTokens',
})

const { t } = useI18n()
const tableRef = ref()
const route = useRoute()

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/ai.UserTokens/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('ai.userTokens.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('ai.userTokens.ai_user_id'),
                prop: 'ai_user_id',
                align: 'center',
                comSearchRender: 'remoteSelect',
                remote: {
                    pk: 'ai_user.id',
                    field: 'nickname',
                    remoteUrl: '/admin/ai.AiUser/index',
                },
            },
            {
                label: '绑定用户昵称',
                prop: 'aiUser.nickname',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: false,
            },
            { label: t('ai.userTokens.tokens'), prop: 'tokens', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('ai.userTokens.before'), prop: 'before', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('ai.userTokens.after'), prop: 'after', align: 'center', operator: 'RANGE', sortable: false },
            {
                label: t('ai.userTokens.memo'),
                prop: 'memo',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                showOverflowTooltip: true,
            },
            {
                label: t('ai.userTokens.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
        ],
        dblClickNotEditColumn: ['all'],
    },
    {
        defaultItems: { tokens: 0, ai_user_id: route.query.ai_user_id ? route.query.ai_user_id : 0 },
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

watch(
    () => route.query,
    () => {
        baTable.form.defaultItems!.ai_user_id = route.query.ai_user_id ? route.query.ai_user_id : 0
    }
)
</script>

<style scoped lang="scss"></style>
