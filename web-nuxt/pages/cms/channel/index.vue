<template>
    <div>
        <component
            v-if="state.data.template"
            @reload="loadArticleList"
            :data="state.data"
            :params="state.loadArticleParams"
            :is="templates[state.data.template]"
        />
    </div>
</template>

<script setup lang="ts">
import { omit } from 'lodash-es'
import { articleList } from '~/api/cms'

definePageMeta({
    name: 'cmsChannel',
    path: '/cms/channel/:value/:type?',
})

const defaultOrder = 'weigh'
const defaultSort = 'desc'

const route = useRoute()
const router = useRouter()
const type = route.params.type || 'channel'
const state: {
    data: ArticleListData
    loadArticleParams: anyObj
} = reactive({
    data: {
        template: '',
    },
    loadArticleParams: {
        tag: type == 'tag' ? route.params.value : 0,
        channel: type == 'channel' ? route.params.value : 0,
        keywords: type == 'search' ? route.params.value : '',
        user: type == 'user' ? route.params.value : 0,
        order: (route.query.order as string) ?? defaultOrder,
        sort: (route.query.sort as string) ?? defaultSort,
        page: (route.query.page as string) ?? '1',
        filter: omit(route.query, ['page', 'order', 'sort']),
        limit: 16,
    },
})

const loadArticleList = (params: anyObj) => {
    // 筛选数据记录至URL
    router.push({
        name: 'cmsChannel',
        params: { value: route.params.value, type: type },
        query: {
            page: params.page,
            order: params.order,
            sort: params.sort,
            ...params.filter,
        },
    })
}

const templates: Record<string, Component> = {}
const templateComponents = import.meta.glob('~/composables/template/cms/channel/**.vue')
for (const key in templateComponents) {
    const res: any = await templateComponents[key]()
    const fileName = key.replace('/composables/template/cms/channel/', '').replace('.vue', '')
    templates[fileName] = res.default
}

const { data } = await articleList(state.loadArticleParams)
if (data.value?.code == 1) {
    state.data = computed(() => data.value?.data)
}

useHead({
    title: state.data.info?.name || state.data.info?.seotitle,
    meta: [
        { name: 'keywords', content: state.data.info?.keywords },
        { name: 'description', content: state.data.info?.description },
    ],
})

onMounted(() => {
    watch(
        () => route.query,
        () => {
            state.loadArticleParams.page = (route.query.page as string) ?? '1'
            state.loadArticleParams.order = (route.query.order as string) ?? defaultOrder
            state.loadArticleParams.sort = (route.query.sort as string) ?? defaultSort
            state.loadArticleParams.filter = omit(route.query, ['page', 'order', 'sort'])
        }
    )
})
</script>

<style lang="scss">
.cms-order {
    .caret-wrapper {
        align-items: center;
        cursor: pointer;
        display: inline-flex;
        flex-direction: column;
        height: 14px;
        overflow: initial;
        position: relative;
        vertical-align: middle;
        width: 24px;
        margin-right: 10px;
    }
    .sort-caret {
        border: 5px solid transparent;
        height: 0;
        left: 7px;
        position: absolute;
        width: 0;
    }
    .sort-caret.ascending {
        border-bottom-color: var(--el-text-color-placeholder);
        top: -5px;
    }
    .sort-caret.descending {
        border-top-color: var(--el-text-color-placeholder);
        bottom: -3px;
    }
    .asc {
        .sort-caret.ascending {
            border-bottom-color: var(--el-color-primary);
        }
    }
    .desc {
        .sort-caret.descending {
            border-top-color: var(--el-color-primary);
        }
    }
}
</style>
