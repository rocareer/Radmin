<template>
    <div class="double-column-card">
        <el-row v-for="(item, idx) in articles" :key="idx" :gutter="20" class="content-item-box">
            <el-col v-for="(article, index) in item" :key="index" :lg="12" :md="24">
                <CmsLeftImageCard :article="article"></CmsLeftImageCard>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
interface Props {
    articleList: any[]
}
const props = withDefaults(defineProps<Props>(), {
    articleList: () => [],
})

// 将 props.articleList 分隔为两个一组的数组
const articles = computed(() => {
    const list = props.articleList
    const result = []
    for (let i = 0; i < list.length; i += 2) {
        result.push(list.slice(i, i + 2))
    }
    return result
})
</script>

<style scoped lang="scss">
.content-item-box {
    border-bottom: 1px solid var(--el-border-color-extra-light);
}
</style>
