<template>
    <div>
        <template v-for="(item, idx) in cmsConfig.right_sidebar" :key="idx">
            <template v-if="item.type == 'recommendContent' && item.data.length">
                <el-card class="right-sidebar-card" shadow="never">
                    <template #header>
                        <div class="card-header">{{ item.title }}</div>
                    </template>
                    <CmsBackgroundCard
                        round="4px"
                        :data="{
                            ...item.data[0],
                            title_style: {
                                color: item.data[0].title_style.color == '#000000' ? '#ffffff' : item.data[0].title_style.color,
                                bold: item.data[0].title_style.bold,
                            },
                        }"
                        height="auto"
                        class="suspension"
                    />
                    <CmsContentTitleRank :article-list="item.data.slice(1)" />
                </el-card>
            </template>
            <template v-if="item.type == 'ad'">
                <!-- 广告位 -->
                <div class="index-image-ad">
                    <a :href="item.data.link" :target="item.data.target">
                        <el-image fit="cover" loading="lazy" class="suspension" :src="getFirstImage(item.data.image)" :alt="item.data.title">
                            <template #placeholder>
                                <div class="img-loading-placeholder">
                                    <Loading />
                                </div>
                            </template>
                        </el-image>
                    </a>
                </div>
            </template>

            <template v-if="item.type == 'tags'">
                <!-- 热门标签 -->
                <el-card class="hot-tags-card right-sidebar-card" shadow="never">
                    <template #header>
                        <div class="card-header">热门标签</div>
                    </template>
                    <div v-for="(tag, tidx) in item.data" :key="tidx">
                        <NuxtLink :to="{ name: 'cmsChannel', params: { value: tag.id, type: 'tag' } }">
                            <el-tag :type="tag.type == 'default' ? 'primary' : tag.type">{{ tag.name }}</el-tag>
                        </NuxtLink>
                    </div>
                </el-card>
            </template>
        </template>
    </div>
</template>

<script setup lang="ts">
const cmsConfig = useCmsConfig()
</script>

<style scoped lang="scss">
.suspension {
    box-shadow: none;
    border-radius: 0;
}
.right-sidebar-card {
    border: none;
    border-radius: 0;
    margin-bottom: 10px;
    :deep(.el-card__header) {
        padding: 15px;
        border: none;
    }
    :deep(.el-card__body) {
        padding: 0 15px 5px 15px;
    }
}
.index-image-ad {
    margin-bottom: 8px;
    .el-image {
        width: 100%;
        min-height: 80px;
    }
}

.hot-tags-card {
    .el-tag {
        float: left;
        margin: 0 10px 10px 0;
        cursor: pointer;
    }
    .el-tag:hover {
        background-color: var(--el-color-primary);
        color: var(--el-color-white);
    }
}
</style>
