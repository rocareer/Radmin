<!-- 封面频道顶部标题 -->
<template>
    <div>
        <div class="header">
            <div class="title">
                {{ props.data.info?.name }}
            </div>
            <el-breadcrumb class="breadcrumb" separator="/">
                <el-breadcrumb-item :to="{ name: 'cmsIndex' }">{{ $t('Home') }}</el-breadcrumb-item>
                <el-breadcrumb-item>{{ data.info?.name }}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    data: ArticleListData
    params: anyObj
}
const props = withDefaults(defineProps<Props>(), {
    data: () => {
        return {}
    },
    params: () => {
        return {}
    },
})
</script>

<style scoped lang="scss">
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    .title {
        font-weight: bold;
        font-size: var(--el-font-size-large);
        color: var(--el-text-color-primary);
        padding: 10px 0;
    }
    .breadcrumb {
        padding: 0 10px;
    }
}
</style>
