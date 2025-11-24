<template>
    <div>
        <NuxtLink class="title-rank-item" v-for="(item, idx) in props.articleList" :key="idx" :to="{ name: 'cmsInfo', params: { id: item.id } }">
            <span class="rank-icon">{{ idx + 1 }}</span>
            <span class="title">{{ item.title }}</span>
        </NuxtLink>
    </div>
</template>

<script setup lang="ts">
interface Props {
    articleList: any[]
}
const props = withDefaults(defineProps<Props>(), {
    articleList: () => [],
})
</script>

<style scoped lang="scss">
.title-rank-item {
    display: flex;
    align-items: center;
    font-size: 14px;
    margin: 12px 0;
    cursor: pointer;
    text-decoration: none;
    color: var(--el-text-color-primary);
    :hover {
        text-decoration: underline;
    }
    .rank-icon {
        display: block;
        height: 18px;
        width: 18px;
        line-height: 18px;
        background-color: var(--el-color-info-light-8);
        border-radius: 5px;
        text-align: center;
        font-size: 12px;
        margin-right: 4px;
    }
    .title {
        display: inline-block;
        width: calc(100% - 22px);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}
.title-rank-item:nth-child(-n + 3) {
    .rank-icon {
        color: var(--el-color-primary-light-9);
        background-image: linear-gradient(120deg, red, yellow);
    }
}
</style>
