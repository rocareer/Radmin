import { nextTick } from 'vue'
import { debounce } from 'lodash-es'
import { calcTokens, records, chatInit } from '~/api/ai'
import { ElNotification } from 'element-plus'
import { AiChatModelTokenAttr, AiMessageItem, WATER_MARK_ID } from '~/stores/interface/ai'

// --------------------------
// --- AI窗口
// --- 1. 用户配置是保存于缓存的
// --------------------------

/**
 * 聊天记录 - 设置是否还有下一页
 */
export const setNextPage = (value: boolean) => {
    const ai = useAi()
    if (!value && ai.state.messageStatus.page > 1) {
        addMessage({
            type: 'system',
            content: '没有更多消息了~',
        })
    }
    ai.state.messageStatus.nextPage = value
}

/**
 * 聊天记录 - 滚动条监听翻页
 */
export const transitionGroupScroll = debounce((e: Event) => {
    const ai = useAi()
    const target = e.target as HTMLDivElement
    const scrollTop = Math.abs(target.scrollTop)
    const scrollHeight = target.scrollHeight
    const clientHeight = target.clientHeight

    if (scrollTop + clientHeight + 10 >= scrollHeight && ai.state.messageStatus.nextPage) {
        ai.state.messageStatus.loading = true
        ai.state.messageStatus.page++
        records(ai.state.sessionInfo.activeSessionId, ai.state.messageStatus.page, ai.state.unexpectedRecords)
            .then((res) => {
                for (const key in res.data.messages) {
                    for (const msgKey in res.data.messages[key].data) {
                        addMessage({
                            id: res.data.messages[key].data[msgKey].id,
                            type: res.data.messages[key].data[msgKey].sender_type == 'ai' ? 'you' : 'me',
                            content: res.data.messages[key].data[msgKey].content,
                            reasoning_content: res.data.messages[key].data[msgKey].reasoning_content,
                            tokens: res.data.messages[key].data[msgKey].tokens,
                            consumption_tokens: res.data.messages[key].data[msgKey].consumption_tokens,
                            nickname: res.data.messages[key].data[msgKey].sender_id,
                        })
                    }
                }

                setNextPage(res.data.nextPage)
            })
            .finally(() => {
                ai.state.messageStatus.loading = false
            })
    }
}, 300)

/**
 * 改变当前会话
 */
export const changeSession = (idx: number, callback = () => {}) => {
    const ai = useAi()
    ai.state.sessionInfo.activeSessionIdx = idx
    ai.state.sessionInfo.activeSessionId = ai.state.sessionList[idx].id
    ai.state.messages = []
    ai.state.messageStatus.loading = true
    ai.state.messageStatus.page = 1
    ai.state.unexpectedRecords = 0
    records(ai.state.sessionInfo.activeSessionId)
        .then((res) => {
            if (res.code == 1) {
                // 发送欢迎消息
                addMessage({
                    content: ai.state.usableModel[ai.state.form.ai_model].greeting || ai.state.form.ai_greeting,
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
                            consumption_tokens: res.data.messages[key].data[msgKey].consumption_tokens,
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

                setNextPage(res.data.nextPage)
                pullMessageScrollBar()
                typeof callback == 'function' && callback()

                if (import.meta.client) {
                    // 标记AI会话已经准备好，此标记未来可能会改变位置，它总是最终准备完成的标记
                    ai.state.ready = true

                    // 聚焦
                    document.getElementById('message-text-input')?.focus()
                }
            }
        })
        .finally(() => {
            ai.state.messageStatus.loading = false
        })
}

/**
 * 重置配置
 */
export const onResetConfig = (callback: () => void) => {
    const ai = useAi()
    ai.state.form.init = false
    ai.tokens.init = false
    ai.resetState()
    ai.state.loading = false

    ElNotification({
        type: 'success',
        message: '重置成功，正在自动刷新~',
    })

    setTimeout(() => {
        callback()
    }, 2500)
}

/**
 * 修改模型
 */
export const onChangeModel = () => {
    const ai = useAi()
    setChatModelAttr(ai.state.chatModelAttr[ai.state.form.ai_model])
    // AI属性与默认值取值
    const title = ai.state.usableModel[ai.state.form.ai_model].title || ai.state.form.ai_session_title
    const avatar = ai.state.usableModel[ai.state.form.ai_model].logo || ai.state.form.ai_session_logo
    const greeting = ai.state.usableModel[ai.state.form.ai_model].greeting || ai.state.form.ai_greeting

    // 会话窗口属性
    ai.state.sessionInfo.title = title
    ai.state.sessionInfo.aiInfo = {
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
export const onPrompt = () => {
    const ai = useAi()
    onPromptChange(ai.state.form.ai_system_prompt, ai.state.form.ai_prompt)
}

// --------------------------
// --- 会话窗口
// --------------------------

/**
 * 初始化
 */
export const getInitData = () => {
    const ai = useAi()
    ai.state.loading = false
    chatInit().then((res) => {
        if (res.code == 1) {
            ai.state.loading = true
            ai.state.usableModel = res.data.usableModel
            for (const key in ai.state.usableModel) {
                ai.state.usableModelContent[key] = ai.state.usableModel[key].optimize_name
            }

            // AI会员信息
            ai.state.aiUserInfo = res.data.aiUserInfo

            // 模型属性-模型预设属性
            ai.state.chatModelAttr = res.data.chatModelAttr

            // 配置表单初始化
            if (!ai.state.form.init) {
                ai.state.form = { ...ai.state.form, ...res.data.data }
                ai.state.form.init = true
            }

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

            ai.state.form = { ...res.data.data, ...ai.state.form, ...serverPriority }

            // 如果缓存中的模型不可用，恢复为默认配置
            if (!ai.state.usableModel[ai.state.form.ai_model]) {
                ai.state.form.ai_model = res.data.data['ai_model']
            }

            // 初始化token占比和模型token数据
            setChatModelAttr(ai.state.chatModelAttr[ai.state.form.ai_model])
            if (!ai.tokens.init) {
                setTokenConfig(res.data.data, res.data.promptTokenCount)
                ai.tokens.init = true
            }
            const percentSalue: anyObj = {}
            for (const key in ai.tokens.percent) {
                percentSalue[key] = ai.tokens.percent[key].value
            }
            setTokenConfig({ ...res.data.data, ...percentSalue }, res.data.promptTokenCount)

            // AI属性与默认值取值
            const title = ai.state.usableModel[ai.state.form.ai_model].title || ai.state.form.ai_session_title
            const avatar = ai.state.usableModel[ai.state.form.ai_model].logo || ai.state.form.ai_session_logo

            // 会话列表
            ai.state.sessionList = res.data.sessionList
            if (import.meta.client && ai.state.sessionList.length) {
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
            ai.state.sessionInfo.title = title
            ai.state.sessionInfo.aiInfo = {
                nickname: title,
                avatar: fullUrl(avatar),
            }
        }
    })
}

/**
 * 窗口滚动条到底
 */
export const pullMessageScrollBar = () => {
    if (import.meta.server) return
    nextTick(() => {
        const messageEl = document.getElementById('im-message-scrollbar') as HTMLDivElement
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
 */
export const addMessage = (item: AiMessageItem, order: 'desc' | 'asc' = 'asc') => {
    const ai = useAi()
    // you、me是对话窗口消息上的标志，you是AI
    if (item.type == 'you') {
        item.avatar = ai.state.sessionInfo.aiInfo.avatar
        item.nickname = item.nickname ? item.nickname : ai.state.sessionInfo.aiInfo.nickname
    } else if (item.type == 'me') {
        const userInfo = useUserInfo()
        item.avatar = userInfo.avatar
        item.nickname = item.nickname ? item.nickname : userInfo.nickname
    }
    if (!item.message_type) item.message_type = 'text'
    if (order == 'asc') {
        ai.state.messages.push(item)
    } else {
        ai.state.messages.unshift(item)
    }
    linkAddTarget()
}

// --------------------------
// --- token占比
// --------------------------

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
    const ai = useAi()
    let assigned = 0
    for (const key in ai.tokens.percent) {
        assigned += ai.tokens.percent[key].value
    }
    allocation(ai.tokens.percent[keyName].change, 100 - assigned)
}

/**
 * 递归修改token占比
 * @param keyName
 * @param value
 */
export const allocation = (keyName: string, value: number) => {
    const ai = useAi()
    const newValue = ai.tokens.percent[keyName].value + value
    if (newValue >= ai.tokens.percent[keyName].min) {
        ai.tokens.percent[keyName].value = newValue
    } else {
        ai.tokens.percent[keyName].value = ai.tokens.percent[keyName].min
        allocation(ai.tokens.percent[keyName].change, newValue - ai.tokens.percent[keyName].min)
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
    const ai = useAi()
    return parseInt(((percent / 100) * ai.tokens.tokenData.assignable! + extra).toString())
}

/**
 * 设置模型token属性
 * @param chatModelAttr token总数、预览情况等
 */
export const setChatModelAttr = (chatModelAttr: AiChatModelTokenAttr) => {
    const ai = useAi()
    if (chatModelAttr) {
        ai.tokens.tokenData = chatModelAttr

        // 可分配的token数计算
        ai.tokens.tokenData.assignable = chatModelAttr.sum - chatModelAttr.reserve
    } else {
        ai.tokens.tokenData = {
            sum: 0,
            reserve: 0,
            assignable: 0,
        }
    }

    // 模块有预留tokens则响应tokens占比最小值可为0
    ai.tokens.percent.ai_response_percent.min = ai.tokens.tokenData.reserve > 0 ? 0 : 5
    ai.tokens.percent.ai_response_percent.value =
        ai.tokens.percent.ai_response_percent.value >= ai.tokens.percent.ai_response_percent.min
            ? ai.tokens.percent.ai_response_percent.value
            : ai.tokens.percent.ai_response_percent.min

    percentCheck()
}

/**
 * 设置 token 占比
 * @param tokenConfig token占比配置
 * @param promptTokenCount 提示词所占的token数
 */
export const setTokenConfig = (tokenConfig: anyObj, promptTokenCount: number) => {
    const ai = useAi()
    // 赋值tokens占比
    for (const key in ai.tokens.percent) {
        ai.tokens.percent[key].value = tokenConfig[key]
        if (key == 'ai_prompt_percent') {
            let promptPercent = (promptTokenCount / ai.tokens.tokenData.sum) * 100

            // 额外加10%，保证提示词占比足够
            promptPercent = Math.ceil(promptPercent + promptPercent * 0.1)
            ai.tokens.percent[key].min = promptPercent
            ai.tokens.percent[key].value = promptPercent
        } else if (key == 'ai_response_percent') {
            ai.tokens.percent[key].min = ai.tokens.tokenData.reserve > 0 ? 0 : 5
            ai.tokens.percent[key].value =
                ai.tokens.percent[key].value >= ai.tokens.percent[key].min ? ai.tokens.percent[key].value : ai.tokens.percent[key].min
        } else if (key == 'ai_short_memory_percent' && ai.state.form.ai_short_memory == 0) {
            ai.tokens.percent.ai_short_memory_percent.min = 0
            ai.tokens.percent.ai_short_memory_percent.value = 0
        }
    }
    percentCheck()
}

/**
 * 短期记忆对话轮数改变
 */
export const onAiShortMemoryChange = (shortMemory: number) => {
    const ai = useAi()
    if (shortMemory <= 0) {
        ai.tokens.percent.ai_short_memory_percent.min = 0
        ai.tokens.percent.ai_short_memory_percent.value = 0
    } else {
        ai.tokens.percent.ai_short_memory_percent.min = 5
        ai.tokens.percent.ai_short_memory_percent.value =
            ai.tokens.percent.ai_short_memory_percent.value >= ai.tokens.percent.ai_short_memory_percent.min
                ? ai.tokens.percent.ai_short_memory_percent.value
                : ai.tokens.percent.ai_short_memory_percent.min
    }
    percentCheck()
}

/**
 * 提示词变更
 */
export const onPromptChange = (systemPrompt: string, prompt: string) => {
    const ai = useAi()
    calcTokens(systemPrompt + prompt).then((res) => {
        let promptPercent = (res.data.count / ai.tokens.tokenData.sum) * 100

        // 额外加10%，保证提示词占比足够
        promptPercent = Math.ceil(promptPercent + promptPercent * 0.1)
        ai.tokens.percent.ai_prompt_percent.min = promptPercent
        ai.tokens.percent.ai_prompt_percent.value = promptPercent
        percentCheck()
    })
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
