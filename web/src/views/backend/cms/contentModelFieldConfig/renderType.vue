<template>
    <div>
        <span v-if="props.renderRow.main_field">-</span>
        <el-tag v-else>{{ props.renderRow.type }}</el-tag>
    </div>
</template>

<script setup lang="ts">
interface Props {
    renderRow: TableRow // 当前行数据
}
const props = defineProps<Props>()
</script>

<style scoped lang="scss"></style>
