<template>
    <div>
        <el-tag :type="props.renderRow.type == 'default' ? 'primary' : props.renderRow.type">{{ props.renderValue }}</el-tag>
    </div>
</template>

<script setup lang="ts">
interface Props {
    renderValue: any // 单元格值
    renderRow: TableRow // 当前行数据
}
const props = defineProps<Props>()
</script>

<style scoped lang="scss"></style>
