<template>
    <div class="user-views">
        <div v-if="state.loading" class="edit-loading">
            <Loading />
        </div>
        <client-only>
            <el-dialog v-model="state.channelPreSelect.show" title="选择频道" width="40%">
                <el-select class="w100" v-model="state.form.channel_id" placeholder="请选择投稿频道">
                    <el-option v-for="(item, idx) in state.channelPreSelect.channel" :key="idx" :label="item.name" :value="item.id" />
                </el-select>
                <el-button :loading="state.channelPreSelect.btnLoading" @click="onContribute" class="contribute-button" type="primary">
                    确定投稿
                </el-button>
            </el-dialog>
        </client-only>
        <el-card class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span>投递稿件</span>
                </div>
            </template>
            <el-form
                v-if="!state.loading"
                ref="formRef"
                @submit.prevent=""
                @keyup.enter="onSubmit()"
                :model="state.form"
                label-position="right"
                class="content-form"
                :rules="state.rules"
                :label-width="120"
            >
                <el-form-item v-if="state.fields.channel_id" label="频道" prop="channel_id">
                    <el-select :disabled="true" class="w100" v-model="state.form.channel_id" placeholder="请选择频道">
                        <el-option v-for="(item, idx) in state.channelPreSelect.channel" :key="idx" :label="item.name" :value="parseInt(item.id)" />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="state.fields.channel_ids" label="副频道" prop="channel_ids">
                    <el-select class="w100" :multiple="true" v-model="state.form.channel_ids" placeholder="请选择副频道">
                        <el-option v-for="(item, idx) in state.channelPreSelect.channel" :key="idx" :label="item.name" :value="item.id.toString()" />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="state.fields.title" label="标题" prop="title">
                    <div class="title-box">
                        <el-input class="title" type="string" v-model="state.form.title" placeholder="请输入文章标题"></el-input>
                        <div v-if="state.fields.title_style">
                            <el-color-picker v-model="state.form.title_style.color" />
                            <el-checkbox size="large" class="title-bold" v-model="state.form.title_style.bold" label="加粗" />
                        </div>
                    </div>
                </el-form-item>
                <FormItem
                    v-if="state.fields.flag"
                    label="标志"
                    type="checkbox"
                    v-model="state.form.flag"
                    prop="flag"
                    :data="{
                        content: {
                            top: '置顶',
                            hot: '热门',
                            recommend: '推荐',
                            new: '最新',
                        },
                    }"
                />
                <FormItem v-if="state.fields.images" label="封面图片" type="images" v-model="state.form.images" prop="images" />
                <FormItem
                    v-if="state.fields.seotitle"
                    label="SEO标题"
                    type="string"
                    v-model="state.form.seotitle"
                    prop="seotitle"
                    placeholder="请输入SEO标题"
                />
                <FormItem
                    v-if="state.fields.keywords"
                    label="SEO关键词"
                    type="textarea"
                    v-model="state.form.keywords"
                    prop="keywords"
                    :input-attr="{ rows: 3 }"
                    @keyup.enter.stop=""
                    @keyup.ctrl.enter="onSubmit()"
                    placeholder="请输入SEO关键词"
                />
                <FormItem
                    v-if="state.fields.description"
                    label="摘要信息"
                    type="textarea"
                    v-model="state.form.description"
                    prop="description"
                    :input-attr="{ rows: 3 }"
                    @keyup.enter.stop=""
                    @keyup.ctrl.enter="onSubmit()"
                    placeholder="文章摘要，同时将作为SEO描述"
                />
                <FormItem
                    v-if="state.fields.tags"
                    label="标签"
                    type="remoteSelect"
                    v-model="state.form.tags"
                    prop="tags"
                    @keyup.enter.stop=""
                    :input-attr="{
                        pk: 'tags.id',
                        field: 'name',
                        remoteUrl: '/api/cms.Index/tags',
                        multiple: true,
                        allowCreate: true,
                        defaultFirstOption: true,
                    }"
                    :attr="{
                        blockHelp: '输入标签名并按下回车即可添加标签',
                    }"
                />
                <FormItem
                    v-if="state.fields.price"
                    label="价格"
                    type="number"
                    prop="price"
                    :input-attr="{ step: 1, valueOnClear: 0 }"
                    v-model.number="state.form.price"
                    placeholder="请输入价格"
                    :attr="{
                        blockHelp: '您可以设置一个价格，用户购买后才能查阅，同时您将获得收益',
                    }"
                />
                <FormItem
                    v-if="state.fields.currency"
                    label="货币"
                    type="radio"
                    v-model="state.form.currency"
                    prop="currency"
                    :data="{ content: { RMB: '人民币', integral: '积分' } }"
                    placeholder="用户需要使用选择的货币购买文章"
                />
                <div class="model-fields" v-for="(item, idx) in state.fields" :key="idx">
                    <div v-if="!item.main_field">
                        <FormItem
                            v-if="item.type == 'number'"
                            :label="item.title"
                            :type="item.type"
                            v-model.number="state.form[item.name]"
                            :attr="{
                                prop: item.name,
                                blockHelp: item.tip,
                                ...item.extend,
                            }"
                            :input-attr="{ placeholder: item.tip, ...item.input_extend }"
                            :key="'number-' + item.id"
                        />
                        <FormItem
                            v-else-if="item.type == 'editor'"
                            :label="item.title"
                            :type="item.type"
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="onSubmit()"
                            v-model="state.form[item.name]"
                            :attr="{
                                prop: item.name,
                                blockHelp: item.tip,
                                ...item.extend,
                            }"
                            :input-attr="{
                                placeholder: item.tip,
                                style: {
                                    zIndex: 99,
                                },
                                ...item.input_extend,
                            }"
                            :key="'editor-' + item.id"
                        />
                        <FormItem
                            v-else-if="item.type == 'textarea'"
                            :label="item.title"
                            :type="item.type"
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="onSubmit()"
                            v-model="state.form[item.name]"
                            :attr="{
                                prop: item.name,
                                blockHelp: item.tip,
                                ...item.extend,
                            }"
                            :input-attr="{ placeholder: item.tip, rows: 3, ...item.input_extend }"
                            :key="'textarea-' + item.id"
                        />
                        <FormItem
                            v-else
                            :label="item.title"
                            :type="item.type"
                            v-model="state.form[item.name]"
                            :attr="{
                                prop: item.name,
                                blockHelp: item.tip,
                                ...item.extend,
                            }"
                            :input-attr="{ placeholder: item.tip, ...item.input_extend }"
                            :data="{ content: item.content ? item.content : {} }"
                            :key="'other-' + item.id"
                        />
                    </div>
                </div>
                <FormItem
                    v-if="state.fields.content"
                    label="内容"
                    type="editor"
                    v-model="state.form.content"
                    prop="content"
                    @keyup.enter.stop=""
                    @keyup.ctrl.enter="onSubmit()"
                    placeholder="请输入内容"
                />
                <div class="form-footer">
                    <el-button v-blur :loading="state.submitLoading" @click="onSubmit()" type="primary"> 保存稿件 </el-button>
                </div>
            </el-form>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { contributeChannel, getContribute, postContribute } from '~/api/cms/user'
import type { FormInstance, FormItemRule } from 'element-plus'
import { buildValidatorParams } from '~/utils/validate'

definePageMeta({
    name: 'cms/editContent',
    path: '/user/cms/editContent/:id',
})

const formRef = ref<FormInstance>()
const route = useRoute()
const router = useRouter()
const state: {
    loading: boolean
    submitLoading: boolean
    // 频道预选
    channelPreSelect: {
        show: boolean
        channel: anyObj[]
        btnLoading: boolean
    }
    fields: anyObj
    form: anyObj
    rules: Partial<Record<string, FormItemRule[]>>
} = reactive({
    loading: true,
    submitLoading: false,
    channelPreSelect: {
        show: false,
        channel: [],
        btnLoading: false,
    },
    fields: {},
    form: {
        channel_id: '',
        flag: ['new'],
        currency: 'RMB',
        title_style: {
            color: '#000000',
            bold: false,
        },
        content: '',
    },
    rules: {
        title: [buildValidatorData({ name: 'required', title: '标题' })],
        channel_id: [buildValidatorData({ name: 'required', title: '频道' })],
        content: [buildValidatorData({ name: 'editorRequired', title: '内容' })],
    },
})

const onContribute = () => {
    state.loading = true
    state.channelPreSelect.btnLoading = true
    getContribute({
        id: route.params.id,
        channel_id: state.form.channel_id,
    })
        .then((res) => {
            if (res.code == 1) {
                state.channelPreSelect.show = false

                let defaults: anyObj = {}
                for (const key in res.data.fields) {
                    if (!res.data.fields[key].main_field) {
                        defaults[res.data.fields[key].name] = res.data.fields[key].default
                    }
                }

                state.fields = res.data.fields
                state.channelPreSelect.channel = res.data.channel

                if (res.data.info) {
                    state.form = res.data.info
                } else {
                    state.form = { ...state.form, ...defaults }
                }

                for (const key in state.fields) {
                    if (state.fields[key].rule) {
                        let ruleStr = state.fields[key].rule.split(',')
                        let ruleArr: anyObj = []
                        ruleStr.forEach((item: string) => {
                            ruleArr.push(buildValidatorData({ name: item as buildValidatorParams['name'], title: state.fields[key].title }))
                        })
                        state.rules = Object.assign(state.rules, {
                            [state.fields[key].name]: ruleArr,
                        })
                    }
                }
            }
        })
        .finally(() => {
            state.loading = false
            state.channelPreSelect.btnLoading = false
        })
}

onMounted(() => {
    if (parseInt(route.params.id as string) == 0) {
        contributeChannel()
            .then((res) => {
                if (res.code == 1) {
                    state.channelPreSelect.show = true
                    state.channelPreSelect.channel = res.data.channel
                }
            })
            .finally(() => {
                state.loading = false
            })
    } else {
        onContribute()
    }
})

const onSubmit = () => {
    formRef.value?.validate((valid) => {
        if (!valid) return
        state.submitLoading = true
        postContribute({ ...state.form, id: route.params.id })
            .then((res) => {
                if (res.code == 1) {
                    setTimeout(() => {
                        router.push({ name: 'cms/content' })
                    }, 2500)
                }
            })
            .finally(() => {
                state.submitLoading = false
            })
    })
}
</script>

<style scoped lang="scss">
.edit-loading {
    display: flex;
    align-items: center;
    justify-content: center;
}
.contribute-button {
    display: block;
    margin: 20px auto;
    margin-bottom: 0;
}
.content-form {
    padding-right: 30px;
    .title-box {
        display: flex;
        align-items: center;
        width: 100%;
        .title {
            flex: 1;
            margin-right: 10px;
        }
        .title-bold {
            margin-left: 10px;
        }
    }
    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 20px;
    }
}
</style>
