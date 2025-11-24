<template>
    <div>
        <!-- 栏目标题和面包屑 -->
        <div class="header">
            <div class="title">
                <template v-if="data.title?.length == 1">{{ data.title[0] }}</template>
                <template v-else>
                    <span v-for="(item, idx) in data.title" :key="idx">{{ item + ' ' }}</span>
                </template>
            </div>
            <el-breadcrumb class="breadcrumb" separator="/">
                <el-breadcrumb-item :to="{ name: 'cmsIndex' }">{{ $t('Home') }}</el-breadcrumb-item>
                <el-breadcrumb-item :to="infoBreadcrumbTo(item)" v-for="(item, idx) in data.breadcrumb" :key="idx">
                    {{ item.name }}
                </el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <el-row :gutter="15">
            <el-col :lg="18">
                <!-- 数据筛选器 -->
                <el-card v-if="!isEmpty(data.filterConfig)" class="filter-card" shadow="never">
                    <div class="filter-box">
                        <div class="filter-item" v-for="(item, idx) in data.filterConfig" :key="idx">
                            <div class="label">{{ item.title }}</div>
                            <el-radio-group @change="onFilterChange(item.name, $event)" :model-value="props.params.filter[item.name]">
                                <el-radio
                                    v-for="(dict, index) in item.frontend_filter_dict"
                                    :key="index"
                                    :value="index"
                                    :label="dict"
                                    border
                                ></el-radio>
                            </el-radio-group>
                        </div>
                    </div>
                </el-card>

                <!-- 数据排序器和数据列表 -->
                <el-card class="article-card" shadow="never">
                    <template #header>
                        <div class="order cms-order">
                            <span
                                @click="onOrder('weigh')"
                                class="order-item"
                                :class="[props.params.order == 'weigh' ? 'active' : '', props.params.order == 'weigh' ? props.params.sort : '']"
                            >
                                <span>默认</span>
                                <span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span>
                            </span>
                            <span
                                @click="onOrder('views')"
                                class="order-item"
                                :class="[props.params.order == 'views' ? 'active' : '', props.params.order == 'views' ? props.params.sort : '']"
                            >
                                <span>浏览量</span>
                                <span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span>
                            </span>
                            <span
                                @click="onOrder('publish_time')"
                                class="order-item"
                                :class="[
                                    props.params.order == 'publish_time' ? 'active' : '',
                                    props.params.order == 'publish_time' ? props.params.sort : '',
                                ]"
                            >
                                <span>发布时间</span>
                                <span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span>
                            </span>
                        </div>
                    </template>
                    <div>
                        <div v-if="props.data.list?.length">
                            <CmsDoubleColumnCard :article-list="props.data.list" />
                            <div class="load-list">
                                <el-pagination
                                    :current-page="Number(props.params.page)"
                                    @current-change="onCurrentChange"
                                    background
                                    :default-page-size="props.params.limit"
                                    layout="prev, pager, next, jumper, total"
                                    :total="data.total"
                                />
                            </div>
                        </div>
                        <el-empty v-else description="无数据" />
                    </div>
                </el-card>
            </el-col>
            <el-col :lg="6">
                <CmsRightSideBar />
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { isEmpty } from 'lodash-es'

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

const emits = defineEmits<{
    (e: 'reload', params: anyObj): void
}>()

const onOrder = (order: string) => {
    let sort = props.params.sort
    if (props.params.order == order) {
        sort = props.params.sort == 'desc' ? 'asc' : 'desc'
    }
    emits('reload', { ...props.params, order: order, sort: sort, page: 1 })
}

const onCurrentChange = (val: number) => {
    emits('reload', {
        ...props.params,
        page: val,
    })
    window.scrollTo(0, 0)
}

const onFilterChange = (field: string, val?: string | number | boolean) => {
    emits('reload', {
        ...props.params,
        page: 1,
        filter: {
            ...props.params.filter,
            [field]: val,
        },
    })
}
</script>

<style scoped lang="scss">
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    .title {
        font-size: var(--el-font-size-large);
        font-weight: bold;
        color: var(--el-text-color-primary);
        padding: 20px 10px;
    }
    .breadcrumb {
        padding: 0 10px;
    }
}
.filter-card {
    border: none;
    border-radius: 0;
    margin-bottom: 10px;
    :deep(.el-card__body) {
        padding: 10px 15px;
    }
    .filter-item {
        display: flex;
        align-items: center;
        .label {
            font-size: var(--el-font-size-medium);
            margin-right: 10px;
            min-width: 60px;
        }
        :deep(.el-radio) {
            margin: 6px;
        }
        :deep(.el-radio__inner) {
            display: none;
        }
    }
    .filter-item:last-child {
        margin-bottom: 0;
    }
}
.article-card {
    border: none;
    border-radius: 0;
    margin-bottom: 10px;
    :deep(.el-card__header) {
        padding: 15px;
        border-bottom: 1px solid var(--el-border-color-extra-light);
    }
    :deep(.el-card__body) {
        padding: 0 15px;
    }
}
.order {
    .order-item {
        cursor: pointer;
        color: var(--el-text-color-primary);
        &:hover {
            color: var(--el-color-primary);
            text-decoration: underline;
        }
    }
    .order-item.active {
        color: var(--el-color-primary);
    }
}
.load-list {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
