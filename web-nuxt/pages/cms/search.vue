<template>
    <div>
        <el-row class="search" justify="center">
            <el-col class="search-col" :xs="22" :lg="14">
                <el-input
                    ref="inputRef"
                    class="search-input"
                    @keyup.enter="onSearch()"
                    placeholder="搜索其实很简单"
                    size="large"
                    v-model="state.keywords"
                />
                <el-button @click="onSearch()" class="search-button" size="large">搜索</el-button>
            </el-col>
            <el-col class="search-col" :xs="22" :lg="14">
                <div class="hot-keywords-box">
                    <div class="hot-title-box">
                        <div class="hot-title">热门搜索</div>
                        <div @click="onHotKeywordsPage" class="hot-title-refresh">
                            <Icon size="var(--el-font-size-small)" color="var(--el-color-info)" name="fa fa-refresh" />
                            <span>换一换</span>
                        </div>
                    </div>
                    <div class="hot-keyword-list">
                        <div
                            class="hot-keyword-item"
                            @click="onSearch(item.search)"
                            v-for="(item, idx) in state.hotKeywords[state.hotKeywordsPage]"
                            :key="idx"
                        >
                            <span class="rank-icon">{{ item.rank }}</span>
                            <span class="title">{{ item.search }}</span>
                        </div>
                    </div>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { search } from '~/api/cms/index'
import type { InputInstance } from 'element-plus'

definePageMeta({
    name: 'cmsSearch',
})

const inputRef = ref<InputInstance>()
const router = useRouter()
const state: {
    keywords: string
    hotKeywords: anyObj[]
    hotKeywordsPage: number
} = reactive({
    keywords: '',
    hotKeywords: [],
    hotKeywordsPage: 0,
})

const onSearch = (keywords = '') => {
    if (!state.keywords && !keywords) return
    router.push({ name: 'cmsChannel', params: { value: keywords ? keywords : state.keywords, type: 'search' } })
}

const onHotKeywordsPage = () => {
    state.hotKeywordsPage = state.hotKeywords.length - 1 > state.hotKeywordsPage ? state.hotKeywordsPage + 1 : 0
}

const { data } = await search()
if (data.value?.code == 1) {
    const result = []
    for (let i = 0; i < data.value.data.hotKeywords.length; i += 6) {
        result.push(data.value.data.hotKeywords.slice(i, i + 6))
    }
    state.hotKeywords = result
}

onMounted(() => {
    inputRef.value?.focus()
})
</script>

<style scoped lang="scss">
.search {
    margin-top: 60px;
    .search-col {
        display: flex;
        .search-input {
            margin-right: 10px;
        }
        .search-button {
            padding-left: 30px;
            padding-right: 30px;
        }
        .hot-keywords-box {
            width: 100%;
            padding: 30px 10px;
            .hot-title-box {
                display: flex;
                justify-content: space-between;
                align-items: center;
                .hot-title {
                    font-size: var(--el-font-size-medium);
                }
                .hot-title-refresh {
                    cursor: pointer;
                    color: var(--el-color-info);
                    font-size: var(--el-font-size-small);
                    user-select: none;
                    .icon {
                        margin-right: 2px;
                    }
                }
            }
        }
        .hot-keyword-list {
            margin-top: 10px;
            .hot-keyword-item {
                float: left;
                display: flex;
                align-items: center;
                width: 50%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                padding: 6px 10px 5px 0;
                cursor: pointer;
                .rank-icon {
                    display: block;
                    height: 18px;
                    width: 18px;
                    line-height: 18px;
                    background-color: var(--el-color-info-light-8);
                    border-radius: 5px;
                    text-align: center;
                    font-size: 12px;
                    margin-right: 4px;
                }
                .title {
                    display: inline-block;
                    width: calc(100% - 22px);
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
            }
            .hot-keyword-item:nth-child(-n + 4) {
                .rank-icon {
                    color: var(--el-color-primary-light-9);
                    background-image: linear-gradient(120deg, red, yellow);
                }
            }
        }
    }
}
</style>
