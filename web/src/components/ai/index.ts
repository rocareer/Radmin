import { nextTick, reactive } from 'vue'
import { debounce } from 'lodash-es'
import { calcTokens } from '/@/api/ai'
import { SessionState, ConfigForm, Percent, ChatModelTokenAttr, MessageItem } from './types'
import { Local } from '/@/utils/storage'
import { records } from '/@/api/ai'
import { useAdminInfo } from '/@/stores/adminInfo'
import { useUserInfo } from '/@/stores/userInfo'
import { isAdminApp } from '/@/utils/common'
import { ElNotification } from 'element-plus'

// --------------------------
// --- AI窗口
// --- 1. 用户配置是保存于缓存的
// --------------------------

// 配置缓存key
export const USER_AI_CONFIG = 'user_ai_config'

// 水印元素ID号
export const WATER_MARK_ID = 'water-mark'

/**
 * AI窗口状态
 */
export const sessionState: SessionState = reactive({
    loading: true,
    form: {
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
    },
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
    formBack: {},
    cacheConfigSwitch: false,
    aiOutputMessageKey: null,
    messages: [],
    messageStatus: {
        loading: false,
        page: 1,
        nextPage: false,
    },
    unexpectedRecords: 0,
})
// 备份配置，以便恢复默认
sessionState.formBack = sessionState.form

/**
 * 聊天记录 - 设置是否还有下一页
 */
export const setNextPage = (value: boolean) => {
    if (!value && sessionState.messageStatus.page > 1) {
        addMessage({
            type: 'system',
            content: '没有更多消息了~',
        })
    }
    sessionState.messageStatus.nextPage = value
}

/**
 * 聊天记录 - 滚动条监听翻页
 */
export const transitionGroupScroll = debounce((e: Event) => {
    const target = e.target as HTMLDivElement
    const scrollTop = Math.abs(target.scrollTop)
    const scrollHeight = target.scrollHeight
    const clientHeight = target.clientHeight

    if (scrollTop + clientHeight + 10 >= scrollHeight && sessionState.messageStatus.nextPage) {
        sessionState.messageStatus.loading = true
        sessionState.messageStatus.page++

        records(sessionState.sessionInfo.activeSessionId, sessionState.messageStatus.page, sessionState.unexpectedRecords)
            .then((res) => {
                for (const key in res.data.messages) {
                    for (const msgKey in res.data.messages[key].data) {
                        addMessage({
                            id: res.data.messages[key].data[msgKey].id,
                            type: res.data.messages[key].data[msgKey].sender_type == 'ai' ? 'you' : 'me',
                            content: res.data.messages[key].data[msgKey].content,
                            reasoning_content: res.data.messages[key].data[msgKey].reasoning_content,
                            tokens: res.data.messages[key].data[msgKey].tokens,
                            nickname: res.data.messages[key].data[msgKey].sender_id,
                        })
                    }
                }
                setNextPage(res.data.nextPage)
            })
            .finally(() => {
                sessionState.messageStatus.loading = false
            })
    }
}, 300)

/**
 * 改变当前会话
 */
export const changeSession = (idx: number, callback = () => {}) => {
    sessionState.sessionInfo.activeSessionIdx = idx
    sessionState.sessionInfo.activeSessionId = sessionState.sessionList[idx].id
    sessionState.messages = []
    sessionState.messageStatus.loading = true
    sessionState.messageStatus.page = 1
    sessionState.unexpectedRecords = 0
    records(sessionState.sessionInfo.activeSessionId)
        .then((res) => {
            // 发送欢迎消息
            addMessage({
                content: sessionState.usableModel[sessionState.form.ai_model].greeting || sessionState.form.ai_greeting,
                type: 'you',
            })

            for (const key in res.data.messages) {
                for (const msgKey in res.data.messages[key].data) {
                    addMessage({
                        id: res.data.messages[key].data[msgKey].id,
                        type: res.data.messages[key].data[msgKey].sender_type == 'ai' ? 'you' : 'me',
                        content: res.data.messages[key].data[msgKey].content,
                        reasoning_content: res.data.messages[key].data[msgKey].reasoning_content,
                        tokens: res.data.messages[key].data[msgKey].tokens,
                        kbs: res.data.messages[key].data[msgKey].kbs,
                        kbsTable: res.data.messages[key].data[msgKey].kbsTable,
                        nickname: res.data.messages[key].data[msgKey].sender_id,
                    })
                }
                addMessage({
                    type: 'system',
                    content: res.data.messages[key].datetime,
                })
            }

            document.getElementById('message-text-input')?.focus()
            setNextPage(res.data.nextPage)
            pullMessageScrollBar()
            typeof callback == 'function' && callback()
        })
        .finally(() => {
            sessionState.messageStatus.loading = false
        })
}

/**
 * 缓存配置信息
 */
export const cacheConfig = () => {
    if (!sessionState.cacheConfigSwitch) return
    const percentSalue: anyObj = {}
    for (const key in aiPercent.percent) {
        percentSalue[key] = aiPercent.percent[key].value
    }

    Local.set(USER_AI_CONFIG, { ...sessionState.form, ...percentSalue })
}

/**
 * 重置配置
 */
export const onResetConfig = (callback: () => void) => {
    sessionState.cacheConfigSwitch = false
    Local.remove(USER_AI_CONFIG)
    sessionState.form = sessionState.formBack as ConfigForm
    callback()
}

// --------------------------
// --- 会话窗口
// --------------------------

/**
 * 窗口滚动条到底
 */
export const pullMessageScrollBar = () => {
    const messageEl = document.getElementById('im-message-scrollbar') as HTMLDivElement
    nextTick(() => {
        messageEl.scrollTop = 0
    })
}

export const linkAddTarget = () => {
    setTimeout(() => {
        const elements = document.querySelectorAll('.message-md-content a')
        for (let i = 0; i < elements.length; i++) {
            if (!elements[i].hasAttribute('target')) {
                elements[i].setAttribute('target', '_blank')
            }
        }
    }, 500)
}

/**
 * 添加消息
 * @param item
 * @param order 为避免向上翻动历史记录时窗口闪烁的问题，消息记录窗口是反转渲染的
 */
export const addMessage = (item: MessageItem, order: 'desc' | 'asc' = 'asc') => {
    // you、me是对话窗口消息上的标志，you是AI
    if (item.type == 'you') {
        item.avatar = sessionState.sessionInfo.aiInfo.avatar
        item.nickname = item.nickname ? item.nickname : sessionState.sessionInfo.aiInfo.nickname
    } else if (item.type == 'me') {
        const user = isAdminApp() ? useAdminInfo() : useUserInfo()
        item.avatar = user.avatar
        item.nickname = item.nickname ? item.nickname : user.nickname
    }
    if (!item.message_type) item.message_type = 'text'
    if (order == 'asc') {
        sessionState.messages.push(item)
    } else {
        sessionState.messages.unshift(item)
    }
    linkAddTarget()
}

// --------------------------
// --- token占比
// --------------------------

/**
 * token占比状态
 */
export const aiPercent: {
    tokenData: ChatModelTokenAttr
    percent: Percent
} = reactive({
    tokenData: {
        sum: 0,
        reserve: 0,
        assignable: 0,
    },
    percent: {
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
    },
})

/**
 * slider的tip格式化
 * @param val
 */
export const formatSliderTooltip = (val: number) => {
    return `${val}%`
}

/**
 * 百分比改变
 * @param keyName
 */
export const onSliderChange = (keyName: string) => {
    let assigned = 0
    for (const key in aiPercent.percent) {
        assigned += aiPercent.percent[key].value
    }
    allocation(aiPercent.percent[keyName].change, 100 - assigned)
}

/**
 * 递归修改token占比
 * @param keyName
 * @param value
 */
export const allocation = (keyName: string, value: number) => {
    const newValue = aiPercent.percent[keyName].value + value
    if (newValue >= aiPercent.percent[keyName].min) {
        aiPercent.percent[keyName].value = newValue
    } else {
        aiPercent.percent[keyName].value = aiPercent.percent[keyName].min
        allocation(aiPercent.percent[keyName].change, newValue - aiPercent.percent[keyName].min)
    }
}

/**
 * 检查token占比的总体合理性
 */
export const percentCheck = () => {
    onSliderChange('ai_short_memory_percent')
}

/**
 * 获取一个百分比的token数
 * @param percent 百分比
 * @param extra 需要额外加上的token数（模型保留值）
 */
export const geTokenCount = (percent: number, extra = 0) => {
    return parseInt(((percent / 100) * aiPercent.tokenData.assignable! + extra).toString())
}

/**
 * 设置模型token属性
 * @param chatModelAttr token总数、预览情况等
 */
export const setChatModelAttr = (chatModelAttr: ChatModelTokenAttr) => {
    if (chatModelAttr) {
        aiPercent.tokenData = chatModelAttr

        // 可分配的token数计算
        aiPercent.tokenData.assignable = chatModelAttr.sum - chatModelAttr.reserve
    } else {
        aiPercent.tokenData = {
            sum: 0,
            reserve: 0,
            assignable: 0,
        }
    }

    // 模块有预留tokens则响应tokens占比最小值可为0
    aiPercent.percent.ai_response_percent.min = aiPercent.tokenData.reserve > 0 ? 0 : 5
    aiPercent.percent.ai_response_percent.value =
        aiPercent.percent.ai_response_percent.value >= aiPercent.percent.ai_response_percent.min
            ? aiPercent.percent.ai_response_percent.value
            : aiPercent.percent.ai_response_percent.min

    percentCheck()
}

/**
 * 设置 token 占比
 * @param tokenConfig token占比配置
 * @param promptTokenCount 提示词所占的token数
 */
export const setTokenConfig = (tokenConfig: anyObj, promptTokenCount: number) => {
    // 赋值tokens占比
    for (const key in aiPercent.percent) {
        aiPercent.percent[key].value = tokenConfig[key]
        if (key == 'ai_prompt_percent') {
            let promptPercent = (promptTokenCount / aiPercent.tokenData.sum) * 100

            // 额外加10%，保证提示词占比足够
            promptPercent = Math.ceil(promptPercent + promptPercent * 0.1)
            aiPercent.percent[key].min = promptPercent
            aiPercent.percent[key].value = promptPercent
        } else if (key == 'ai_response_percent') {
            aiPercent.percent[key].min = aiPercent.tokenData.reserve > 0 ? 0 : 5
            aiPercent.percent[key].value =
                aiPercent.percent[key].value >= aiPercent.percent[key].min ? aiPercent.percent[key].value : aiPercent.percent[key].min
        } else if (key == 'ai_short_memory_percent' && sessionState.form.ai_short_memory == 0) {
            aiPercent.percent.ai_short_memory_percent.min = 0
            aiPercent.percent.ai_short_memory_percent.value = 0
        }
    }
    percentCheck()
}

/**
 * 短期记忆对话轮数改变
 */
export const onAiShortMemoryChange = (shortMemory: number) => {
    if (shortMemory <= 0) {
        aiPercent.percent.ai_short_memory_percent.min = 0
        aiPercent.percent.ai_short_memory_percent.value = 0
    } else {
        aiPercent.percent.ai_short_memory_percent.min = 5
        aiPercent.percent.ai_short_memory_percent.value =
            aiPercent.percent.ai_short_memory_percent.value >= aiPercent.percent.ai_short_memory_percent.min
                ? aiPercent.percent.ai_short_memory_percent.value
                : aiPercent.percent.ai_short_memory_percent.min
    }
    percentCheck()
}

export const onCopy = (text: string) => {
    navigator.clipboard.writeText(text).then(() => {
        ElNotification({
            type: 'success',
            message: '复制成功~',
        })
    })
}

/**
 * 提示词变更
 */
export const onPromptChange = (systemPrompt: string, prompt: string) => {
    calcTokens(systemPrompt + prompt).then((res) => {
        let promptPercent = (res.data.count / aiPercent.tokenData.sum) * 100

        // 额外加10%，保证提示词占比足够
        promptPercent = Math.ceil(promptPercent + promptPercent * 0.1)
        aiPercent.percent.ai_prompt_percent.min = promptPercent
        aiPercent.percent.ai_prompt_percent.value = promptPercent
        percentCheck()
    })
}

/**
 * 设置水印
 */
export const setWatermark = () => {
    delWatermark()
    const canvas = document.createElement('canvas')
    canvas.width = 360
    canvas.height = 120
    const cans = canvas.getContext('2d')!
    cans.rotate((-15 * Math.PI) / 180)
    cans.font = '12px SimSun'
    cans.fillStyle = 'rgba(200, 200, 200, 0.30)'
    cans.textAlign = 'center'
    cans.fillText('AI生成内容仅供参考', 360 / 8, 120 / 2)
    const div = document.createElement('div')
    div.id = WATER_MARK_ID
    div.style.pointerEvents = 'none'
    div.style.top = '0px'
    div.style.left = '0px'
    div.style.position = 'fixed'
    div.style.zIndex = '999'
    div.style.width = document.documentElement.clientWidth + 'px'
    div.style.height = document.documentElement.clientHeight + 'px'
    div.style.background = 'url(' + canvas.toDataURL('image/png') + ') left top repeat'
    document.body.appendChild(div)
}

/**
 * 删除水印
 */
export const delWatermark = () => {
    document.getElementById(WATER_MARK_ID)?.remove()
}
