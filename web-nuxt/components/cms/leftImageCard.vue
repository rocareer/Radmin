<template>
    <div ref="articleCard" class="article-card">
        <NuxtLink :to="{ name: 'cmsInfo', params: { id: props.article.id } }">
            <el-image fit="cover" class="card-content-image suspension" :src="getFirstImage(article.images)" :alt="article.seotitle ?? article.title">
                <template #placeholder>
                    <div class="img-loading-placeholder">
                        <Loading color="var(--el-color-info-light-7)" />
                    </div>
                </template>
            </el-image>
        </NuxtLink>
        <div class="card-content-body">
            <NuxtLink :to="{ name: 'cmsInfo', params: { id: article.id } }">
                <div class="title" :style="calcTitleStyle(article.title_style)">{{ article.title }}</div>
                <div class="intro">
                    {{ article.description }}
                </div>
            </NuxtLink>
            <div class="tags">
                <NuxtLink v-if="!isEmpty(article.cmsChannel)" :to="{ name: 'cmsChannel', params: { value: article.cmsChannel.id } }">
                    <el-tag class="cms-channel-name">
                        {{ article.cmsChannel.name }}
                    </el-tag>
                </NuxtLink>
                <div class="tag-right" v-if="state.showTag">
                    <div class="tag-item">{{ article.create_time }}</div>
                    <span class="tag-item"> <i class="fa fa-eye"></i> {{ article.views }} 浏览 </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { isEmpty } from 'lodash-es'

interface Props {
    article: anyObj
}
const props = withDefaults(defineProps<Props>(), {
    article: () => [],
})

const state = reactive({
    showTag: true,
})

const articleCard = ref()
onMounted(() => {
    state.showTag = articleCard.value?.offsetWidth > 360
    useEventListener(window, 'resize', () => {
        state.showTag = articleCard.value?.offsetWidth > 360
    })
})
</script>

<style scoped lang="scss">
.article-card {
    display: flex;
    box-sizing: border-box;
    padding: 15px 0;
    width: 100%;
    .card-content-image {
        width: 160px;
        height: 90px;
    }
    .card-content-body {
        position: relative;
        margin-left: 10px;
        width: calc(100% - 160px);
        a {
            text-decoration: none;
        }
        .title {
            display: inline-block;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 1.2em;
            &:hover {
                text-decoration: underline;
            }
        }
        .intro {
            font-size: 1em;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            color: var(--el-color-info);
            &:hover {
                text-decoration: underline var(--el-color-info);
            }
        }
        .tags {
            width: 100%;
            position: absolute;
            bottom: 0;
            display: flex;
            align-items: center;
            .cms-channel-name:hover {
                background-color: var(--el-color-primary);
                color: var(--el-color-white);
            }
            .tag-right {
                margin-left: auto;
                display: flex;
                .tag-item {
                    font-size: 12px;
                    padding-right: 10px;
                    color: var(--el-color-info-light-3);
                }
            }
        }
    }
}
</style>
