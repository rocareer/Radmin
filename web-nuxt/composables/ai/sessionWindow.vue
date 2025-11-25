<template>
    <div class="session-box">
        <el-row class="session-row">
            <el-col class="session-list hidden-xs-only" :sm="6">
                <el-button
                    :loading="state.addSessionLoading"
                    @click="onAddSession"
                    class="session-plus"
                    round
                    type="primary"
                    size="large"
                    icon="el-icon-Plus"
                >
                    新建会话
                </el-button>
                <div class="session-list-scroll ba-scroll-style">
                    <div
                        v-for="(session, idx) in ai.state.sessionList"
                        :key="idx"
                        class="session-item"
                        @click="changeSession(idx)"
                        :class="session.id == ai.state.sessionInfo.activeSessionId ? 'active' : ''"
                    >
                        <div class="session-title-box" :class="'session-item-' + session.id">
                            <el-input
                                v-if="state.editTitle.show && state.editTitle.id == session.id"
                                size="small"
                                v-model="state.editTitle.value"
                                class="title-input"
                                :class="'title-input-' + idx"
                                @blur="onSaveSessionTitle(idx)"
                                @keyup.enter="onSaveSessionTitle(idx)"
                            ></el-input>
                            <div v-else class="title">{{ session.title }}</div>
                        </div>
                        <div class="session-footer">
                            <div class="create-time">{{ session.create_time }}</div>
                            <div class="footer-opt">
                                <Icon
                                    class="opt-icon"
                                    color="var(--el-text-color-placeholder)"
                                    :name="session.operateLoading ? 'el-icon-Loading' : 'el-icon-EditPen'"
                                    size="small"
                                    @click.stop="onEditSession(idx)"
                                />
                                <el-popconfirm @confirm="onDelSession(idx)" title="确定删除会话？">
                                    <template #reference>
                                        <Icon
                                            class="opt-icon"
                                            color="var(--el-text-color-placeholder)"
                                            :name="session.operateLoading ? 'el-icon-Loading' : 'el-icon-Delete'"
                                            size="small"
                                            @click.stop
                                        />
                                    </template>
                                </el-popconfirm>
                            </div>
                        </div>
                    </div>
                </div>
            </el-col>
            <el-col class="session-window" :xs="24" :sm="18">
                <div class="window-title">
                    <span>{{ ai.state.sessionInfo.title }}</span>
                    <span class="window-loading">
                        <Icon v-show="ai.state.messageStatus.loading" name="el-icon-Loading" class="is-loading" />
                    </span>
                </div>
                <TransitionGroup
                    @scroll="transitionGroupScroll"
                    id="im-message-scrollbar"
                    class="chat-records ba-scroll-style"
                    name="el-fade-in"
                    tag="div"
                >
                    <div class="placeholder" key="placeholder"></div>
                    <div class="chat-records-grow" key="chatRecordsGrow"></div>
                    <div
                        v-for="(item, idx) in ai.state.messages"
                        :key="item.id || item.uuid || item.content"
                        class="chat-record-item"
                        :class="item.type"
                    >
                        <span v-if="item.type == 'system'">{{ item.content }}</span>
                        <template v-else>
                            <div class="record-avatar-img">
                                <img draggable="false" :src="fullUrl(item.avatar!)" />
                            </div>
                            <div class="record-content-box">
                                <div class="chat-record-nickname">{{ item.nickname }}</div>
                                <div class="record-content">
                                    <template v-if="item.message_type == 'text'">
                                        <div class="text-content">
                                            <MdPreview
                                                v-if="item.reasoning_content"
                                                class="message-md-content deep-seek"
                                                :id="'content-deep-seek-' + idx"
                                                :editorId="'content-deep-seek-' + idx"
                                                :modelValue="item.reasoning_content"
                                            />
                                            <MdPreview
                                                class="message-md-content"
                                                :id="'content-' + idx"
                                                :editorId="'content-' + idx"
                                                :modelValue="item.content"
                                            />
                                            <Icon v-if="item.loading" class="is-loading message-loading" name="el-icon-Loading" size="16" />
                                        </div>
                                    </template>
                                    <el-image
                                        :hide-on-click-modal="true"
                                        v-else-if="item.message_type == 'image'"
                                        :preview-src-list="[item.content]"
                                        :src="item.content"
                                    ></el-image>
                                    <el-link target="_blank" type="success" v-else-if="item.message_type == 'link'" :href="item.content">
                                        {{ item.title ?? item.content }}
                                    </el-link>
                                </div>
                                <div class="content-tags">
                                    <el-tooltip effect="dark" content="复制" placement="bottom">
                                        <el-tag @click="onCopy(item.content)">
                                            <Icon name="fa fa-file-o" size="12" color="var(--el-color-primary)" />
                                        </el-tag>
                                    </el-tooltip>
                                    <el-tooltip
                                        effect="dark"
                                        :content="item.type == 'you' ? '输入输出消耗 tokens 计量' : '向量化消耗 tokens 计量'"
                                        placement="bottom"
                                        v-if="item.tokens"
                                    >
                                        <el-tag>{{ item.tokens }}</el-tag>
                                    </el-tooltip>
                                    <el-tooltip
                                        effect="dark"
                                        :content="'实际扣除 tokens'"
                                        placement="bottom"
                                        v-if="item.type == 'you' && item.consumption_tokens"
                                    >
                                        <el-tag>{{ item.consumption_tokens }}</el-tag>
                                    </el-tooltip>
                                    <template v-if="item.kbs?.length">
                                        <el-tooltip
                                            effect="dark"
                                            :content="'点击查阅引用知识点：' + kb"
                                            placement="bottom"
                                            v-for="(kb, kbi) in item.kbsTable?.title"
                                            :key="kbi"
                                        >
                                            <el-tag
                                                @click="router.push({ path: '/admin/ai/kbsContent', query: { id: item.kbs![kbi] } })"
                                                class="kbs-item"
                                            >
                                                {{ kb }}
                                            </el-tag>
                                        </el-tooltip>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </TransitionGroup>
                <div v-if="ai.state.aiOutputMessageKey != null" @click="onStopGenerate" class="stop-generate">停止生成</div>
                <div class="message-textarea-box">
                    <textarea
                        @input="onMessageInput"
                        class="ba-scroll-style"
                        id="message-text-input"
                        placeholder="请输入消息内容"
                        rows="3"
                        @keyup.alt.enter="onSubmit"
                        @keyup.ctrl.enter="onSubmit"
                        v-model="state.message"
                    ></textarea>
                    <div class="message-textarea-footer">
                        <span>按下Enter换行，Ctrl或Alt+Enter发送</span>
                        <el-button type="primary" @click="onSubmit" size="small">发送</el-button>
                    </div>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import {
    changeSession,
    linkAddTarget,
    pullMessageScrollBar,
    addMessage,
    transitionGroupScroll,
    onCopy,
    setWatermark,
    delWatermark,
} from '~/composables/ai/index'
import { debounce } from 'lodash-es'
import { sessionOperate, checkStop } from '~/api/ai'
import { fetchEventSource } from '@microsoft/fetch-event-source'
import { MdPreview } from 'md-editor-v3'
import 'md-editor-v3/lib/preview.css'

let fetchEventSourceCtrl = new AbortController()
const ai = useAi()
const route = useRoute()
const router = useRouter()
const userInfo = useUserInfo()
const state: {
    message: string
    recordsHeight: string
    editTitle: {
        id: number
        show: boolean
        value: string
    }
    addSessionLoading: boolean
} = reactive({
    message: '',
    recordsHeight: 'calc(100% - 150px)',
    editTitle: {
        id: 0,
        show: false,
        value: '',
    },
    addSessionLoading: false,
})

const onMessageInput = debounce((el: Event) => {
    const elem = el.target as HTMLInputElement
    elem.style.height = 'auto'
    const height = elem.scrollHeight > 200 ? 200 : elem.scrollHeight
    state.recordsHeight = 'calc(100% - ' + (height + 95) + 'px)'
    elem.style.height = height + 'px'
    elem.scrollTop = height

    pullMessageScrollBar()
}, 300)

const onSubmit = () => {
    // 发送消息到接口
    const uuid = shortUuid()
    const baUserToken = userInfo.getToken()
    fetchEventSourceCtrl = new AbortController()

    const percentSalue: anyObj = {}
    for (const key in ai.tokens.percent) {
        percentSalue[key] = ai.tokens.percent[key].value
    }

    fetchEventSource(import.meta.env.VITE_API_BASE_URL + '/api/ai/index', {
        method: 'POST',
        headers: {
            server: '1',
            'ba-user-token': baUserToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ...ai.state.form,
            ...percentSalue,
            message: state.message,
            id: ai.state.sessionInfo.activeSessionId,
            uuid: uuid,
        }),
        async onopen(response) {
            if (response.ok) {
                return
            } else if (response.status != 200) {
                throw new Error('HTTP SSE 链接失败！')
            }
        },
        onmessage(ev) {
            if (!ev.data) return
            const data = JSON.parse(ev.data)
            if (!data) return
            if (typeof data.code != 'undefined') {
                if (data.code == 0) {
                    // code为0 -> 错误消息
                    if (ai.state.aiOutputMessageKey !== null) {
                        ai.state.messages[ai.state.aiOutputMessageKey as number].content = data.msg
                    } else {
                        addMessage(
                            {
                                content: data.msg,
                                type: 'you',
                            },
                            'desc'
                        )
                    }
                    onStoppedGenerate()
                    return
                } else if (data.code == 1) {
                    // 新标题或消息详情
                    if (data.type == 'newTitle') {
                        ai.state.sessionList[ai.state.sessionInfo.activeSessionIdx].title = data.value
                    } else if (data.type == 'messageInfo') {
                        const messageIdx = getArrayKey(ai.state.messages, 'uuid', data.uuid + (data.value.sender_type == 'ai' ? 'you' : 'me'))
                        ai.state.messages[messageIdx].tokens = data.value.tokens
                        ai.state.messages[messageIdx].consumption_tokens = data.value.consumption_tokens
                        ai.state.messages[messageIdx].kbs = data.value.kbs
                        ai.state.messages[messageIdx].kbsTable = data.value.kbsTable

                        ai.state.sessionList[ai.state.sessionInfo.activeSessionIdx].create_time = data.value.last_message_time
                    }
                    return
                } else if (data.code == 2 && ai.state.aiOutputMessageKey !== null) {
                    // 来自ChatGPT AI的回复
                    ai.state.messages[ai.state.aiOutputMessageKey as number].content = data.content
                    ai.state.messages[ai.state.aiOutputMessageKey as number].reasoning_content = data.reasoning_content
                    if (data.finish_reason == 'stop') {
                        // 生成完毕
                        onStoppedGenerate()
                        linkAddTarget()
                    }
                    pullMessageScrollBar()
                    return
                }
            }

            // 来自千问AI的回复
            if (data.request_id && ai.state.aiOutputMessageKey !== null) {
                ai.state.messages[ai.state.aiOutputMessageKey as number].content = data.message || data.output.choices[0].message.content
                if (data?.output?.choices[0]?.finish_reason != 'null') {
                    // 生成完毕
                    onStoppedGenerate()
                    linkAddTarget()
                }
                pullMessageScrollBar()
            }
        },
        onerror(err) {
            throw err
        },
        openWhenHidden: true,
        signal: fetchEventSourceCtrl.signal,
    })

    ai.state.messages.unshift({
        type: 'me',
        content: state.message,
        message_type: 'text',
        nickname: userInfo.nickname,
        avatar: userInfo.avatar,
        uuid: uuid + 'me',
    })
    state.message = ''
    pullMessageScrollBar()

    // 直接生成好AI的消息框，显示为加载状态
    addMessage(
        {
            content: '',
            type: 'you',
            loading: true,
            uuid: uuid + 'you',
        },
        'desc'
    )

    ai.state.unexpectedRecords += 2
    ai.state.aiOutputMessageKey = getArrayKey(ai.state.messages, 'loading', true)
}

const onStoppedGenerate = () => {
    if (ai.state.aiOutputMessageKey === null) return
    ai.state.messages[ai.state.aiOutputMessageKey as number].loading = false
    ai.state.aiOutputMessageKey = null
    fetchEventSourceCtrl.abort()
}

const onStopGenerate = () => {
    onStoppedGenerate()

    const percentSalue: anyObj = {}
    for (const key in ai.tokens.percent) {
        percentSalue[key] = ai.tokens.percent[key].value
    }

    checkStop({
        ...ai.state.form,
        ...percentSalue,
        message: state.message,
        id: ai.state.sessionInfo.activeSessionId,
    })
}

const onAddSession = () => {
    state.addSessionLoading = true
    sessionOperate({
        type: 'add',
    })
        .then((res) => {
            ai.state.sessionList.unshift(res.data.sessionInfo)
            changeSession(0)
        })
        .finally(() => {
            state.addSessionLoading = false
        })
}

const onEditSession = (idx: number) => {
    state.editTitle.id = ai.state.sessionList[idx].id
    state.editTitle.show = true
    state.editTitle.value = ai.state.sessionList[idx].title
    nextTick(() => {
        const el = document.querySelector('.title-input-' + idx + ' input') as HTMLInputElement
        el.focus()
    })
}

const onSaveSessionTitle = (idx: number) => {
    if (ai.state.sessionList[idx].operateLoading) return
    ai.state.sessionList[idx].operateLoading = true
    sessionOperate({
        id: ai.state.sessionList[idx].id,
        type: 'changeTitle',
        newTitle: state.editTitle.value,
    })
        .then(() => {
            state.editTitle.id = 0
            state.editTitle.show = false
            ai.state.sessionList[idx].title = state.editTitle.value
        })
        .finally(() => {
            ai.state.sessionList[idx].operateLoading = false
        })
}

const onDelSession = (idx: number) => {
    ai.state.sessionList[idx].operateLoading = true
    sessionOperate({
        id: ai.state.sessionList[idx].id,
        type: 'del',
    })
        .then((res) => {
            if (ai.state.sessionList[idx].id == ai.state.sessionInfo.activeSessionId) {
                ai.state.sessionList = res.data.sessionList
                changeSession(0)
            } else {
                ai.state.sessionList = res.data.sessionList
            }
        })
        .catch(() => {
            ai.state.sessionList[idx].operateLoading = false
        })
}

onMounted(() => {
    pullMessageScrollBar()
    setWatermark()

    watch(
        () => ai.state.ready,
        (newVal) => {
            // 接受q，并自动向AI发送
            if (route.query.q && newVal) {
                state.message = route.query.q as string
                onSubmit()
            }
        }
    )
})

onUnmounted(() => {
    delWatermark()
})
</script>

<style scoped lang="scss">
.message-md-content {
    background: transparent;
    font-size: 15px;
    :deep(.default-theme) {
        p {
            padding: 0;
        }
        pre {
            white-space: pre-wrap;
        }
    }
    :deep(.md-editor-preview) {
        font-size: 15px;
    }
    :deep(.md-editor-preview-wrapper) {
        padding: 0;
        overflow: hidden;
    }
    :deep(.default-theme ul li) {
        margin: 0;
    }
    :deep(a) {
        color: var(--el-color-primary);
        text-decoration: none;
        &:hover {
            text-decoration: underline;
        }
    }
}
.message-md-content.deep-seek {
    border-left: 2px solid var(--el-border-color-light);
    padding-left: 6px;
    margin-top: 6px;
    :deep(.default-theme) {
        --md-theme-color: var(--el-text-color-secondary);
    }
}
.me {
    .message-md-content {
        :deep(.default-theme) {
            --md-theme-color: var(--el-color-white);
            --md-theme-code-inline-color: var(--el-color-black);
        }
    }
}
.session-box {
    height: 100%;
    .session-row {
        height: 100%;
        .session-list {
            display: block;
            height: 100%;
            box-sizing: border-box;
            background-color: #fff;
            .session-plus {
                width: calc(100% - 20px);
                margin: 10px;
            }
            .session-list-scroll {
                display: block;
                height: calc(100% - 60px);
                overflow-y: auto;
                overflow-x: hidden;
            }
            .session-item {
                padding: 10px 15px;
                cursor: pointer;
                .session-title-box {
                    font-size: var(--el-font-size-medium);
                    .title {
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }
                }
                .session-footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 5px;
                    .footer-opt {
                        display: flex;
                        align-items: center;
                        .opt-icon {
                            margin-left: 6px;
                            &:hover {
                                color: var(--el-text-color-primary) !important;
                            }
                        }
                    }
                    .create-time {
                        font-size: 12px;
                        color: var(--el-text-color-placeholder);
                    }
                }
                &:hover {
                    background-color: var(--el-fill-color-light);
                }
            }
            .session-item.active {
                background-color: var(--el-fill-color);
            }
        }
        .session-window {
            position: relative;
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 4px;
            padding: 0 10px;
            background-color: #fbfbfb;
            .window-title {
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: var(--el-font-size-medium);
                font-weight: bold;
                color: var(--el-text-color-primary);
                height: 50px;
                line-height: 50px;
                text-align: center;
                border-bottom: 1px solid var(--el-border-color-extra-light);
                .window-loading {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-left: 10px;
                    min-width: 18px;
                }
            }
            .stop-generate {
                position: absolute;
                bottom: 100px;
                right: 15px;
                color: var(--el-color-info);
                cursor: pointer;
            }
            .chat-records {
                display: flex;
                flex-direction: column-reverse;
                position: absolute;
                width: calc(100% - 10px);
                height: v-bind('state.recordsHeight');
                padding: 0;
                overflow-y: auto;
                overflow-x: hidden;
                box-sizing: border-box;
                .chat-records-grow {
                    flex-grow: 1;
                    flex-shrink: 1;
                }
                .chat-record-item {
                    display: flex;
                    padding-top: 16px;
                    align-items: flex-start;
                }
                .system span {
                    font-size: 12px;
                    display: inline-block;
                    background: var(--el-color-info-light-9);
                    color: var(--el-text-color-primary);
                    line-height: 12px;
                    border-radius: 5px;
                    padding: 3px 10px;
                    text-align: center;
                    word-wrap: break-word;
                    word-break: break-all;
                    margin: 0 auto;
                    margin-bottom: 6px;
                }
                .record-avatar-img {
                    position: relative;
                    display: inline-block;
                    background: transparent;
                    width: 36px;
                    height: 36px;
                    cursor: pointer;
                    user-select: none;
                    img {
                        height: 100%;
                        width: 100%;
                        border-radius: 6px;
                    }
                }
                .record-content-box {
                    position: relative;
                    max-width: 75%;
                    margin: 0 13px;
                }
                .me {
                    flex-direction: row-reverse;
                    display: flex;
                    justify-content: flex-start;
                    padding-right: 4px;
                    margin-inline-end: 0;
                }
                .chat-record-nickname {
                    color: var(--el-text-color-secondary);
                    padding-bottom: 3px;
                }
                .you .chat-record-nickname {
                    text-align: left;
                }
                .you .record-content {
                    color: #000;
                    background: var(--el-color-info-light-9);
                    .el-link {
                        --el-link-text-color: var(--el-color-success);
                    }
                }
                .me .chat-record-nickname {
                    text-align: right;
                }
                .me .record-content {
                    margin-left: auto;
                    color: var(--el-color-white);
                    background: var(--el-color-primary);
                    .el-link {
                        --el-link-text-color: var(--el-color-warning);
                        &:hover {
                            --el-link-hover-text-color: var(--el-color-warning);
                        }
                    }
                }
                .you .record-content-box:before {
                    left: -4px;
                    background: var(--el-color-info-light-9);
                }
                .me .record-content-box:before {
                    right: -4px;
                    background: var(--el-color-primary);
                }
                .record-content-box:before {
                    position: absolute;
                    top: 26px;
                    display: block;
                    width: 8px;
                    height: 6px;
                    content: '\00a0';
                    -webkit-transform: rotate(29deg) skew(-35deg);
                    transform: rotate(29deg) skew(-35deg);
                }
                .record-content {
                    font-size: 14px;
                    color: var(--el-text-color-primary);
                    padding: 2px 10px;
                    border-radius: 5px;
                    position: relative;
                    width: fit-content;
                    max-width: 100%;
                    word-wrap: break-word;
                    word-break: break-all;
                    .record-img,
                    .el-image {
                        vertical-align: bottom;
                        max-height: 200px;
                        width: auto;
                        height: auto;
                        cursor: pointer;
                        user-select: none;
                        border: 1px solid transparent;
                    }
                    .message-loading {
                        margin: 6px 0;
                    }
                }
                .content-tags {
                    margin-top: 4px;
                    .el-tag {
                        margin-right: 4px;
                        cursor: pointer;
                    }
                    .kbs-item.el-tag {
                        display: inline-flex;
                        max-width: 100px;
                        :deep(.el-tag__content) {
                            width: 100%;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            white-space: nowrap;
                        }
                    }
                }
                .placeholder {
                    height: 20px;
                    width: 100%;
                }
            }
            .message-textarea-box {
                position: absolute;
                bottom: 0;
                width: calc(100% - 10px);
                border-top: 1px solid var(--el-color-info-light-9);
                margin-right: -10px;
                textarea {
                    width: 100%;
                    border: none;
                    background-color: transparent;
                    padding: 5px 0;
                    line-height: 15px;
                }
                .message-textarea-footer {
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    font-size: var(--el-font-size-small);
                    color: var(--el-text-color-placeholder);
                    margin-top: 8px;
                    margin-right: 10px;
                    span {
                        padding: 0 8px;
                    }
                }
            }
        }
    }
}
</style>
