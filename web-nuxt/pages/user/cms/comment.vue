<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>我的评论</span>
                </div>
            </template>
            <div v-loading="state.pageLoading" class="list-box">
                <div class="content-item" v-for="(item, idx) in state.list" :key="idx">
                    <el-image fit="cover" class="avatar" :src="fullUrl(userInfo.avatar ? userInfo.avatar : '~/assets/images/avatar.png')">
                        <template #placeholder>
                            <div class="img-loading-placeholder">
                                <Loading />
                            </div>
                        </template>
                    </el-image>
                    <div class="content-info">
                        <div class="content-info-header">
                            <NuxtLink class="title" :to="{ name: 'cmsInfo', params: { id: item.content_id } }">
                                {{ item.title }}
                            </NuxtLink>
                            <span class="create_time">{{ item.create_time }}</span>
                        </div>
                        <div
                            :class="cmsConfig.content_language == 'html' ? 'ba-wang-editor' : 'ba-markdown'"
                            v-html="item.content"
                            class="comment"
                        ></div>
                    </div>
                </div>
            </div>
            <div v-if="state.total > 0" class="log-footer">
                <el-pagination
                    :currentPage="state.currentPage"
                    :page-size="state.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    background
                    :layout="memberCenter.state.shrink ? 'prev, next, jumper' : 'sizes, ->, prev, pager, next, jumper'"
                    :total="state.total"
                    @size-change="onTableSizeChange"
                    @current-change="onTableCurrentChange"
                ></el-pagination>
            </div>
            <el-empty v-else />
        </el-card>
    </div>
</template>

<script setup lang="ts">
import type { ScrollbarInstance } from 'element-plus'
import { comment } from '~/api/cms/user'

definePageMeta({
    name: 'cms/comment',
})

const userInfo = useUserInfo()
const cmsConfig = useCmsConfig()
const memberCenter = useMemberCenter()
const mainScrollbarRef = inject<Ref<ScrollbarInstance>>('mainScrollbarRef')
const state: {
    list: {
        id: string
        title: string
        content: string
        create_time: string
        content_id: string
    }[]
    currentPage: number
    total: number
    pageSize: number
    pageLoading: boolean
} = reactive({
    list: [],
    currentPage: 1,
    total: 0,
    pageSize: 10,
    pageLoading: true,
})

const loadData = () => {
    state.pageLoading = true
    comment({
        page: state.currentPage,
        limit: state.pageSize,
    })
        .then((res) => {
            state.list = res.data.list
            state.total = res.data.total
            mainScrollbarRef?.value?.scrollTo(0, 0)
        })
        .finally(() => {
            state.pageLoading = false
        })
}

const onTableSizeChange = (val: number) => {
    state.pageSize = val
    loadData()
}
const onTableCurrentChange = (val: number) => {
    state.currentPage = val
    loadData()
}

onMounted(() => {
    loadData()
})
</script>

<style scoped lang="scss">
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.user-views-card :deep(.el-card__body) {
    padding-top: 0;
    .content-item {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid var(--el-border-color-extra-light);
        .avatar {
            height: 50px;
            width: 50px;
            border-radius: 50%;
        }
        .content-info {
            padding-left: 10px;
            width: calc(100% - 50px);
            .content-info-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                .title {
                    font-size: var(--el-font-size-medium);
                    font-weight: bold;
                }
                .create_time {
                    font-size: var(--el-font-size-small);
                    color: var(--el-color-info);
                    width: 120px;
                    text-align: right;
                }
                a {
                    color: var(--el-text-color-primary);
                    text-decoration: none;
                    &:hover {
                        text-decoration: underline;
                    }
                }
            }
            .comment {
                margin-top: 10px;
                padding: 10px;
                border-left: 4px solid var(--el-color-info-light-7);
                h1,
                h2,
                h3,
                h4,
                h5,
                h6 {
                    padding: 0;
                    margin: 0;
                }
                blockquote {
                    margin: 5px 0;
                }
                pre {
                    margin: 5px 0;
                }
                hr {
                    margin: 5px 0;
                }
                :deep(img) {
                    max-width: 100%;
                }
            }
        }
    }
    .content-item:last-child {
        border: none;
    }
}
</style>
