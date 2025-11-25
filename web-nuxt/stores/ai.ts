import { AiState, AiTokensPercent, AiChatModelTokenAttr, USER_AI_CONFIG } from '~/stores/interface/ai'

interface Tokens {
    tokenData: AiChatModelTokenAttr
    percent: AiTokensPercent
    init: boolean
}
interface State {
    state: AiState
    tokens: Tokens
}

const defaultFormData = {
    ai_model: '',
    ai_system_prompt: '',
    ai_prompt: '',
    temperature: 1.0,
    top_p: 0.5,
    top_k: 0,
    ai_short_memory: 5,
    ai_kbs_open: true,
    ai_kbs: [],
    ai_effective_match_kbs: 0.5,
    ai_effective_kbs_count: 5,
    ai_kbs_type: '',
    ai_kbs_text_type: 'query',
    ai_irrelevant_determination: 0.2,
    ai_irrelevant_message: '',
    ai_enable_search: false,
    ai_presence_penalty: 0,
    ai_frequency_penalty: 0,
    ai_max_tokens: 0,
    init: false,
}

const defaultPercentData = {
    ai_short_memory_percent: {
        value: 0,
        min: 5,
        change: 'ai_kbs_percent',
    },
    ai_prompt_percent: {
        value: 0,
        min: 0,
        change: 'ai_kbs_percent',
    },
    ai_kbs_percent: {
        value: 0,
        min: 5,
        change: 'ai_user_input_percent',
    },
    ai_user_input_percent: {
        value: 0,
        min: 5,
        change: 'ai_response_percent',
    },
    ai_response_percent: {
        value: 0,
        min: 5,
        change: 'ai_short_memory_percent',
    },
}

export const useAi = defineStore('ai', {
    state: (): State => {
        return {
            state: {
                loading: true,
                form: defaultFormData,
                rules: {},
                usableModel: {},
                usableModelContent: {},
                chatModelAttr: {},
                sessionInfo: {
                    title: '',
                    aiInfo: {
                        nickname: '',
                        avatar: '',
                    },
                    activeSessionId: 0,
                    activeSessionIdx: 0,
                },
                sessionList: [],
                aiOutputMessageKey: null,
                messages: [],
                messageStatus: {
                    loading: false,
                    page: 1,
                    nextPage: false,
                },
                aiUserInfo: {
                    id: 0,
                    user_type: '',
                    tokens: 0,
                },
                unexpectedRecords: 0,
                ready: false,
            },
            tokens: {
                tokenData: {
                    sum: 0,
                    reserve: 0,
                    assignable: 0,
                },
                percent: defaultPercentData,
                init: false,
            },
        }
    },
    actions: {
        resetState() {
            this.state.form = defaultFormData
            this.tokens.percent = defaultPercentData
        },
    },
    persist: {
        key: USER_AI_CONFIG,
        pick: ['state.form', 'tokens'],
    },
})
