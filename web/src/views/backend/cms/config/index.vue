<template>
    <div class="default-main">
        <el-row v-loading="state.loading" :gutter="20">
            <el-col class="xs-mb-20" :xs="24" :sm="16">
                <el-form
                    v-if="!state.loading"
                    ref="formRef"
                    @keyup.enter="onSubmit()"
                    :model="state.form"
                    :rules="state.rules"
                    :label-position="'top'"
                >
                    <el-tabs v-model="state.activeTab" type="border-card">
                        <el-tab-pane class="config-tab-pane" name="base" label="基础配置">
                            <div class="config-form-item">
                                <FormItem
                                    label="CMS首页标题"
                                    prop="index_seo_title"
                                    type="string"
                                    v-model="state.form.index_seo_title"
                                    placeholder="站点首页标题"
                                />
                                <FormItem
                                    label="CMS首页SEO关键词"
                                    prop="index_seo_keywords"
                                    type="string"
                                    v-model="state.form.index_seo_keywords"
                                    placeholder="站点首页SEO关键词"
                                />
                                <FormItem
                                    label="CMS首页SEO描述"
                                    prop="index_seo_description"
                                    type="textarea"
                                    v-model="state.form.index_seo_description"
                                    placeholder="站点首页SEO描述"
                                />
                                <FormItem
                                    label="前后台内容标记语言"
                                    prop="content_language"
                                    type="radio"
                                    v-model="state.form.content_language"
                                    :data="{ content: { html: 'Html', markdown: 'Markdown' } }"
                                    :attr="{
                                        blockHelp: '内容使用的标记语言，程序将根据标记语言确保内容正确显示',
                                    }"
                                />
                                <FormItem
                                    label="前后台评论标记语言"
                                    prop="comment_language"
                                    type="radio"
                                    v-model="state.form.comment_language"
                                    :data="{ content: { html: 'Html', markdown: 'Markdown' } }"
                                    :attr="{
                                        blockHelp: '用户评论使用的标记语言，程序将根据标记语言确保内容正确显示',
                                    }"
                                />
                                <FormItem
                                    label="用户赞赏内容"
                                    prop="appreciation"
                                    type="radio"
                                    v-model="state.form.appreciation"
                                    :data="{ content: { enable: $t('Enable'), disable: $t('Disable') } }"
                                    :attr="{
                                        blockHelp: '用户是否可以付费赞赏内容作者，支付金额将自动汇入作者余额（仅会员）',
                                    }"
                                />
                                <FormItem
                                    label="用户赞赏分成比例（%）"
                                    prop="appreciation_ratio"
                                    type="number"
                                    v-model.number="state.form.appreciation_ratio"
                                    :attr="{
                                        blockHelp: '内容作者可以得到的赞赏金额比例，0-100',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    label="用户购买分成比例（%）"
                                    prop="buy_ratio"
                                    type="number"
                                    v-model.number="state.form.buy_ratio"
                                    :attr="{
                                        blockHelp: '用户购买付费内容时，作者可以得到的金额比例，0-100，支付金额将自动汇入作者余额（仅会员）',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    label="微信平台收款手续费（%）"
                                    prop="wechat_payment_commission"
                                    type="number"
                                    v-model.number="state.form.wechat_payment_commission"
                                    :attr="{
                                        blockHelp: '用户付费总额减去被收款平台扣取的手续费，再按比例汇入作者余额，0-100',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    label="用户评论需要审核"
                                    prop="comments_review"
                                    type="radio"
                                    v-model="state.form.comments_review"
                                    :data="{ content: { yes: '需要审核', no: '无需审核' } }"
                                />
                                <FormItem
                                    label="评论发布间隔（秒）"
                                    prop="comments_interval"
                                    type="number"
                                    v-model.number="state.form.comments_interval"
                                    :attr="{
                                        blockHelp: '用户每多少秒可以发布一次评论，输入 0 则不做限制',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                            </div>
                            <el-button :loading="state.submitLoading" type="primary" @click="onSubmit()">{{ $t('Save') }}</el-button>
                        </el-tab-pane>
                        <el-tab-pane class="config-tab-pane" name="uni" label="uni-app 配置">
                            <!-- 加 v-if 避免富文本编辑器报高度低于 300 的警告 -->
                            <div v-if="state.activeTab == 'uni'" class="config-form-item">
                                <FormItem
                                    label="H5 站点部署域名"
                                    prop="h5_domain"
                                    type="string"
                                    v-model="state.form.h5_domain"
                                    :attr="{
                                        blockHelp: '用于 H5 微信公众号授权登录回调和 APP 的分享卡片跳转，示例：http://h5.your cms domain.com',
                                    }"
                                    placeholder="请输入域名，带协议，尾部无需 /"
                                />
                                <FormItem
                                    label="协议内容标记语言"
                                    prop="agreement_language"
                                    type="radio"
                                    v-model="state.form.agreement_language"
                                    :data="{ content: { html: 'Html', markdown: 'Markdown' } }"
                                    :attr="{
                                        blockHelp: '以下的【服务条款、隐私政策、关于我们】使用的标记语言，程序将根据标记语言确保内容正确显示',
                                    }"
                                />
                                <FormItem
                                    label="服务条款"
                                    @keyup.enter.stop=""
                                    @keyup.ctrl.enter="onSubmit()"
                                    prop="terms"
                                    type="editor"
                                    v-model="state.form.terms"
                                />
                                <FormItem
                                    label="隐私政策"
                                    @keyup.enter.stop=""
                                    @keyup.ctrl.enter="onSubmit()"
                                    prop="privacy"
                                    type="editor"
                                    v-model="state.form.privacy"
                                />
                                <FormItem
                                    label="关于我们"
                                    @keyup.enter.stop=""
                                    @keyup.ctrl.enter="onSubmit()"
                                    prop="about"
                                    type="editor"
                                    v-model="state.form.about"
                                />
                            </div>
                            <el-button :loading="state.submitLoading" type="primary" @click="onSubmit()">{{ $t('Save') }}</el-button>
                        </el-tab-pane>
                    </el-tabs>
                </el-form>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { FormInstance } from 'element-plus'
import { reactive, ref } from 'vue'
import { index, save } from '/@/api/backend/cms/config'
import FormItem from '/@/components/formItem/index.vue'
import { buildValidatorData } from '/@/utils/validate'

const ratioValidator = (rule: any, val: number, callback: Function) => {
    let msg = '请填写 0-100 之间的数字'
    if (val != 0 && !val) {
        return callback(new Error(msg))
    }
    if (val < 0 || val > 100) {
        return callback(new Error(msg))
    }
    return callback()
}

const state = reactive({
    loading: false,
    form: {
        index_seo_title: '',
        index_seo_keywords: '',
        index_seo_description: '',
        content_language: '',
        comment_language: '',
        appreciation: '',
        appreciation_ratio: 0,
        buy_ratio: 0,
        wechat_payment_commission: 0,
        comments_review: '',
        comments_interval: 0,
        h5_domain: '',
        agreement_language: '',
        terms: '',
        privacy: '',
        about: '',
    },
    rules: {
        index_seo_title: [buildValidatorData({ name: 'required', title: '标题' })],
        index_seo_keywords: [buildValidatorData({ name: 'required', title: 'SEO关键词' })],
        index_seo_description: [buildValidatorData({ name: 'required', title: 'SEO描述' })],
        appreciation_ratio: [
            {
                required: true,
                trigger: 'blur',
                validator: ratioValidator,
            },
        ],
        buy_ratio: [
            {
                required: true,
                trigger: 'blur',
                validator: ratioValidator,
            },
        ],
        wechat_payment_commission: [
            {
                required: true,
                trigger: 'blur',
                validator: ratioValidator,
            },
        ],
    },
    activeTab: 'base',
    submitLoading: false,
})

const formRef = ref<FormInstance>()

const onSubmit = () => {
    if (!formRef.value) return
    formRef.value.validate((valid: boolean) => {
        if (valid) {
            state.submitLoading = true
            save({ ...state.form, activeTab: state.activeTab })
                .then(() => {})
                .finally(() => {
                    state.submitLoading = false
                })
        }
    })
}

index().then((res) => {
    state.form = res.data.data
})

defineOptions({
    name: 'cms/config',
})
</script>

<style scoped lang="scss">
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--ba-bg-color);
    border-bottom: none;
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}
.el-tabs--border-card :deep(.el-tabs__nav-wrap) {
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-card :deep(.el-card__header) {
    height: 40px;
    padding: 0;
    line-height: 40px;
    border: none;
    padding-left: 20px;
    background-color: var(--ba-bg-color);
}
.config-tab-pane {
    padding: 5px;
}
.config-tab-pane-add {
    width: 80%;
}
</style>
