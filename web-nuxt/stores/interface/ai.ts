// 会员资料
export const USER_AI_CONFIG = 'userAiConfig-v1'
// 水印元素ID号
export const WATER_MARK_ID = 'water-mark'

/**
 * Token占比
 */
export interface AiTokensPercent {
    [key: string]: {
        value: number
        min: number
        change: string
    }
}

/**
 * 模型Token配置
 */
export interface AiChatModelTokenAttr {
    // 模型总tokens上下文
    sum: number
    // 模型为输出预留了tokens数量
    reserve: number
    // 可以分配的
    assignable?: number
}

/**
 * 对话窗口配置
 */
export interface AiConfigForm {
    ai_model: string
    ai_system_prompt: string
    ai_prompt: string
    temperature: number
    top_p: number
    top_k: number
    ai_short_memory: number
    ai_kbs_open: boolean
    ai_kbs: string[]
    ai_effective_match_kbs: number
    ai_effective_kbs_count: number
    ai_kbs_type: string
    ai_kbs_text_type: string
    ai_irrelevant_determination: number
    ai_irrelevant_message: string
    ai_enable_search: boolean
    ai_presence_penalty: number
    ai_frequency_penalty: number
    ai_max_tokens: number
    init: boolean
    [key: string]: any
}

/**
 * 消息
 */
export interface AiMessageItem {
    id?: number
    type: 'you' | 'me' | 'system'
    avatar?: string
    nickname?: string
    reasoning_content?: string
    content: string
    tokens?: number
    message_type?: 'text' | 'image' | 'link'
    loading?: boolean
    kbs?: string[]
    kbsTable?: {
        title: string[]
    }
    consumption_tokens?: number
    // 临时消息ID
    uuid?: string
    // link的锚点
    title?: string
}

/**
 * 对话窗口状态
 */
export interface AiState {
    loading: boolean
    form: AiConfigForm
    rules: anyObj
    usableModel: anyObj
    usableModelContent: anyObj
    chatModelAttr: anyObj
    sessionInfo: {
        title: string
        aiInfo: {
            nickname: string
            avatar: string
        }
        activeSessionId: number
        activeSessionIdx: number
    }
    sessionList: {
        id: number
        title: string
        create_time: string
        operateLoading: boolean
    }[]
    aiOutputMessageKey: number | null
    messages: AiMessageItem[]
    messageStatus: {
        loading: boolean
        page: number
        nextPage: boolean
    }
    aiUserInfo: {
        id: number
        user_type: string
        tokens: number
    }
    unexpectedRecords: number
    // AI对话准备好(初始化请求已结束、聊天记录已经获取等)
    ready: boolean
}
