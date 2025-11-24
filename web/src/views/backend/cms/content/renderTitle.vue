<template>
    <div
        class="title"
        @click="onClickTitle(props.renderRow)"
        :style="{ fontWeight: props.renderRow['title_style'].bold ? 'bold' : 'normal', color: props.renderRow['title_style'].color }"
    >
        <el-tooltip placement="top" :content="props.renderValue">
            {{ truncate(props.renderValue, { length: 15 }) }}
        </el-tooltip>
    </div>
</template>

<script setup lang="ts">
import { truncate } from 'lodash-es'
import { TableColumnCtx } from 'element-plus'
import { onClickTitle } from './helper'

interface Props {
    renderValue: any // 单元格值
    renderRow: TableRow // 当前行数据
    renderField: TableColumn // 当前列数据
    renderColumn: TableColumnCtx<TableRow> // 当前列上下文数据
    renderIndex: number // 当前行号
}
const props = defineProps<Props>()
</script>

<style scoped lang="scss">
.title {
    cursor: pointer;
}
</style>
