<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.method.quick Search Fields') })"
        ></TableHeader>

        <Table ref="tableRef" :key="tableKey"></Table>

        <!-- 示例核心代码(1/2) -->
        <div class="methods">
            <el-button v-blur @click="onSelectAll">全选/取消全选</el-button>
            <el-button v-blur @click="onSelectFirst">选中/取消选中第一行</el-button>

            <el-button v-blur @click="onSort">
                {{ !baTable.table.filter!.order || baTable.table.filter!.order == 'id,asc' ? '以 创建时间 字段排序' : '以 ID 字段排序' }}
            </el-button>

            <el-button v-blur @click="onClearSort">清空排序条件</el-button>
            <el-button v-blur @click="onPageLimit20">每页显示20条数据</el-button>

            <el-button v-blur @click="baTable.table.data![0].string += ' - 我被修改了'">
                <div class="ba-markdown">修改第一行的<code>字符串</code>显示值</div>
            </el-button>

            <el-button v-blur @click="onSwitch">
                <div class="ba-markdown">切换第一行的<code>开关</code></div>
            </el-button>

            <el-button v-blur @click="baTable.onTableHeaderAction('refresh', {})">刷新表格</el-button>

            <el-button v-blur @click="onPage">翻页</el-button>

            <el-button v-blur @click="onComSearchIdEq">
                <div class="ba-markdown">公共搜索<code>ID=1</code></div>
            </el-button>

            <el-button v-blur @click="onComSearchTimeEq">
                <div class="ba-markdown">公共搜索<code>时间日期=2023-12-12 06:06:06</code></div>
            </el-button>

            <el-button v-blur @click="onComSearchQuery">
                <div class="ba-markdown">带参自动公共搜索(请留意路由的 query)</div>
            </el-button>

            <el-button v-blur @click="onIdDisplay">
                <div class="ba-markdown">隐藏/显示<code>ID</code>列</div>
            </el-button>

            <el-button v-blur @click="onDelBtnId1">
                <div class="ba-markdown">隐藏<code>ID=1</code>的列<code>删除</code>操作按钮</div>
            </el-button>

            <el-button v-blur @click="onSelectableFirst">
                <div class="ba-markdown">禁止<code>ID=1</code>的列被选中</div>
            </el-button>

            <el-button v-blur @click="onSelectRow">获取选中行ID</el-button>

            <el-button v-blur @click="doLayout">重新布局表格(布局异常时)</el-button>
        </div>

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
import { ElNotification } from 'element-plus'
import { getArrayKey } from '/@/utils/common'
import { uuid } from '/@/utils/random'
import { useRouter } from 'vue-router'

defineOptions({
    name: 'examples/table/method',
})

const { t } = useI18n()
const tableRef = ref()
const tableKey = ref(uuid())
const router = useRouter()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * 示例核心代码(2/2)
 */
const onSelectAll = () => {
    let ref = tableRef.value.getRef()
    ref.toggleAllSelection()
}

const onSelectFirst = () => {
    let ref = tableRef.value.getRef()
    ref.toggleRowSelection(baTable.table.data![0])
}

const onSort = () => {
    let orderField = !baTable.table.filter!.order || baTable.table.filter!.order == 'id,asc'

    let ref = tableRef.value.getRef()
    ref.sort(orderField ? 'create_time' : 'id', orderField ? 'descending' : 'ascending')

    // 切换排序方式无需手动刷新表格
}

const onClearSort = () => {
    let ref = tableRef.value.getRef()
    ref.clearSort()

    // 清空后，记得手动刷新表格
    baTable.table.filter!.order = ''
    baTable.onTableHeaderAction('refresh', {})
}

const onPageLimit20 = () => {
    baTable.table.filter!.limit = 20

    // 修改每页数量后，记得刷新表格
    // 如果是在 baTable.getIndex() 之前修改，则修改后只需执行 baTable.getIndex()
    baTable.onTableHeaderAction('refresh', {})
}

const onSwitch = () => {
    const newValue = baTable.table.data![0].switch == 0 ? 1 : 0

    // 改变值
    // 值虽已改变，但单元格并不会重新渲染，所以还需要在 baTable.column 的列属性定义中使用 getRenderKey 自定义单元格渲染 key（目前仅 switch 和 buttons 字段不会自动重新渲染）
    baTable.table.data![0].switch = newValue

    // 开关的切换不仅仅需要改变值，同时需要请求服务端修改
    baTable.api.postData('edit', {
        id: baTable.table.data![0]['id'],
        switch: newValue,
    })
}

const onPage = () => {
    baTable.table.filter!.page = baTable.table.filter!.page == 1 || !baTable.table.filter!.page ? 2 : 1
    // 如果是在 baTable.getIndex() 之前修改，则修改后只需执行 baTable.getIndex()
    baTable.onTableHeaderAction('refresh', {})
}

const onComSearchIdEq = () => {
    // 展开公共搜索
    baTable.table.showComSearch = true

    // 范围搜索有两个输入框，使用逗号分割开始和结束值
    // 当前是范围搜索的等于示例，所以 start 和 end 均为1
    baTable.setComSearchData({ id: '1,1' })

    // 执行公共搜索
    baTable.onTableAction('com-search', {})
}

const onComSearchTimeEq = () => {
    // 展开公共搜索
    baTable.table.showComSearch = true

    /**
     * 公共搜索表单赋值
     */
    baTable.setComSearchData({ datetime: '2023-12-12 06:06:06' })

    // 执行公共搜索
    baTable.onTableAction('com-search', {})
}

const onComSearchQuery = () => {
    // 也就是说，url 中的 query，将被公共搜索组件读取，并自动应用至对应字段
    router.push({ query: { switch: '1' } })

    // window.location.href = '/#/admin/examples/table/method?switch=0'
}

const onIdDisplay = () => {
    baTable.table.column[1].show = !baTable.table.column[1].show
}

const onDelBtnId1 = () => {
    let btnIndex = getArrayKey(baTable.table.column, 'render', 'buttons') // 找到 render=buttons 的列

    // 按钮有 display 属性，控制按钮是否显示 (row: TableRow, field: TableColumn) => boolean
    baTable.table.column[btnIndex].buttons![1].display = (row: TableRow) => {
        return row.id != 1
    }

    // 按钮组使用了 v-memo 缓存，随意修改一下 key 触发按钮组的重新渲染使改动生效
    baTable.table.column[btnIndex].getRenderKey = (row, field, column, index) => {
        return index + uuid()
    }

    // 按钮还有 disabled 属性，控制是否禁用 (row: TableRow, field: TableColumn) => boolean
}

const onSelectableFirst = () => {
    baTable.table.column[0].selectable = (row: TableRow) => {
        return row.id != 1
    }

    // 此项修改，需要重新渲染表格，利用 :key 即可实现
    tableKey.value = uuid()
}

const onSelectRow = () => {
    let ids = '选中行的 ID 是：'
    baTable.table.selection?.map((val) => {
        ids += val.id + ','
    })
    ElNotification.success({
        message: ids,
    })
}

const doLayout = () => {
    let ref = tableRef.value.getRef()
    ref.doLayout()
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Method/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.method.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom', show: true },
            {
                label: t('examples.table.method.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.method.switch'),
                prop: 'switch',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('examples.table.method.switch 0'), '1': t('examples.table.method.switch 1') },
                // 自定义渲染 key，加入 row.switch
                getRenderKey(row) {
                    return 'switch' + row.id + row.switch
                },
            },
            { label: t('examples.table.method.datetime'), prop: 'datetime', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            {
                label: t('examples.table.method.create_time'),
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
        dblClickNotEditColumn: [undefined, 'switch'],
    },
    {
        defaultItems: { switch: '1', datetime: null },
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

<style scoped lang="scss">
.methods {
    margin: 20px;
    .el-button {
        margin-top: 10px;
    }
}
</style>
