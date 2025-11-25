<template>
    <div class="default-main">
        <el-row>
            <el-col :span="12" :offset="6">
                <div class="filter-box">
                    <FormItem
                        label="知识库"
                        type="remoteSelects"
                        v-model="state.kbs"
                        :input-attr="{
                            pk: 'kbs.id',
                            field: 'name',
                            remoteUrl: '/admin/ai.Kbs/index',
                            clearable: true,
                        }"
                        class="filter-item"
                        placeholder="限定知识库（可选）"
                    />
                    <FormItem
                        label="类型"
                        class="filter-item"
                        type="select"
                        v-model="state.type"
                        :data="{ content: { qa: '问答对', text: '普通文档' } }"
                        placeholder="限定知识类型（可选）"
                    />
                </div>
            </el-col>
            <el-col :span="12" :offset="6">
                <el-input v-model="state.keywords" @keyup.enter="onSearch" placeholder="输入关键词以搜索向量知识库" clearable size="large">
                    <template v-if="state.text_type_switch" #prepend>
                        <el-select size="large" v-model="state.text_type" style="width: 115px">
                            <el-option label="检索优化" value="query" />
                            <el-option label="底库文本" value="document" />
                        </el-select>
                    </template>
                    <template #append>
                        <el-button @click="onSearch" icon="el-icon-Search" />
                    </template>
                </el-input>
            </el-col>
        </el-row>
        <div v-loading="state.dataLoading" v-if="state.dataLoading" class="data-loading"></div>
        <el-row v-if="state.data.length" :gutter="20">
            <el-col v-for="(item, idx) in state.data" :key="idx" :span="8">
                <div class="content-box">
                    <div class="title">
                        {{ item.data.title }}
                    </div>
                    <div class="content">
                        {{ item.data.content }}
                    </div>
                    <div class="footer">
                        <el-tag type="success">{{ parseFloat(item.similarity).toFixed(3) }} 相似</el-tag>
                        <el-tag :type="item.mode == 'redis' ? 'success' : 'info'">来自 {{ item.mode }}</el-tag>
                        <el-tag>{{ item.data.type == 'qa' ? '问答对' : '文档' }}</el-tag>
                        <el-tag>精准命中 {{ item.data.hits }} 次</el-tag>
                        <el-tag>tokens {{ item.data.tokens }}</el-tag>
                        <el-tag>{{ item.data.model }} 模型</el-tag>
                        <el-button @click="router.push({ path: '/admin/ai/kbsContent', query: { id: item.data.id } })" size="small" type="primary">
                            <Icon size="12" name="fa fa-pencil" color="#fff" />
                        </el-button>
                    </div>
                </div>
            </el-col>
        </el-row>
        <div class="data-none" v-else>暂无匹配结果...</div>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { debounce } from 'lodash-es'
import { search } from '/@/api/backend/ai/index'
import FormItem from '/@/components/formItem/index.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const state: {
    text_type: string // 检索优化/低库文档
    keywords: string // 搜索关键词
    text_type_switch: boolean // 是否支持 text_type
    data: anyObj // 搜索结果
    dataLoading: boolean
    kbs: string
    type: string
} = reactive({
    text_type: 'query',
    keywords: '',
    text_type_switch: false,
    data: [],
    dataLoading: false,
    kbs: '',
    type: '',
})

defineOptions({
    name: 'ai/search',
})

const onSearch = debounce(() => {
    if (state.dataLoading) return
    state.dataLoading = true
    search('post', {
        keywords: state.keywords,
        text_type: state.text_type,
        type: state.type,
        kbs: state.kbs,
    })
        .then((res) => {
            state.data = res.data.matchData
        })
        .finally(() => {
            state.dataLoading = false
        })
}, 200)

search('get').then((res) => {
    state.text_type_switch = res.data.text_type_switch
})
</script>

<style scoped lang="scss">
.data-none {
    display: block;
    text-align: center;
    color: var(--el-color-info);
    padding-top: 100px;
}
.data-loading {
    height: 80px;
    width: 100%;
    margin-top: 15px;
    :deep(.el-loading-mask) {
        background-color: transparent !important;
    }
}
.filter-box {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 10px 0;
    .filter-item {
        width: 40%;
        margin-right: 40px;
    }
    .filter-item:last-child {
        margin-right: 0;
    }
}
.content-box {
    padding: 15px;
    margin: 15px 0;
    overflow: hidden;
    background-color: var(--el-bg-color-overlay);
    .title,
    .content {
        margin-bottom: 10px;
        // 多行超出省略号
        font-size: 28rpx;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        line-height: 18px;
        height: 54px;
        font-weight: bold;
    }
    .content {
        font-weight: normal;
    }
    .footer {
        .el-tag,
        .el-button {
            margin: 0 5px 5px 0;
        }
        .el-tag:last-child,
        .el-button:last-child {
            margin-right: 0;
        }
    }
}
</style>
