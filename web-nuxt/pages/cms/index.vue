<template>
    <div class="index-container">
        <!-- 首页顶部广告位-s -->
        <template v-for="(item, idx) in state.data.indexTopBar" :key="idx">
            <a :href="item.link" :target="item.target">
                <el-image loading="lazy" class="ad-top-banner suspension" :src="item.image" :alt="item.title">
                    <template #placeholder>
                        <div class="img-loading-placeholder">
                            <Loading color="var(--el-color-info-light-7)" />
                        </div>
                    </template>
                </el-image>
            </a>
        </template>
        <!-- 首页顶部广告位-e -->

        <el-row class="main-row" :gutter="15">
            <el-col :lg="18">
                <!-- 首页轮播图和焦点图-s -->
                <el-row class="index-hot-row" :gutter="10">
                    <el-col :md="16" :xs="24">
                        <el-carousel height="360px">
                            <el-carousel-item v-for="(item, idx) in state.data.indexCarousel" :key="idx">
                                <div class="index-hot-img-item suspension">
                                    <a :href="item.link" :target="item.target">
                                        <el-image fit="cover" loading="lazy" :src="getFirstImage(item.image)" :alt="item.title">
                                            <template #placeholder>
                                                <div class="img-loading-placeholder">
                                                    <Loading />
                                                </div>
                                            </template>
                                        </el-image>
                                    </a>
                                    <div class="hot-title">{{ item.title }}</div>
                                </div>
                            </el-carousel-item>
                        </el-carousel>
                    </el-col>
                    <el-col :md="8" class="hidden-sm-and-down">
                        <CmsBackgroundCard
                            v-for="(item, idx) in state.data.indexFocus"
                            :key="idx"
                            class="hot-right-item suspension"
                            :data="{ title: item.title, images: item.image, url: item.link, target: item.target }"
                        />
                    </el-col>
                </el-row>
                <!-- 首页轮播图-e -->

                <!-- 最新更新（标记为最新的内容）-s -->
                <el-card class="new-box-card index-card" shadow="never">
                    <template #header>
                        <div class="card-header">最新更新</div>
                    </template>
                    <div>
                        <CmsDoubleColumnCard :article-list="state.data.newContent.slice(0, 2)" />
                        <div class="rec-content-title-list">
                            <NuxtLink
                                v-for="(item, idx) in state.data.newContent.slice(2)"
                                :key="idx"
                                class="rec-content-title-item"
                                :to="{ name: 'cmsInfo', params: { id: item.id } }"
                            >
                                <div class="rec-content-title-left">
                                    <Icon name="fa fa-caret-right" size="14" color="var(--el-color-info-light-3)" />
                                    <div class="rec-content-title-content">
                                        {{ item.title }}
                                    </div>
                                </div>
                                <div class="rec-content-title-right hidden-xs-only">{{ item.create_time }}</div>
                            </NuxtLink>
                        </div>
                        <div class="clear"></div>
                    </div>
                </el-card>
                <!-- 最新更新-e -->

                <!-- 最近更新（按更新时间排序）-s -->
                <el-card class="recently-box-card index-card" shadow="never">
                    <template #header>
                        <div class="card-header">
                            <div>最近更新</div>
                            <div class="hot-channels">
                                <NuxtLink
                                    v-for="(item, idx) in state.data.coverChannel"
                                    :key="idx"
                                    :to="{ name: 'cmsChannel', params: { value: item.id } }"
                                    class="cover-channel-item"
                                >
                                    {{ item.name }}
                                </NuxtLink>
                            </div>
                        </div>
                    </template>
                    <div>
                        <CmsDoubleColumnCard :article-list="state.data.newPublishContent" />
                        <div class="load-new-publish">
                            <el-button text v-blur @click="loadNewPublish" :disabled="state.newPublishNothingMore">
                                {{ state.newPublishNothingMore ? '没有更多了' : '加载更多' }}
                            </el-button>
                        </div>
                    </div>
                </el-card>
                <!-- 最近更新-e -->
            </el-col>
            <el-col :lg="6">
                <CmsRightSideBar />
            </el-col>
        </el-row>
        <CmsFriendlyLink></CmsFriendlyLink>
    </div>
</template>

<script setup lang="ts">
import { index, getNewPublish } from '~/api/cms'

definePageMeta({
    name: 'cmsIndex',
})

const cmsConfig = useCmsConfig()
useHead({
    title: cmsConfig.index_seo_title,
    meta: [
        { name: 'keywords', content: cmsConfig.index_seo_keywords },
        { name: 'description', content: cmsConfig.index_seo_description },
    ],
})

const state: {
    data: anyObj
    newPublishPage: number
    newPublishLoading: boolean
    newPublishNothingMore: boolean
} = reactive({
    data: {},
    newPublishPage: 1,
    newPublishLoading: false,
    newPublishNothingMore: false,
})

const { data } = await index()
if (data.value?.code == 1) {
    state.data = data.value.data
    state.newPublishNothingMore = !(state.data.newPublishContent.length >= 16)
}

const loadNewPublish = () => {
    if (state.newPublishNothingMore) return
    state.newPublishPage++
    state.newPublishLoading = true
    getNewPublish(state.newPublishPage)
        .then((res) => {
            if (res.code == 1) {
                state.newPublishNothingMore = !(res.data.newPublishContent.length >= 16)
                state.data.newPublishContent = state.data.newPublishContent.concat(res.data.newPublishContent)
            }
        })
        .finally(() => {
            state.newPublishLoading = false
        })
}
</script>

<style scoped lang="scss">
.suspension {
    box-shadow: none;
    border-radius: 0;
}
.ad-top-banner {
    display: block;
    min-height: 46px;
    width: 100%;
    height: 100%;
    margin-top: 10px;
    cursor: pointer;
}
.main-row {
    margin-top: 10px;
}
.index-hot-row {
    margin-bottom: 10px;
    .index-hot-img-item {
        position: relative;
        cursor: pointer;
        width: 100%;
        height: 100%;
        .hot-title {
            display: block;
            width: 100%;
            position: absolute;
            bottom: 26px;
            color: #ffffff;
            text-align: center;
        }
        .el-image {
            width: 100%;
            height: 100%;
        }
    }
    .hot-right-item:first-child {
        margin-bottom: 10px;
    }
}
.index-card {
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
.new-box-card {
    :deep(.el-card__header) {
        border-bottom: 1px solid var(--el-border-color-extra-light);
    }
    :deep(.el-card__body) {
        padding-bottom: 10px;
    }
}
.recently-box-card {
    :deep(.el-card__header) {
        border-bottom: 1px solid var(--el-border-color-extra-light);
    }
    :deep(.el-card__body) {
        padding-bottom: 0;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        .hot-channels {
            a {
                color: var(--el-text-color-primary);
                text-decoration: none;
                &:hover {
                    text-decoration: underline;
                }
            }
            .cover-channel-item {
                margin: 5px;
            }
            .cover-channel-item:first-child {
                margin-left: 0;
            }
            .cover-channel-item:last-child {
                margin-right: 0;
            }
        }
    }
    .load-new-publish {
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
.clear {
    clear: both;
}
.rec-content-title-list {
    padding-top: 10px;
    a {
        color: var(--el-text-color-primary);
        text-decoration: none;
    }
    .rec-content-title-item {
        float: left;
        display: flex;
        align-items: center;
        width: 50%;
        padding: 4px 8px 4px 0;
        box-sizing: border-box;
        .rec-content-title-left {
            display: flex;
            align-items: center;
            width: calc(100% - 96px);
            .icon {
                margin-right: 3px;
            }
            .rec-content-title-content {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                &:hover {
                    text-decoration: underline;
                }
            }
        }
        .rec-content-title-right {
            font-size: var(--el-font-size-small);
            margin-left: auto;
            color: var(--el-color-info);
            width: 90px;
            white-space: nowrap;
            text-align: right;
            &:hover {
                text-decoration: underline;
            }
        }
    }
    .rec-content-title-item:nth-child(even) {
        padding: 4px 0 4px 8px;
    }
}
@media screen and (max-width: 768px) {
    .rec-content-title-left {
        width: 100% !important;
    }
    .ad-top-banner {
        min-height: unset;
    }
}
</style>
