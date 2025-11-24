<template>
    <div>
        <CmsCoverChannelTitle :data="props.data" :params="params" />

        <!-- 轮播图和焦点图 -->
        <CmsCoverChannelAd :data="data" :params="params" />

        <!-- 频道 -->
        <el-row class="news-channel" :gutter="10">
            <el-col v-for="(content, idx) in data.content" :key="idx" :md="12" :xs="24">
                <el-card class="news-channel-card" shadow="never">
                    <template #header>
                        <div class="card-header">
                            <span class="title">{{ content.info.name }}</span>
                            <NuxtLink :to="{ name: 'cmsChannel', params: { value: content.info.id } }" class="see-more">查看全部</NuxtLink>
                        </div>
                    </template>
                    <div class="news-channel-list">
                        <CmsLeftImageCard class="news-channel-left-image-card" :article="content.contentList[0]"></CmsLeftImageCard>
                        <NuxtLink
                            v-for="(item, cIdx) in content.contentList.slice(1)"
                            :key="cIdx"
                            class="content-title-item"
                            :to="{ name: 'cmsInfo', params: { id: item.id } }"
                        >
                            <div class="content-title-left">
                                <Icon name="fa fa-caret-right" size="14" color="var(--el-color-info-light-3)" />
                                <div class="content-title-content">
                                    {{ item.title }}
                                </div>
                            </div>
                            <div class="content-title-right hidden-xs-only">{{ item.create_time }}</div>
                        </NuxtLink>
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <!-- 友情链接 -->
        <CmsFriendlyLink class="friendly-link"></CmsFriendlyLink>
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
.news-channel-card {
    border: none;
    border-radius: 0;
    margin-top: 10px;
    .card-header {
        display: flex;
        justify-content: space-between;
        .title {
            font-size: 15px;
            font-weight: bold;
        }
        .see-more {
            font-size: var(--el-font-size-small);
            color: var(--el-color-info);
            &:hover {
                text-decoration: underline;
            }
        }
    }
    :deep(.el-card__header) {
        padding: 15px 15px 0 15px;
        border: none;
    }
    :deep(.el-card__body) {
        padding: 0 15px 0 15px;
    }
    a {
        color: var(--el-text-color-primary);
        text-decoration: none;
    }
    .news-channel-left-image-card {
        // 虚线
        border-bottom: 1px dashed var(--el-color-info-light-9);
    }
    .content-title-item {
        display: flex;
        align-items: center;
        padding: 8px;
        border-bottom: 1px dashed var(--el-color-info-light-9);
        line-height: 20px;
        .content-title-left {
            display: flex;
            align-items: center;
            .icon {
                margin-right: 3px;
            }
            .content-title-content {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                color: var(--el-text-color-primary);
                &:hover {
                    text-decoration: underline;
                }
            }
        }
        .content-title-right {
            font-size: var(--el-font-size-small);
            margin-left: auto;
            color: var(--el-color-info);
            white-space: nowrap;
            text-align: right;
            &:hover {
                text-decoration: underline;
            }
        }
    }
    .content-title-item:last-child {
        border-bottom: none;
    }
}
.friendly-link {
    margin-top: 10px;
}
</style>
