<template>
    <div>
        <el-tag class="m-4" v-for="(tag, idx) in props.renderValue" :key="idx">
            {{ tag }}
        </el-tag>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
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
.m-4 {
    margin: 4px;
}
</style>
