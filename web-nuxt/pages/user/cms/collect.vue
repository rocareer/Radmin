<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>我的收藏</span>
                </div>
            </template>
            <div v-loading="state.pageLoading" class="list-box">
                <div class="content-item" v-for="(item, idx) in state.list" :key="idx">
                    <CmsLeftImageCard
                        :article="{
                            ...item,
                            cmsChannel: {
                                id: item.channel_id,
                                name: item.name,
                            },
                            create_time: item.publish_time,
                        }"
                    />
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
import { operateRecord } from '~/api/cms/user'

definePageMeta({
    name: 'cms/collect',
})

const memberCenter = useMemberCenter()
const mainScrollbarRef = inject<Ref<ScrollbarInstance>>('mainScrollbarRef')
const state: {
    list: {
        id: string
        title: string
        images: string
        publish_time: string
        name: string
        channel_id: number
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
    operateRecord({
        page: state.currentPage,
        limit: state.pageSize,
        type: 'collect',
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
        border-bottom: 1px solid var(--el-border-color-extra-light);
    }
    .content-item:last-child {
        border: none;
    }
}
</style>
