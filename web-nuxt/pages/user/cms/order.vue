<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>我的订单</span>
                </div>
            </template>
            <div v-loading="state.pageLoading" class="list-box">
                <NuxtLink :to="{ name: 'cmsInfo', params: { id: item.object_id } }" class="content-item" v-for="(item, idx) in state.list" :key="idx">
                    <el-image class="content-image suspension" fit="cover" :src="getFirstImage(item.images)">
                        <template #placeholder>
                            <div class="img-loading-placeholder">
                                <Loading />
                            </div>
                        </template>
                    </el-image>
                    <div class="content-info">
                        <div class="content-title">{{ item.title }}</div>
                        <div class="content-desc">{{ item.description }}</div>
                        <div class="content-footer">
                            <span class="amount">{{ formatAmount(item) }}</span>
                            <div class="buy-time">购买于：{{ item.pay_time }}</div>
                        </div>
                    </div>
                </NuxtLink>
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
import { order } from '~/api/cms/user'
import type { ScrollbarInstance } from 'element-plus'

definePageMeta({
    name: 'cms/order',
})

const memberCenter = useMemberCenter()
const mainScrollbarRef = inject<Ref<ScrollbarInstance>>('mainScrollbarRef')
const state: {
    list: {
        object_id: string
        id: string
        title: string
        images: string
        amount: string
        pay_time: string
        description: string
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
    pageLoading: false,
})

const loadData = () => {
    state.pageLoading = true
    order({
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

const formatAmount = (item: anyObj) => {
    if (item.type == 'score') {
        return item.amount + '积分'
    } else {
        return '￥' + item.amount
    }
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
.list-box {
    a {
        color: var(--el-text-color-primary);
        text-decoration: none;
    }
}
.user-views-card :deep(.el-card__body) {
    padding-top: 0;
    .content-item {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid var(--el-border-color-extra-light);
        .content-image {
            width: 80px;
            height: 80px;
        }
        .content-info {
            position: relative;
            padding-left: 10px;
            width: calc(100% - 80px);
            .content-title {
                font-weight: bold;
                font-size: var(--el-font-size-medium);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                padding-top: 2px;
                &:hover {
                    text-decoration: underline;
                }
            }
            .content-desc {
                font-size: var(--el-font-size-small);
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                line-height: 16px;
                padding-top: 4px;
                height: 36px;
                &:hover {
                    text-decoration: underline;
                }
            }
            .content-footer {
                position: absolute;
                bottom: 0;
                color: var(--el-color-info);
                font-size: var(--el-font-size-extra-small);
                display: flex;
                align-items: center;
                .amount {
                    color: var(--el-color-error);
                    font-size: var(--el-font-size-medium);
                }
                .buy-time {
                    margin-left: 10px;
                }
            }
        }
    }
    .content-item:last-child {
        border: none;
    }
}
</style>
