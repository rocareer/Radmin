<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('questionnaire.question.quick Search Fields') })"
        >
            <template #must>
                <!-- 我是公共搜索的slot渲染内容 -->
                <el-select :placeholder="t('Please select field')" v-model="baTable.comSearch.form.must!" clearable>
                    <el-option v-for="item in state.mustList" :key="item.value" :label="item.label" :value="item.value" />
                </el-select>
            </template>
            <template #type>
                <!-- 我是公共搜索的slot渲染内容 -->
                <el-select :placeholder="t('Please select field')" v-model="baTable.comSearch.form.type!" clearable>
                    <el-option v-for="item in state.typeList" :key="item.value" :label="item.label" :value="item.value" />
                </el-select>
            </template>
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #must>
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column prop="must" align="center" :label="t('questionnaire.question.must')">
                    <template #default="scope">
                        <el-tag type="info" v-if="scope.row.must == '0'">{{ t('questionnaire.question.must 0') }}</el-tag>
                        <el-tag type="success" v-if="scope.row.must == '1'">{{ t('questionnaire.question.must 1') }}</el-tag>
                    </template>
                </el-table-column>
            </template>
            <template #type>
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column prop="type" align="center" :label="t('questionnaire.question.type')">
                    <template #default="scope">
                        <template v-for="item in state.typeList" :key="item.value">
                            <el-tag type="primary" v-if="scope.row.type === item.value">
                                {{ item.label }}
                            </el-tag>
                        </template>
                    </template>
                </el-table-column>
            </template>
        </Table>

        <!-- 表单 -->
        <PopupForm />
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
import { getConfig } from '/@/api/backend/questionnaire/common'

defineOptions({
    name: 'questionnaire/question',
})

const { t } = useI18n()
const tableRef = ref()

const state = reactive({
    //必答类型
    mustList: [
        { value: '0', label: t('questionnaire.question.must 0') },
        { value: '1', label: t('questionnaire.question.must 1') },
    ],
    //题目类型
    typeList: [
        { value: '0', label: t('questionnaire.question.type 0') },
        { value: '1', label: t('questionnaire.question.type 1') },
        { value: '2', label: t('questionnaire.question.type 2') },
        { value: '3', label: t('questionnaire.question.type 3') },
        { value: '4', label: t('questionnaire.question.type 4') },
        { value: '5', label: t('questionnaire.question.type 5') },
        { value: '6', label: t('questionnaire.question.type 6') },
        { value: '7', label: t('questionnaire.question.type 7') },
    ],
})

const optButtons: OptButton[] = defaultOptButtons(['weigh-sort', 'edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/questionnaire.Question/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            {
                label: t('questionnaire.question.id'),
                prop: 'id',
                align: 'center',
                width: 70,
                operator: 'eq',
                sortable: 'custom',
                operatorPlaceholder: t('questionnaire.question.id'),
            },
            {
                label: t('questionnaire.question.title'),
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                showOverflowTooltip: true,
            },
            {
                label: t('questionnaire.question.must'),
                prop: 'must',
                align: 'center',
                operator: 'eq',
                sortable: false,
                render: 'slot',
                slotName: 'must',
                comSearchRender: 'slot',
                comSearchSlotName: 'must',
                operatorPlaceholder: t('Please select field'),
            },
            {
                label: t('questionnaire.question.type'),
                prop: 'type',
                align: 'center',
                operator: 'eq',
                sortable: false,
                render: 'slot',
                slotName: 'type',
                comSearchRender: 'slot',
                comSearchSlotName: 'type',
                operatorPlaceholder: t('Please select field'),
            },
            {
                label: t('questionnaire.question.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                operatorPlaceholder: t('Please select field'),
                replaceValue: { '0': t('questionnaire.question.status 0'), '1': t('questionnaire.question.status 1') },
            },
            { label: t('questionnaire.question.weigh'), prop: 'weigh', align: 'center', operator: false, sortable: 'custom' },
            {
                label: t('questionnaire.question.create_time'),
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
        dblClickNotEditColumn: [undefined, 'status'],
        defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: { type: '0', must: '1', status: '1', file_num: 1, file_size: 1, weigh: 0 },
    }
)

provide('baTable', baTable)

baTable.before.toggleForm = ({ operate }: { operate: string }) => {
    if (operate == 'Add' || operate == 'Edit') {
        baTable.form.extend!.operate = operate
        //获取文件最大数量，大小
        getConfig().then((res) => {
            let other = res.data.questionnaire_other
            baTable.form.extend!.num = other.num
            baTable.form.extend!.size = other.size
            if (operate == 'Add') {
                baTable.form.items!.file_size = other.size
            }

            baTable.form.extend!.picture = other.picture.split(',')
            baTable.form.extend!.video = other.video.split(',')
            baTable.form.extend!.file = other.file.split(',')
        })
    }
}

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
