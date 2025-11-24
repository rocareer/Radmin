<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('questionnaire.answer.quick Search Fields') })"
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
                <el-table-column prop="must" align="center" :label="t('questionnaire.answer.must')">
                    <template #default="scope">
                        <el-tag type="info" v-if="scope.row.must == '0'">{{ t('questionnaire.answer.must 0') }}</el-tag>
                        <el-tag type="success" v-if="scope.row.must == '1'">{{ t('questionnaire.answer.must 1') }}</el-tag>
                    </template>
                </el-table-column>
            </template>
            <template #type>
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column prop="type" align="center" :label="t('questionnaire.answer.type')">
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

        <!-- 分析-单，多选 -->
        <Analyse />
        <!-- 分析-填空题 -->
        <Gap />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, reactive } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

import Analyse from './analyse.vue'
import Gap from './gap.vue'
import { analyseApi } from '/@/api/backend/questionnaire/common'

defineOptions({
    name: 'questionnaire/answer',
})

const { t } = useI18n()
const tableRef = ref()
const state = reactive({
    //必答类型
    mustList: [
        { value: '0', label: t('questionnaire.answer.must 0') },
        { value: '1', label: t('questionnaire.answer.must 1') },
    ],
    //题目类型
    typeList: [
        { value: '0', label: t('questionnaire.answer.type 0') },
        { value: '1', label: t('questionnaire.answer.type 1') },
        { value: '2', label: t('questionnaire.answer.type 2') },
        { value: '3', label: t('questionnaire.answer.type 3') },
        { value: '4', label: t('questionnaire.answer.type 4') },
        // { value: '5', label: t('questionnaire.answer.type 5') },
        // { value: '6', label: t('questionnaire.answer.type 6') },
        // { value: '7', label: t('questionnaire.answer.type 7') },
    ],
})

let optButtons = defaultOptButtons([])

let newButton: OptButton[] = [
    {
        render: 'tipButton',
        name: 'info',
        title: 'questionnaire.answer.answer',
        type: 'primary',
        icon: 'fa fa-bar-chart-o',
        class: 'table-row-info',
        disabledTip: false,
        // 自定义点击事件
        click: (row: TableRow) => {
            baTable.form.extend!.title = row.title
            baTable.form.extend!.type = row.type
            baTable.form.extend!.must = row.must
            baTable.form.extend!.options = row.options.toString()
            baTable.form.loading = true
            const type = Number(row.type) // 转为数字
            if ([2, 3].includes(type)) {
                //简答题 填空题
                baTable.form.extend!.showGap = true
            } else {
                analyseFu()
            }
        },
    },
]
optButtons = newButton.concat(optButtons)

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/questionnaire.Answer/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            {
                label: t('questionnaire.answer.title'),
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                showOverflowTooltip: true,
            },
            {
                label: t('questionnaire.answer.num'),
                prop: 'num',
                align: 'center',
                operator: false,
                sortable: false,
            },
            {
                label: t('questionnaire.answer.type'),
                prop: 'type',
                align: 'center',
                operator: 'eq',
                sortable: false,
                render: 'slot',
                slotName: 'type',
                comSearchRender: 'slot',
                comSearchSlotName: 'type',
            },
            {
                label: t('questionnaire.answer.must'),
                prop: 'must',
                align: 'center',
                operator: 'eq',
                sortable: false,
                render: 'slot',
                slotName: 'must',
                comSearchRender: 'slot',
                comSearchSlotName: 'must',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: ['all'],
    },
    {
        defaultItems: {},
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
const analyseFu = () => {
    let params = {
        title: baTable.form.extend!.title,
        type: baTable.form.extend!.type,
        must: baTable.form.extend!.must,
        options: baTable.form.extend!.options,
    }
    analyseApi(params)
        .then((res) => {
            let yuan = res.data.yuan
            let optionYuan = {
                tooltip: {
                    trigger: 'item',
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                },

                series: [
                    {
                        type: 'pie',
                        radius: '50%',
                        label: {
                            show: true,
                            formatter: '{d}%',
                        },
                        data: yuan,
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 10,
                                shadowOffsetX: 20,
                                shadowColor: 'rgba(0, 0, 0, 0.5)',
                            },
                        },
                    },
                ],
            }

            let zhu = res.data.zhu
            let optionZhu = {
                xAxis: {
                    type: 'category',
                    data: zhu[0],
                },
                yAxis: {
                    type: 'value',
                },
                series: [
                    {
                        data: zhu[1],
                        type: 'bar',
                        label: {
                            show: true,
                            position: 'top',
                        },
                    },
                ],
            }
            baTable.form.extend!.optionYuan = optionYuan
            baTable.form.extend!.optionZhu = optionZhu
            baTable.form.extend!.showAnalyse = true
        })
        .catch((err) => {
            console.log(err)
        })
}
</script>

<style scoped lang="scss">
.chart {
    width: 100%;
    height: 400px;
}
</style>
