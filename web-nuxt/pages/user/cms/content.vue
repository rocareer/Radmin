<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>我投稿的内容</span>
                    <el-button type="primary" @click="$router.push({ name: 'cms/editContent', params: { id: 0 } })" v-blur> 立即投稿 </el-button>
                </div>
            </template>
            <div v-loading="state.pageLoading" class="list-box">
                <div class="content-item" v-for="(item, idx) in state.list" :key="idx">
                    <NuxtLink :to="{ name: 'cmsInfo', params: { id: item.id } }">
                        <el-image fit="cover" class="card-content-image suspension" :src="getFirstImage(item.images)" :alt="item.title">
                            <template #placeholder>
                                <div class="img-loading-placeholder">
                                    <Loading color="var(--el-color-info-light-7)" />
                                </div>
                            </template>
                        </el-image>
                    </NuxtLink>
                    <div class="card-content-body">
                        <NuxtLink :to="{ name: 'cmsInfo', params: { id: item.id } }">
                            <div class="title" :style="calcTitleStyle(item.title_style)">{{ item.title }}</div>
                        </NuxtLink>
                        <div class="tags">
                            <el-tooltip
                                popper-class="content-status-tooltip"
                                :disabled="['refused', 'offline'].includes(item.status) && item.memo ? false : true"
                                :content="item.memo"
                                placement="top"
                            >
                                <el-tag :class="formatStatus(item).class" :type="formatStatus(item).type" class="tag-name">
                                    {{ formatStatus(item).title }}
                                </el-tag>
                            </el-tooltip>
                            <NuxtLink :to="{ name: 'cmsChannel', params: { value: item.channel_id } }">
                                <el-tag class="tag-name primary-hover">
                                    {{ item.channelName }}
                                </el-tag>
                            </NuxtLink>
                        </div>
                        <div class="footer">
                            <div class="statistical-data">
                                <div class="item publish_time">{{ item.publish_time }}</div>
                                <span class="item views"> {{ item.views }}浏览 </span>
                                <span class="item"> {{ item.comments }}评论 </span>
                                <span class="item"> {{ item.likes }}点赞 </span>
                                <span v-if="parseFloat(item.price) > 0" class="item"> {{ item.sales }}销量 </span>
                            </div>
                            <div class="tag-right">
                                <el-button v-if="parseFloat(item.price) > 0" @click="showBuyLog(item.id)" size="small" v-blur type="success">
                                    已购记录
                                </el-button>
                                <el-button
                                    @click="$router.push({ name: 'cms/editContent', params: { id: item.id } })"
                                    size="small"
                                    v-blur
                                    type="primary"
                                >
                                    编辑
                                </el-button>
                                <el-popconfirm @confirm="onDelContent(item.id)" title="确定删除内容吗？">
                                    <template #reference>
                                        <el-button size="small" v-blur type="danger">删除</el-button>
                                    </template>
                                </el-popconfirm>
                            </div>
                        </div>
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
        <el-dialog :append-to-body="true" v-model="state.buyLog.show" title="用户购买记录">
            <el-table v-loading="state.buyLog.loading" :data="state.buyLog.data">
                <el-table-column align="center" prop="id" label="ID" width="150" />
                <el-table-column prop="nickname" label="用户昵称" />
                <el-table-column :show-overflow-tooltip="true" prop="title" label="标题" />
                <el-table-column align="center" :formatter="formatterAmount" prop="amount" label="支付金额" />
                <el-table-column align="center" prop="pay_time" label="购买时间" />
            </el-table>
            <div v-if="state.buyLog.total > 0" class="buy-log-footer">
                <el-pagination
                    :currentPage="state.buyLog.currentPage"
                    :page-size="state.buyLog.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    background
                    :layout="memberCenter.state.shrink ? 'prev, next, jumper' : 'sizes, ->, prev, pager, next, jumper'"
                    :total="state.buyLog.total"
                    @size-change="onBuyLogTableSizeChange"
                    @current-change="onBuyLogTableCurrentChange"
                ></el-pagination>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import type { TagProps } from 'element-plus'
import { content, delContent, buyLog } from '~/api/cms/user'
import type { ScrollbarInstance } from 'element-plus'

definePageMeta({
    name: 'cms/content',
})

const memberCenter = useMemberCenter()
const mainScrollbarRef = inject<Ref<ScrollbarInstance>>('mainScrollbarRef')
const state: {
    list: anyObj[]
    currentPage: number
    total: number
    pageSize: number
    pageLoading: boolean
    buyLog: {
        id: number
        show: boolean
        loading: boolean
        data: anyObj[]
        currentPage: number
        total: number
        pageSize: number
    }
} = reactive({
    list: [],
    currentPage: 1,
    total: 0,
    pageSize: 10,
    pageLoading: true,
    buyLog: {
        id: 0,
        show: false,
        loading: true,
        data: [],
        currentPage: 1,
        total: 0,
        pageSize: 10,
    },
})

const loadData = () => {
    state.pageLoading = true
    content({
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

const formatStatus = (item: anyObj) => {
    let status: {
        type: TagProps['type']
        title: string
        class: string
    } = { type: 'primary', title: '', class: '' }

    switch (item.status) {
        case 'normal':
            status = { type: 'success', title: '状态正常', class: 'success-hover' }
            break
        case 'unaudited':
            status = { type: 'primary', title: '等待管理员审核', class: 'primary-hover' }
            break
        case 'refused':
            status = { type: 'danger', title: '管理员已拒绝', class: 'danger-hover' }
            break
        case 'offline':
            status = { type: 'info', title: '已下线', class: 'info-hover' }
            break
    }
    return status
}

const onDelContent = (id: string) => {
    delContent(id).then((res) => {
        if (res.code == 1) {
            loadData()
        }
    })
}

const loadBuyLog = () => {
    state.buyLog.loading = true
    buyLog({
        page: state.buyLog.currentPage,
        limit: state.buyLog.pageSize,
        id: state.buyLog.id,
    })
        .then((res) => {
            if (res.code == 1) {
                state.buyLog.data = res.data.list
                state.buyLog.total = res.data.total
            }
        })
        .finally(() => {
            state.buyLog.loading = false
        })
}

const showBuyLog = (id: number) => {
    state.buyLog.id = id
    state.buyLog.show = true
    loadBuyLog()
}

const onBuyLogTableSizeChange = (val: number) => {
    state.buyLog.pageSize = val
    loadBuyLog()
}
const onBuyLogTableCurrentChange = (val: number) => {
    state.buyLog.currentPage = val
    loadBuyLog()
}

const formatterAmount = (row: anyObj) => {
    if (row['type'] == 'score') {
        return row['amount'] + '积分'
    } else {
        return '￥' + row['amount']
    }
}

onMounted(() => {
    loadData()
})
</script>

<style>
.content-status-tooltip {
    width: 300px;
}
</style>

<style scoped lang="scss">
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.buy-log-footer {
    margin-top: 20px;
}
.user-views-card :deep(.el-card__body) {
    padding-top: 0;
    .content-item {
        display: flex;
        box-sizing: border-box;
        padding: 10px 0;
        width: 100%;
        border-bottom: 1px solid var(--el-border-color-extra-light);
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
            .tags {
                margin-top: 4px;
                .tag-name {
                    cursor: pointer;
                    margin-right: 6px;
                }
                .tag-name:hover {
                    color: var(--el-color-white);
                }
            }
            .footer {
                width: 100%;
                position: absolute;
                bottom: 0;
                display: flex;
                align-items: center;
                .statistical-data {
                    display: flex;
                    align-items: center;
                    .item {
                        font-size: 13px;
                        padding-right: 10px;
                        color: var(--el-color-info);
                    }
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
    .content-item:last-child {
        border: none;
    }
}
@media screen and (max-width: 460px) {
    .content-item {
        display: block !important;
        .card-content-image {
            width: 100% !important;
            height: 120px !important;
        }
        .card-content-body {
            width: 100% !important;
        }
    }
}
@media screen and (max-width: 1360px) {
    .footer {
        .publish_time,
        .views {
            display: none;
        }
    }
}
@media screen and (max-width: 1250px) {
    .footer {
        display: block !important;
        position: unset !important;
        bottom: unset !important;
        .statistical-data {
            display: block;
            margin: 5px 0;
        }
        .tag-right {
            display: block;
        }
    }
}
.success-hover {
    &:hover {
        background-color: var(--el-color-success) !important;
    }
}
.primary-hover {
    &:hover {
        background-color: var(--el-color-primary) !important;
    }
}
.danger-hover {
    &:hover {
        background-color: var(--el-color-danger) !important;
    }
}
.info-hover {
    &:hover {
        background-color: var(--el-color-info) !important;
    }
}
</style>
