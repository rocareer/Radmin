<template>
    <div class="default-main">
        <el-row v-loading="state.loading" :gutter="20">
            <el-col class="xs-mb-20" :xs="24" :sm="16">
                <el-form
                    v-if="!state.loading"
                    ref="formRef"
                    @keyup.enter="onSubmit(formRef)"
                    :model="state.form"
                    :rules="state.rules"
                    :label-position="'top'"
                    :key="state.formKey"
                >
                    <el-tabs v-model="state.activeTab" type="border-card">
                        <el-tab-pane class="config-tab-pane" name="base" label="嵌入">
                            <div v-if="state.activeTab == 'base'" class="config-form-item">
                                <el-alert
                                    class="tab-pane-alert"
                                    title="将知识点内容`嵌入`到`向量`里（文本向量化），以达到最佳的`检索`功能实现。我们可以使用阿里或ChatGPT的嵌入模型。"
                                    type="success"
                                    :closable="false"
                                />
                                <FormItem
                                    label="接口类型"
                                    prop="ai_api_type"
                                    type="select"
                                    v-model="state.form.ai_api_type"
                                    :data="{ content: state.embeddingModelSelectContent }"
                                    :attr="{
                                        blockHelp: '请注意，修改接口类型意味着所有的知识点需要重新向量化',
                                    }"
                                    :key="JSON.stringify(state.embeddingModelSelectContent)"
                                    :input-attr="{
                                        onChange: onApiTypeChage,
                                    }"
                                />
                                <FormItem
                                    label="API KEY"
                                    prop="ai_api_key"
                                    type="string"
                                    v-model="state.form.ai_api_key"
                                    placeholder="请输入API KEY"
                                />
                                <FormItem
                                    label="接口基础URL"
                                    prop="ai_api_url"
                                    type="string"
                                    v-model="state.form.ai_api_url"
                                    placeholder="仅在您需要自定义时，在此填写"
                                />
                                <FormItem
                                    label="代理"
                                    prop="ai_proxy"
                                    type="string"
                                    v-model="state.form.ai_proxy"
                                    placeholder="如果需要使用代理服务器请求AI相关接口，请在此填写"
                                />
                                <FormItem
                                    label="运行模式"
                                    prop="ai_work_mode"
                                    type="radio"
                                    v-model="state.form.ai_work_mode"
                                    :data="{ content: { mysql: 'PHP+MySQL', redis: 'Redis+MySQL' } }"
                                    :attr="{
                                        blockHelp:
                                            'PHP模式一次性取出所有知识点，遍历做向量相似性计算再取最高得分，100 条数据即达默认内存容量极限，Redis模式 10K 数据未见性能损失和内存异常增长；MySQL:仅支持存储,PHP:可做相似性计算,Redis:高速缓存+向量索引+相似性计算',
                                    }"
                                />
                                <FormItem
                                    label="精准命中相似度"
                                    prop="ai_accurate_hit"
                                    type="number"
                                    v-model.number="state.form.ai_accurate_hit"
                                    :input-attr="{
                                        step: 0.01,
                                        valueOnClear: 0,
                                    }"
                                    :attr="{
                                        blockHelp:
                                            '知识点被精准命中的次数会被记录，以便优化；当用户搜索、提问与知识点的相似度达到此值时，被认为是精准命中了，取值范围：0-1',
                                    }"
                                />
                                <FormItem
                                    label="AI新会员送tokens"
                                    prop="ai_gift_tokens"
                                    type="number"
                                    v-model.number="state.form.ai_gift_tokens"
                                    :input-attr="{
                                        step: 100,
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    label="AI会员积分兑换tokens"
                                    prop="ai_score_exchange_tokens"
                                    type="number"
                                    v-model.number="state.form.ai_score_exchange_tokens"
                                    :input-attr="{
                                        step: 10,
                                        valueOnClear: 0,
                                    }"
                                    :attr="{
                                        blockHelp: '每积分可以兑换的token数，输入 0 则关闭积分兑换',
                                    }"
                                />
                                <FormItem
                                    label="AI会员余额兑换tokens"
                                    prop="ai_money_exchange_tokens"
                                    type="number"
                                    v-model.number="state.form.ai_money_exchange_tokens"
                                    :input-attr="{
                                        step: 10,
                                        valueOnClear: 0,
                                    }"
                                    :attr="{
                                        blockHelp: '每1元余额可以兑换的token数，输入 0 则关闭余额兑换',
                                    }"
                                />
                                <el-button :loading="state.submitLoading" type="primary" @click="onSubmit(formRef)">{{ $t('Save') }}</el-button>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane class="config-tab-pane" name="chat" label="对话/聊天">
                            <div v-if="state.activeTab == 'chat'" class="config-form-item">
                                <FormItem
                                    label="自动新会话间隔时间（分钟）"
                                    type="number"
                                    v-model="state.form.auto_new_session_interval"
                                    block-help="超过此时间没有通信，并且重新打开会话页面时将自动创建一个新会话"
                                />
                                <FormItem label="对话AI默认头像" type="image" v-model="state.form.ai_session_logo" />
                                <FormItem label="对话窗口默认标题" prop="ai_session_title" type="string" v-model="state.form.ai_session_title" />
                                <FormItem
                                    label="对话默认欢迎文案"
                                    prop="ai_greeting"
                                    type="textarea"
                                    v-model="state.form.ai_greeting"
                                    :input-attr="{
                                        rows: 1,
                                    }"
                                    placeholder="用户首次使用聊天时的欢迎语，不消耗 tokens"
                                />
                                <FormItem
                                    label="系统提示词"
                                    prop="ai_system_prompt"
                                    type="textarea"
                                    v-model="state.form.ai_system_prompt"
                                    :input-attr="{
                                        rows: 1,
                                        onChange: onPrompt,
                                    }"
                                    :attr="{
                                        blockHelp: '系统级提示',
                                    }"
                                />
                                <FormItem
                                    label="限定提示词"
                                    prop="ai_prompt"
                                    type="textarea"
                                    v-model="state.form.ai_prompt"
                                    :input-attr="{
                                        rows: 6,
                                        onChange: onPrompt,
                                    }"
                                    :attr="{
                                        blockHelp:
                                            '系统向AI模型接口交互时，这段文案将自动被放置在内容最开始的位置，可用于指定AI身份、边界、如何回复等。',
                                    }"
                                />
                                <FormItem
                                    label="限定提示词（无匹配知识点）"
                                    prop="ai_prompt_no_reference"
                                    type="textarea"
                                    v-model="state.form.ai_prompt_no_reference"
                                    :input-attr="{
                                        rows: 3,
                                    }"
                                    :attr="{
                                        blockHelp: '没有匹配知识点时，可以使用另外的限定提示词，不填则不使用',
                                    }"
                                />
                                <el-form-item label="短期记忆对话论数">
                                    <el-slider
                                        v-model="state.form.ai_short_memory"
                                        :min="0"
                                        :max="20"
                                        :step="1"
                                        show-input
                                        @change="onAiShortMemoryChange(state.form.ai_short_memory)"
                                    />
                                    <div class="block-help">向AI发送最近几轮对话记录，若为0，则AI将失去连续对话能力。</div>
                                </el-form-item>
                                <el-form-item label="有效知识相似度">
                                    <el-slider v-model="state.form.ai_effective_match_kbs" :min="0" :max="1" :step="0.1" show-input />
                                    <div class="block-help">知识点与用户提问的相似度达到指定值时，才作为参考资料发送给AI。</div>
                                </el-form-item>
                                <el-form-item label="有效知识数量">
                                    <el-slider v-model="state.form.ai_effective_kbs_count" :min="1" :max="20" :step="1" show-input />
                                    <div class="block-help">
                                        从知识库选取相似度最高的几条知识点，作为参考资料发送给AI，比如设置为5，且有5个以上知识点达到有效相似度要求，则只发送前5条。
                                    </div>
                                </el-form-item>
                                <el-form-item label="无关认定相似度">
                                    <el-slider v-model="state.form.ai_irrelevant_determination" :min="0" :max="0.9" :step="0.1" show-input />
                                    <div class="block-help">用户提问与所有知识点的相似度都未能达到以上值，则认为是无关的问题。</div>
                                </el-form-item>
                                <FormItem
                                    label="无关问题响应文案"
                                    prop="ai_irrelevant_message"
                                    type="textarea"
                                    v-model="state.form.ai_irrelevant_message"
                                    :input-attr="{
                                        rows: 2,
                                    }"
                                    :attr="{
                                        blockHelp: '一个问题被认为无关时，直接回复以上文案，不填写则可以继续交由AI回复',
                                    }"
                                />
                                <br />
                                <el-divider content-position="left"> 前台用户对话相关 </el-divider>
                                <FormItem label="允许用户关闭知识库" type="switch" v-model="state.form.ai_allow_users_close_kbs" />
                                <FormItem label="允许用户选择知识库" type="switch" v-model="state.form.ai_allow_users_select_kbs" />
                                <FormItem
                                    label="允许用户调整有效知识设定"
                                    :attr="{
                                        blockHelp: '开启后，知识点无关认定将失效',
                                    }"
                                    type="switch"
                                    v-model="state.form.ai_allow_users_edit_effective_kbs"
                                />
                                <FormItem label="允许用户修改提示词" type="switch" v-model="state.form.ai_allow_users_edit_prompt" />
                                <FormItem label="允许用户修改 tokens 占比" type="switch" v-model="state.form.ai_allow_users_edit_tokens" />
                                <FormItem label="自动发送知识点更新情况" type="switch" v-model="state.form.ai_send_kbs_update_status" />
                                <br />
                                <FormItem
                                    :key="state.form.ai_api_type"
                                    label="AI模型"
                                    prop="ai_model"
                                    type="select"
                                    v-model="state.form.ai_model"
                                    :data="{
                                        content: state.apiModelNames,
                                    }"
                                    :input-attr="{
                                        onChange: onAiModelChange,
                                    }"
                                    :attr="{
                                        blockHelp: '选择一个模型后可在下方预览 tokens 占比情况',
                                    }"
                                />
                                <br />
                                <TokensPercent />
                                <el-button :loading="state.submitLoading" type="primary" @click="onSubmit(formRef)">{{ $t('Save') }}</el-button>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane class="config-tab-pane" name="redis" label="Redis">
                            <div v-if="state.activeTab == 'redis'" class="config-form-item">
                                <el-alert
                                    class="tab-pane-alert"
                                    title="仅在运行模式为：Redis+MySQL 下有效，请先在 config/ai.php 配置好 Redis 的驱动信息"
                                    type="warning"
                                    :closable="false"
                                />
                                <FormItem
                                    label="向量索引类型"
                                    prop="ai_vector_index"
                                    type="select"
                                    v-model="state.form.ai_vector_index"
                                    :data="{
                                        content: {
                                            FLAT: '蛮力（时间换准确度）',
                                            HNSW: '分层可导航小世界（准确度换时间）',
                                        },
                                    }"
                                />
                                <FormItem
                                    label="向量相似性算法"
                                    prop="ai_vector_similarity"
                                    type="select"
                                    v-model="state.form.ai_vector_similarity"
                                    :data="{
                                        content: {
                                            L2: '欧氏距离',
                                            IP: '内积',
                                            COSINE: '余弦距离',
                                        },
                                    }"
                                    :attr="{
                                        blockHelp: '默认为余弦距离，它和 PHP+MySQL 模式下的余弦相似性类似',
                                    }"
                                    :input-attr="{
                                        disabled: true,
                                    }"
                                />
                                <FormItem
                                    label="初始索引内存大小"
                                    prop="ai_initial_cap"
                                    type="number"
                                    v-model.number="state.form.ai_initial_cap"
                                    :attr="{
                                        blockHelp: '分配的初始内存不足时会自动调整，但会出现内存分配开销',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    v-if="state.form.ai_vector_index == 'FLAT'"
                                    label="分块大小"
                                    prop="ai_flat_block_size"
                                    type="number"
                                    v-model.number="state.form.ai_flat_block_size"
                                    :attr="{
                                        blockHelp:
                                            '索引内部使用连续数组保存向量数据，一个数组可理解为一个块，若向量数据总是添加和删除时，可以调整此值来降低块分配和释放的频率',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    v-if="state.form.ai_vector_index == 'HNSW'"
                                    label="最大前候选"
                                    prop="ai_hnsw_knn_ef_runtime"
                                    type="number"
                                    v-model.number="state.form.ai_hnsw_knn_ef_runtime"
                                    :attr="{
                                        blockHelp: '前=最高相似得分；搜索期间要保持的最大前候选者数量，值越高，结果越准确，消耗时间越长',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    v-if="state.form.ai_vector_index == 'HNSW'"
                                    label="EPSILON"
                                    prop="ai_hnsw_epsilon"
                                    type="number"
                                    v-model.number="state.form.ai_hnsw_epsilon"
                                    :attr="{
                                        blockHelp: '值越大，则从越广泛的范围搜索并得到更准确的结果（以运行时间为代价）。',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    v-if="state.form.ai_vector_index == 'HNSW'"
                                    label="图的每层每节点最大出边数"
                                    prop="ai_hnsw_m"
                                    type="number"
                                    v-model.number="state.form.ai_hnsw_m"
                                    :attr="{
                                        blockHelp: '根据数据复杂度设定',
                                    }"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <FormItem
                                    v-if="state.form.ai_vector_index == 'HNSW'"
                                    label="潜在出边候选数"
                                    prop="ai_hnsw_ef_construction"
                                    type="number"
                                    v-model.number="state.form.ai_hnsw_ef_construction"
                                    :input-attr="{
                                        valueOnClear: 0,
                                    }"
                                />
                                <el-button :loading="state.submitLoading" type="primary" @click="onSubmit(formRef)">{{ $t('Save') }}</el-button>
                                <el-popconfirm
                                    @confirm="onClearRedis()"
                                    :width="260"
                                    title="删除位于 redis 内的所有缓存的数据和索引，直到你在知识点管理页面重建它们，确定清理吗？"
                                >
                                    <template #reference>
                                        <el-button :loading="state.submitLoading" type="danger">删除缓存数据和索引</el-button>
                                    </template>
                                </el-popconfirm>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </el-form>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { uuid } from '/@/utils/random'
import FormItem from '/@/components/formItem/index.vue'
import { FormInstance } from 'element-plus'
import { index, save, clear } from '/@/api/backend/ai/index'
import { buildValidatorData } from '/@/utils/validate'
import { State } from './index'
import TokensPercent from '/@/components/ai/tokensPercent.vue'
import { aiPercent, setChatModelAttr, setTokenConfig, onAiShortMemoryChange, onPromptChange } from '/@/components/ai/index'

defineOptions({
    name: 'ai/config',
})

const state: State = reactive({
    loading: false,
    form: {
        ai_api_type: 'ali',
        ai_api_key: '',
        ai_api_url: '',
        ai_proxy: '',
        ai_work_mode: 'mysql',
        ai_accurate_hit: 0.9,
        ai_model: '',
        ai_greeting: '',
        ai_system_prompt: '',
        ai_prompt: '',
        ai_prompt_no_reference: '',
        ai_short_memory: 5,
        ai_effective_match_kbs: 0.8,
        ai_effective_kbs_count: 5,
        ai_irrelevant_determination: 0.1,
        ai_irrelevant_message: '非常抱歉，我未能找到相关信息。您可以换个问法~',
        ai_vector_index: 'FLAT',
        ai_vector_similarity: 'COSINE',
        ai_initial_cap: 1000000,
        ai_flat_block_size: 1024,
        ai_hnsw_m: 16,
        ai_hnsw_ef_construction: 200,
        ai_hnsw_knn_ef_runtime: 10,
        ai_hnsw_epsilon: 0.01,
        ai_allow_users_close_kbs: true,
        ai_allow_users_select_kbs: true,
        ai_allow_users_edit_prompt: true,
        ai_allow_users_edit_tokens: true,
        ai_allow_users_edit_effective_kbs: true,
        ai_send_kbs_update_status: true,
        auto_new_session_interval: 60,
        ai_session_title: '',
        ai_session_logo: '',
        ai_gift_tokens: 0,
        ai_score_exchange_tokens: 0,
        ai_money_exchange_tokens: 0,
    },
    rules: {
        ai_api_key: [buildValidatorData({ name: 'required', title: 'API KEY' })],
        ai_session_title: [buildValidatorData({ name: 'required', title: '会话窗口标题' })],
        ai_score_exchange_tokens: [buildValidatorData({ name: 'integer', title: '积分兑tokens' })],
        ai_money_exchange_tokens: [buildValidatorData({ name: 'integer', title: '余额兑tokens' })],
    },
    formKey: uuid(),
    activeTab: 'base',
    submitLoading: false,
    apiModelNames: {},
    chatModelAttr: {},
    embeddingModelAttr: {},
    embeddingModelSelectContent: {},
})

const formRef = ref<FormInstance>()

const onSubmit = (formRef?: FormInstance) => {
    if (!formRef) return
    formRef.validate((valid: boolean) => {
        if (valid) {
            state.submitLoading = true

            const percentSalue: anyObj = {}
            for (const key in aiPercent.percent) {
                percentSalue[key] = aiPercent.percent[key].value
            }

            save({ ...state.form, tab: state.activeTab, ...percentSalue })
                .then(() => {})
                .finally(() => {
                    state.submitLoading = false
                })
        }
    })
}

/**
 * 清理redis缓存和索引
 */
const onClearRedis = () => {
    state.submitLoading = true
    clear()
        .then(() => {})
        .finally(() => {
            state.submitLoading = false
        })
}

index().then((res) => {
    state.form = res.data.data
    state.apiModelNames = res.data.apiModelNames
    state.chatModelAttr = res.data.chatModelAttr
    state.embeddingModelAttr = res.data.embeddingModelAttr
    for (const key in res.data.embeddingModelAttr) {
        state.embeddingModelSelectContent[key] =
            res.data.embeddingModelAttr[key].title +
            ' - ' +
            res.data.embeddingModelAttr[key].name +
            ' - 单次可嵌入文本长度:' +
            res.data.embeddingModelAttr[key].max_tokens
    }

    // 初始化token占比和模型token数据
    setChatModelAttr(state.chatModelAttr[state.form.ai_model])
    setTokenConfig(state.form, res.data.promptTokenCount)
})

/**
 * API类型切换
 */
const onApiTypeChage = () => {
    state.form.ai_model = ''
    onAiModelChange()
}

/**
 * AI模型切换
 */
const onAiModelChange = () => {
    setChatModelAttr(state.chatModelAttr[state.form.ai_model])
}

/**
 * 重新计算提示词占比
 */
const onPrompt = () => {
    onPromptChange(state.form.ai_system_prompt, state.form.ai_prompt)
}
</script>

<style scoped lang="scss">
.ba-input-item-switch {
    display: flex;
    justify-content: center;
    :deep(.el-form-item__label) {
        display: flex;
        align-items: center;
        margin-bottom: 0 !important;
    }
}
.tab-pane-alert {
    margin-bottom: 10px;
}
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
