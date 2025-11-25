<template>
    <div class="config-box">
        <div class="ai-config ba-scroll-style">
            <el-form :model="ai.state.form" :rules="ai.state.rules" label-position="top">
                <el-form-item>
                    <el-button v-blur @click="onMyAccount">我的账户</el-button>
                    <el-popconfirm @confirm="onResetConfig(reload)" title="确定恢复默认配置？">
                        <template #reference>
                            <el-button>恢复默认配置</el-button>
                        </template>
                    </el-popconfirm>
                </el-form-item>

                <el-divider content-position="left">基本</el-divider>

                <FormItem
                    label="AI模型"
                    :key="JSON.stringify(ai.state.usableModelContent)"
                    type="select"
                    v-model="ai.state.form.ai_model"
                    :data="{ content: ai.state.usableModelContent }"
                    :input-attr="{
                        onChange: onChangeModel,
                        clearable: false,
                    }"
                    :attr="{
                        blockHelp: ai.state.usableModel[ai.state.form.ai_model].tokens_multiplier + ' x (消息内容向量化tokens + AI输入输出tokens)',
                    }"
                />

                <el-form-item v-if="!ai.state.chatModelAttr[ai.state.form.ai_model].unsupported_parameters.includes('temperature')" label="温度">
                    <el-slider v-model="ai.state.form.temperature" :min="0" :max="2" :step="0.1" show-input />
                    <div class="block-help">值越大，低概率词越可能被选择，结果更多样化；值越小，确定性越高</div>
                </el-form-item>

                <el-form-item v-if="!ai.state.chatModelAttr[ai.state.form.ai_model].unsupported_parameters.includes('top_p')" label="核采样">
                    <el-slider v-model="ai.state.form.top_p" :min="0" :max="1" :step="0.1" show-input />
                    <div class="block-help">
                        值越大，随机性越高，值越小，确定性越高；0.1意味着仅考虑前10%质量的令牌。建议更改此选项或温度，但不同时更改两者。阿里千问默认0.5，OpenAi默认1。
                    </div>
                </el-form-item>

                <el-form-item
                    v-if="!ai.state.chatModelAttr[ai.state.form.ai_model].unsupported_parameters.includes('top_k')"
                    label="核采样候选集大小"
                >
                    <el-slider v-model="ai.state.form.top_k" :min="0" :max="100" :step="1" show-input />
                    <div class="block-help">值越大，随机性越高，值越小，确定性越高；默认值0表示不启用此策略，仅核采样和温度生效。</div>
                </el-form-item>

                <el-divider
                    content-position="left"
                    v-if="
                        parseInt(ai.state.form.ai_allow_users_close_kbs) == 1 &&
                        parseInt(ai.state.form.ai_allow_users_select_kbs) == 1 &&
                        parseInt(ai.state.form.ai_allow_users_edit_prompt) == 1
                    "
                >
                    核心意图
                </el-divider>
                <el-form-item v-if="parseInt(ai.state.form.ai_allow_users_close_kbs) == 1" label="是否开启知识库">
                    <el-switch v-model="ai.state.form.ai_kbs_open"></el-switch>
                    <div class="block-help">若关闭，请自行修改提示词。</div>
                </el-form-item>
                <FormItem
                    v-if="
                        parseInt(ai.state.form.ai_allow_users_edit_prompt) == 1 &&
                        !ai.state.chatModelAttr[ai.state.form.ai_model].unsupported_parameters.includes('ai_system_prompt')
                    "
                    label="系统提示词"
                    prop="ai_system_prompt"
                    type="textarea"
                    v-model="ai.state.form.ai_system_prompt"
                    :input-attr="{
                        rows: 1,
                        onChange: onPrompt,
                    }"
                />
                <FormItem
                    v-if="parseInt(ai.state.form.ai_allow_users_edit_prompt) == 1"
                    label="限定提示词"
                    prop="ai_prompt"
                    type="textarea"
                    v-model="ai.state.form.ai_prompt"
                    :input-attr="{
                        rows: 3,
                        onChange: onPrompt,
                    }"
                    placeholder="系统向AI模型接口交互时，这段文案将自动被放置在内容最开始的位置，可用于指定AI身份、边界、如何回复等。"
                />
                <el-form-item
                    v-if="parseInt(ai.state.form.ai_allow_users_close_kbs) == 1 && parseInt(ai.state.form.ai_allow_users_edit_effective_kbs) == 1"
                    label="有效知识相似度"
                >
                    <el-slider v-model="ai.state.form.ai_effective_match_kbs" :min="0" :max="1" :step="0.1" show-input />
                    <div class="block-help">知识点与您提问的相似度达到指定值时，才作为参考资料发送给AI。</div>
                </el-form-item>
                <el-form-item
                    v-if="parseInt(ai.state.form.ai_allow_users_close_kbs) == 1 && parseInt(ai.state.form.ai_allow_users_edit_effective_kbs) == 1"
                    label="有效知识数量"
                >
                    <el-slider v-model="ai.state.form.ai_effective_kbs_count" :min="1" :max="20" :step="1" show-input />
                    <div class="block-help">
                        从知识库选取相似度最高的几条知识点，作为参考资料发送给AI；比如设置为5，当有5个以上知识点达到有效相似度要求时，只发送前5条。
                    </div>
                </el-form-item>
                <FormItem
                    v-if="parseInt(ai.state.form.ai_allow_users_close_kbs) == 1 && parseInt(ai.state.form.ai_allow_users_select_kbs) == 1"
                    label="知识库"
                    type="remoteSelects"
                    v-model="ai.state.form.ai_kbs"
                    :input-attr="{
                        pk: 'id',
                        field: 'name',
                        remoteUrl: '/api/ai/getKbs',
                        clearable: true,
                    }"
                    placeholder="限定知识库（可选）"
                />
                <FormItem
                    v-if="parseInt(ai.state.form.ai_allow_users_close_kbs) == 1 && parseInt(ai.state.form.ai_allow_users_select_kbs) == 1"
                    label="知识点类型"
                    type="select"
                    v-model="ai.state.form.ai_kbs_type"
                    :data="{ content: { qa: '问答对', text: '普通文档' } }"
                    placeholder="限定知识类型（可选）"
                />
                <FormItem
                    v-if="parseInt(ai.state.form.ai_allow_users_close_kbs) == 1 && parseInt(ai.state.form.ai_allow_users_select_kbs) == 1"
                    label="知识点向量数据类型"
                    type="select"
                    v-model="ai.state.form.ai_kbs_text_type"
                    :data="{ content: { query: '检索优化', document: '底库文本' } }"
                    placeholder="限定知识点向量类型"
                />

                <el-form-item v-if="parseInt(ai.state.form.ai_allow_users_edit_effective_kbs) == 1" label="无关认定相似度">
                    <el-slider v-model="ai.state.form.ai_irrelevant_determination" :min="0" :max="0.9" :step="0.1" show-input />
                    <div class="block-help">您的提问与所有知识点的相似度都未能达到以上值，则认为是无关的问题。</div>
                </el-form-item>
                <FormItem
                    v-if="parseInt(ai.state.form.ai_allow_users_edit_effective_kbs) == 1"
                    label="无关问题响应文案"
                    type="textarea"
                    v-model="ai.state.form.ai_irrelevant_message"
                    :input-attr="{
                        rows: 2,
                    }"
                    :attr="{
                        blockHelp: '一个问题被认为无关时，直接回复以上文案，不填写则可以继续交由AI回复',
                    }"
                />

                <el-divider content-position="left">扩展</el-divider>
                <FormItem
                    v-if="!ai.state.chatModelAttr[ai.state.form.ai_model].unsupported_parameters.includes('ai_enable_search')"
                    label="参考夸克搜索结果"
                    type="switch"
                    v-model="ai.state.form.ai_enable_search"
                />

                <el-form-item label="短期记忆对话论数">
                    <el-slider
                        v-model="ai.state.form.ai_short_memory"
                        :min="0"
                        :max="20"
                        :step="1"
                        show-input
                        @change="onAiShortMemoryChange(ai.state.form.ai_short_memory)"
                    />
                    <div class="block-help">向AI发送最近几轮对话记录，若为0，则AI将失去连续对话能力。</div>
                </el-form-item>

                <el-form-item
                    v-if="!ai.state.chatModelAttr[ai.state.form.ai_model].unsupported_parameters.includes('ai_presence_penalty')"
                    label="话题新鲜度"
                >
                    <el-slider v-model="ai.state.form.ai_presence_penalty" :min="-2" :max="2" :step="0.1" show-input />
                    <div class="block-help">值越大，谈论新主题的可能性越高，但样本的质量可能越低。</div>
                </el-form-item>

                <el-form-item
                    v-if="!ai.state.chatModelAttr[ai.state.form.ai_model].unsupported_parameters.includes('ai_frequency_penalty')"
                    label="字词频率惩罚"
                >
                    <el-slider v-model="ai.state.form.ai_frequency_penalty" :min="-2" :max="2" :step="0.1" show-input />
                    <div class="block-help">值越大，文字重复概率越低，但样本的质量可能越低。</div>
                </el-form-item>

                <FormItem
                    label="回复内容最大tokens数"
                    prop="ai_max_tokens"
                    type="number"
                    v-model.number="ai.state.form.ai_max_tokens"
                    :attr="{
                        blockHelp: '用于限制回复内容长度',
                    }"
                    :input-attr="{
                        valueOnClear: 0,
                    }"
                />

                <TokensPercent v-if="parseInt(ai.state.form.ai_allow_users_edit_tokens) == 1" />
            </el-form>
        </div>
        <el-dialog v-model="account.show" title="我的账户" width="26%">
            <div v-loading="account.loading" class="my-account">
                <h3 class="tokens">剩余Tokens {{ account.exChangeInfo.tokens }}</h3>
                <el-radio-group @change="onExTokenChange" class="ex-change-type" v-model="account.exChangeType">
                    <el-radio value="score" label="积分兑换 Tokens" size="large" border></el-radio>
                    <el-radio value="money" label="余额兑换 Tokens" size="large" border></el-radio>
                </el-radio-group>
                <el-input
                    class="ex-change-amount"
                    @input="onExAmountChange"
                    v-model="account.exChangeAmount"
                    size="large"
                    :placeholder="
                        '剩余' + (account.exChangeType == 'score' ? '积分 ' + account.exChangeInfo.score : '余额 ' + account.exChangeInfo.money)
                    "
                    ref="exChangeAmountRef"
                    @keyup.enter="onSubmitExChange"
                />
                <div class="block-help">
                    预计消耗 {{ (account.exChangeAmount ? account.exChangeAmount : 0) + (account.exChangeType == 'score' ? ' 积分' : ' 余额') }}，获得
                    {{ account.obtainedAmount }} Tokens
                </div>
                <div class="buttons">
                    <el-button v-blur @click="account.show = false">取消</el-button>
                    <el-button v-blur :loading="account.submitLoading" @click="onSubmitExChange" type="primary">确定</el-button>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { exChange } from '~/api/ai'
import { onResetConfig, onChangeModel, onPrompt, onAiShortMemoryChange } from '~/composables/ai/index'
import TokensPercent from '~/composables/ai/tokensPercent.vue'
import { InputInstance } from 'element-plus'

const ai = useAi()
const exChangeAmountRef = ref<InputInstance>()
const router = useRouter()

const reload = () => {
    router.go(0)
}

const account = reactive({
    show: false,
    loading: false,
    exChangeType: 'score',
    exChangeAmount: '',
    obtainedAmount: 0,
    exChangeInfo: {
        tokens: 0,
        money: 0,
        score: 0,
        ai_score_exchange_tokens: 0,
        ai_money_exchange_tokens: 0,
    },
    submitLoading: false,
})

const onMyAccount = () => {
    account.show = true
    account.loading = true
    exChange('get')
        .then((res) => {
            exChangeAmountRef.value?.focus()
            account.exChangeInfo = res.data
        })
        .finally(() => {
            account.loading = false
        })
}
const onExTokenChange = () => {
    exChangeAmountRef.value?.focus()
    onExAmountChange()
}
const onExAmountChange = () => {
    const exChangeAmount = account.exChangeAmount ? parseFloat(account.exChangeAmount) : 0
    account.obtainedAmount = parseInt(
        (
            exChangeAmount *
            (account.exChangeType == 'score' ? account.exChangeInfo.ai_score_exchange_tokens : account.exChangeInfo.ai_money_exchange_tokens)
        ).toString()
    )
}
const onSubmitExChange = () => {
    account.submitLoading = true
    exChange('post', {
        type: account.exChangeType,
        amount: account.exChangeAmount,
    })
        .then((res) => {
            if (res.code == 1) {
                account.exChangeAmount = ''
                account.obtainedAmount = 0
                account.show = false
            }
        })
        .finally(() => {
            account.submitLoading = false
        })
}
</script>

<style scoped lang="scss">
.config-box {
    position: relative;
    height: 100%;
    .ai-config {
        display: block;
        width: 100%;
        height: 100%;
        padding: 16px;
        position: absolute;
        background-color: #fbfbfb;
        overflow-y: auto;
        overflow-x: hidden;
        box-sizing: border-box;
        :deep(.el-divider__text) {
            background-color: #fbfbfb;
        }
    }
}
.my-account {
    display: block;
    .tokens {
        text-align: center;
    }
    .ex-change-type {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 40px;
    }
    .ex-change-amount {
        margin-top: 20px;
    }
    .buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 20px;
    }
}
</style>
