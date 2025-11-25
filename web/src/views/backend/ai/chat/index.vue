<template>
    <div class="default-main">
        <el-row class="chat-row">
            <el-col :xs="24" :sm="18" class="chat-box">
                <SessionWindow />
            </el-col>
            <el-col class="hidden-xs-only ai-config" v-loading="sessionState.loading" :sm="6">
                <el-scrollbar height="calc(100vh - 120px)">
                    <el-form v-if="!sessionState.loading" :model="sessionState.form" :rules="sessionState.rules" label-position="top">
                        <el-form-item>
                            <el-popconfirm @confirm="onResetConfig(getInitData)" title="确定恢复默认配置？">
                                <template #reference>
                                    <el-button>恢复为默认配置</el-button>
                                </template>
                            </el-popconfirm>
                        </el-form-item>

                        <FormItem
                            label="AI模型"
                            :key="JSON.stringify(sessionState.usableModelContent)"
                            type="select"
                            v-model="sessionState.form.ai_model"
                            :data="{ content: sessionState.usableModelContent }"
                            :input-attr="{
                                onChange: onChangeModel,
                                clearable: false,
                            }"
                        />

                        <FormItem
                            v-if="
                                !isEmpty(sessionState.chatModelAttr) &&
                                !sessionState.chatModelAttr[sessionState.form.ai_model].unsupported_parameters.includes('ai_system_prompt')
                            "
                            label="系统提示词"
                            prop="ai_system_prompt"
                            type="textarea"
                            v-model="sessionState.form.ai_system_prompt"
                            :input-attr="{
                                rows: 1,
                                onChange: onPrompt,
                            }"
                        />
                        <FormItem
                            label="限定提示词"
                            prop="ai_prompt"
                            type="textarea"
                            v-model="sessionState.form.ai_prompt"
                            :input-attr="{
                                rows: 3,
                                onChange: onPrompt,
                            }"
                            placeholder="系统向AI模型接口交互时，这段文案将自动被放置在内容最开始的位置，可用于指定AI身份、边界、如何回复等。"
                        />

                        <el-form-item
                            label="温度"
                            v-if="
                                !isEmpty(sessionState.chatModelAttr) &&
                                !sessionState.chatModelAttr[sessionState.form.ai_model].unsupported_parameters.includes('temperature')
                            "
                        >
                            <el-slider v-model="sessionState.form.temperature" :min="0" :max="2" :step="0.1" show-input />
                            <div class="block-help">值越大，低概率词越可能被选择，结果更多样化；值越小，确定性越高</div>
                        </el-form-item>

                        <el-form-item
                            label="核采样"
                            v-if="
                                !isEmpty(sessionState.chatModelAttr) &&
                                !sessionState.chatModelAttr[sessionState.form.ai_model].unsupported_parameters.includes('top_p')
                            "
                        >
                            <el-slider v-model="sessionState.form.top_p" :min="0" :max="1" :step="0.1" show-input />
                            <div class="block-help">
                                值越大，随机性越高，值越小，确定性越高；0.1意味着仅考虑前10%质量的令牌。建议更改此选项或温度，但不同时更改两者。阿里千问默认0.5，OpenAi默认1。
                            </div>
                        </el-form-item>

                        <el-form-item
                            label="核采样候选集大小"
                            v-if="
                                !isEmpty(sessionState.chatModelAttr) &&
                                !sessionState.chatModelAttr[sessionState.form.ai_model].unsupported_parameters.includes('top_k')
                            "
                        >
                            <el-slider v-model="sessionState.form.top_k" :min="0" :max="100" :step="1" show-input />
                            <div class="block-help">值越大，随机性越高，值越小，确定性越高；默认值0表示不启用此策略，仅核采样和温度生效。</div>
                        </el-form-item>

                        <el-divider content-position="left">知识库</el-divider>
                        <FormItem
                            label="是否开启知识库"
                            prop="ai_kbs_open"
                            type="switch"
                            :attr="{
                                blockHelp: '关闭时，建议修改提示词，可以和建议使用 ${question}，哪怕只有一个 ${question}',
                            }"
                            v-model="sessionState.form.ai_kbs_open"
                        />
                        <el-form-item label="有效知识相似度">
                            <el-slider v-model="sessionState.form.ai_effective_match_kbs" :min="0" :max="1" :step="0.1" show-input />
                            <div class="block-help">知识点与用户提问的相似度达到指定值时，才作为参考资料发送给AI。</div>
                        </el-form-item>
                        <el-form-item label="有效知识数量">
                            <el-slider v-model="sessionState.form.ai_effective_kbs_count" :min="1" :max="20" :step="1" show-input />
                            <div class="block-help">
                                从知识库选取相似度最高的几条知识点，作为参考资料发送给AI，比如设置为5，且有5个以上知识点达到有效相似度要求，则只发送前5条。
                            </div>
                        </el-form-item>
                        <FormItem
                            label="知识库"
                            type="remoteSelects"
                            v-model="sessionState.form.ai_kbs"
                            :input-attr="{
                                pk: 'kbs.id',
                                field: 'name',
                                remoteUrl: '/admin/ai.Kbs/index',
                                clearable: true,
                                params: {
                                    search: [
                                        {
                                            field: 'status',
                                            val: '1',
                                            operator: 'eq',
                                            render: 'switch',
                                        },
                                    ],
                                },
                            }"
                            placeholder="限定知识库（可选）"
                        />
                        <FormItem
                            label="知识点类型"
                            type="select"
                            v-model="sessionState.form.ai_kbs_type"
                            :data="{ content: { qa: '问答对', text: '普通文档' } }"
                            placeholder="限定知识类型（可选）"
                        />
                        <FormItem
                            label="知识点向量数据类型"
                            type="select"
                            v-model="sessionState.form.ai_kbs_text_type"
                            :data="{ content: { query: '检索优化', document: '底库文本' } }"
                            placeholder="限定知识点向量类型"
                        />

                        <el-form-item label="无关认定相似度">
                            <el-slider v-model="sessionState.form.ai_irrelevant_determination" :min="0" :max="0.9" :step="0.1" show-input />
                            <div class="block-help">用户提问与所有知识点的相似度都未能达到以上值，则认为是无关的问题。</div>
                        </el-form-item>
                        <FormItem
                            label="无关问题响应文案"
                            type="textarea"
                            v-model="sessionState.form.ai_irrelevant_message"
                            :input-attr="{
                                rows: 2,
                            }"
                            :attr="{
                                blockHelp: '一个问题被认为无关时，直接回复以上文案，不填写则可以继续交由AI回复',
                            }"
                        />

                        <el-divider content-position="left">扩展</el-divider>

                        <FormItem
                            v-if="
                                !isEmpty(sessionState.chatModelAttr) &&
                                !sessionState.chatModelAttr[sessionState.form.ai_model].unsupported_parameters.includes('ai_enable_search')
                            "
                            label="参考夸克搜索结果"
                            type="switch"
                            v-model="sessionState.form.ai_enable_search"
                        />

                        <el-form-item label="短期记忆对话论数">
                            <el-slider
                                v-model="sessionState.form.ai_short_memory"
                                :min="0"
                                :max="20"
                                :step="1"
                                show-input
                                @change="onAiShortMemoryChange(sessionState.form.ai_short_memory)"
                            />
                            <div class="block-help">向AI发送最近几轮对话记录，若为0，则AI将失去连续对话能力。</div>
                        </el-form-item>

                        <el-form-item
                            v-if="
                                !isEmpty(sessionState.chatModelAttr) &&
                                !sessionState.chatModelAttr[sessionState.form.ai_model].unsupported_parameters.includes('ai_presence_penalty')
                            "
                            label="话题新鲜度"
                        >
                            <el-slider v-model="sessionState.form.ai_presence_penalty" :min="-2" :max="2" :step="0.1" show-input />
                            <div class="block-help">值越大，谈论新主题的可能性越高，但样本的质量可能越低。</div>
                        </el-form-item>

                        <FormItem
                            label="回复内容最大tokens数"
                            prop="ai_max_tokens"
                            type="number"
                            v-model.number="sessionState.form.ai_max_tokens"
                            :attr="{
                                blockHelp: '用于限制回复内容长度',
                            }"
                            :input-attr="{
                                valueOnClear: 0,
                            }"
                        />

                        <el-form-item
                            v-if="
                                !isEmpty(sessionState.chatModelAttr) &&
                                !sessionState.chatModelAttr[sessionState.form.ai_model].unsupported_parameters.includes('ai_frequency_penalty')
                            "
                            label="字词频率惩罚"
                        >
                            <el-slider v-model="sessionState.form.ai_frequency_penalty" :min="-2" :max="2" :step="0.1" show-input />
                            <div class="block-help">值越大，文字重复概率越低，但样本的质量可能越低。</div>
                        </el-form-item>

                        <TokensPercent />
                    </el-form>
                </el-scrollbar>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { watch, nextTick } from 'vue'
import { chatInit } from '/@/api/ai'
import FormItem from '/@/components/formItem/index.vue'
import TokensPercent from '/@/components/ai/tokensPercent.vue'
import SessionWindow from '/@/components/ai/session.vue'
import { fullUrl } from '/@/utils/common'
import { Local } from '/@/utils/storage'
import { isEmpty } from 'lodash-es'
import { sessionState, changeSession, onResetConfig, cacheConfig, USER_AI_CONFIG } from '/@/components/ai/index'
import { aiPercent, setChatModelAttr, setTokenConfig, onAiShortMemoryChange, onPromptChange, addMessage } from '/@/components/ai/index'

defineOptions({
    name: 'ai/chat',
})

const getInitData = () => {
    sessionState.loading = true
    chatInit()
        .then((res) => {
            // 可用模型-模型配置管理数据
            sessionState.usableModel = res.data.usableModel
            for (const key in sessionState.usableModel) {
                sessionState.usableModelContent[key] = sessionState.usableModel[key].optimize_name
            }

            // 模型属性-模型预设属性
            sessionState.chatModelAttr = res.data.chatModelAttr

            // 配置缓存
            const cacheConfigData = Local.get(USER_AI_CONFIG)
            const initData = cacheConfigData || res.data.data

            // 配置表单初始化
            // 一些配置总是使用服务端的最新值
            const serverPriority = {
                ai_session_title: res.data.data.ai_session_title,
                ai_session_logo: res.data.data.ai_session_logo,
                ai_allow_users_close_kbs: res.data.data.ai_allow_users_close_kbs,
                ai_allow_users_select_kbs: res.data.data.ai_allow_users_select_kbs,
                ai_allow_users_edit_effective_kbs: res.data.data.ai_allow_users_edit_effective_kbs,
                ai_allow_users_edit_prompt: res.data.data.ai_allow_users_edit_prompt,
                ai_allow_users_edit_tokens: res.data.data.ai_allow_users_edit_tokens,
            }
            sessionState.form = { ...sessionState.form, ...initData, ...serverPriority }

            // 如果缓存中的模型不可用，恢复为默认配置
            if (!sessionState.usableModel[sessionState.form.ai_model]) {
                sessionState.form.ai_model = res.data.data['ai_model']
            }

            // 初始化token占比和模型token数据
            setChatModelAttr(sessionState.chatModelAttr[sessionState.form.ai_model])
            setTokenConfig(initData, res.data.promptTokenCount)

            // AI属性与默认值取值
            const title = sessionState.usableModel[sessionState.form.ai_model].title || sessionState.form.ai_session_title
            const avatar = sessionState.usableModel[sessionState.form.ai_model].logo || sessionState.form.ai_session_logo

            // 会话列表
            sessionState.sessionList = res.data.sessionList
            if (sessionState.sessionList.length) {
                changeSession(0, () => {
                    // 知识点更新情况
                    if (res.data.kbsUpdateStatusMessage) {
                        addMessage(
                            {
                                content: res.data.kbsUpdateStatusMessage,
                                type: 'system',
                            },
                            'desc'
                        )
                    }
                })
            }

            // 会话窗口属性
            sessionState.sessionInfo.title = title
            sessionState.sessionInfo.aiInfo = {
                nickname: title,
                avatar: fullUrl(avatar),
            }

            // 开启配缓存功能
            sessionState.cacheConfigSwitch = true
        })
        .finally(() => {
            sessionState.loading = false
        })
}

const onChangeModel = () => {
    setChatModelAttr(sessionState.chatModelAttr[sessionState.form.ai_model])
    // AI属性与默认值取值
    const title = sessionState.usableModel[sessionState.form.ai_model].title || sessionState.form.ai_session_title
    const avatar = sessionState.usableModel[sessionState.form.ai_model].logo || sessionState.form.ai_session_logo
    const greeting = sessionState.usableModel[sessionState.form.ai_model].greeting || sessionState.form.ai_greeting

    // 会话窗口属性
    sessionState.sessionInfo.title = title
    sessionState.sessionInfo.aiInfo = {
        nickname: title,
        avatar: fullUrl(avatar),
    }

    // 发送欢迎消息
    nextTick(() => {
        addMessage(
            {
                content: greeting,
                type: 'you',
            },
            'desc'
        )
    })
}

/**
 * 重新计算提示词占比
 */
const onPrompt = () => {
    onPromptChange(sessionState.form.ai_system_prompt, sessionState.form.ai_prompt)
}

watch(
    () => sessionState.form,
    () => {
        cacheConfig()
    },
    {
        deep: true,
    }
)
watch(
    () => aiPercent.percent,
    () => {
        cacheConfig()
    },
    {
        deep: true,
    }
)

getInitData()
</script>

<style scoped lang="scss">
.default-main {
    margin-bottom: 0;
    margin-left: 26px;
}
.chat-row {
    display: flex;
    justify-content: space-between;
}
.ai-config {
    border-radius: 4px;
    background-color: #fbfbfb;
    :deep(.el-scrollbar__view) {
        padding: 16px;
    }
    .chat-alert {
        margin-bottom: 10px;
    }
}
.chat-box {
    margin-left: -10px;
    height: calc(100vh - 120px);
    border-radius: 4px;
    background-color: var(--el-fill-color-blank);
}
.ba-input-item-switch {
    display: flex;
    justify-content: center;
    :deep(.el-form-item__label) {
        display: flex;
        align-items: center;
        margin-bottom: 0 !important;
    }
}
</style>
