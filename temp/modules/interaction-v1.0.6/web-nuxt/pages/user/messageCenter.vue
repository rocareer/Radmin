<template>
    <div class="user-views">
        <el-card v-if="state.message.userInfo === null" class="user-views-card" shadow="hover">
            <template #header>
                <div class="card-header">
                    <span class="card-title">
                        <span>{{ t('user.messageCenter.Message Center') }}</span>
                        <span @click="onMarkRead('all')" v-if="state.existUnread" class="all-read">{{ t('user.messageCenter.All read') }}</span>
                    </span>
                    <div class="header-right">
                        <div class="nickname">
                            <el-input
                                @input="debounce(loadMessage, 500)()"
                                :placeholder="t('user.messageCenter.User nickname fuzzy search')"
                                v-model="state.messageList.nickname"
                            ></el-input>
                        </div>
                        <div class="keywords">
                            <el-input
                                @input="debounce(loadMessage, 500)()"
                                :placeholder="t('user.messageCenter.Message content fuzzy search')"
                                v-model="state.messageList.keywords"
                            ></el-input>
                        </div>
                    </div>
                </div>
            </template>
            <div class="message-list">
                <Loading v-if="state.messageList.loading" />
                <div @click.prevent="toggleMessageItem(message)" class="message-item" v-for="(message, idx) in state.messageList.list" :key="idx">
                    <div class="message-user-info">
                        <img class="avatar" :src="message.show_user.avatar" :alt="message.show_user.nickname" />
                        <div>
                            <div class="nickname">
                                <span class="nickname-hover" @click.stop="openUserCard(message.show_user.id)">{{ message.show_user.nickname }}</span>
                                <el-tag v-if="state.officialAccount.includes(message.show_user.id.toString())" class="nickname-tag" size="small">
                                    {{ t('user.messageCenter.Official account') }}
                                </el-tag>
                                <div v-if="message.status == 'unread'" @click.stop="onMarkRead(message.id)" class="message-operation">
                                    {{ t('user.messageCenter.Mark read') }}
                                </div>
                            </div>
                            <div class="create_time">{{ message.create_time }}</div>
                        </div>
                    </div>
                    <div v-html="message.content" class="message-content"></div>
                    <div v-if="message.status == 'unread'" class="red-dot">•</div>
                </div>
                <div v-if="state.messageList.total > 0" class="message-list-footer">
                    <el-pagination
                        :currentPage="state.messageList.currentPage"
                        :page-size="state.messageList.pageSize"
                        :page-sizes="[10, 20, 50, 100]"
                        background
                        :layout="memberCenter.state.shrink ? 'prev, next, jumper' : 'sizes, ->, prev, pager, next, jumper'"
                        :total="state.messageList.total"
                        @size-change="onMessageListSizeChange"
                        @current-change="onMessageListCurrentChange"
                    ></el-pagination>
                </div>
                <el-empty v-if="state.messageList.total <= 0 && !state.messageList.loading" />
            </div>
        </el-card>

        <div v-if="state.message.userInfo !== null" class="dialog-info">
            <div class="chat-header">
                <Icon @click="toggleMessageItem(null)" class="arrow-left" size="var(--el-font-size-large)" name="el-icon-ArrowLeftBold" />
                <span @click.stop="openUserCard(state.message.userInfo.id)" class="nickname nickname-hover">
                    {{ state.message.userInfo.nickname }}
                </span>
            </div>
            <div class="message-editor-box">
                <baInput
                    :attr="{
                        placeholder: t('user.messageCenter.Speak civilly, ask questions sincerely, please enter what you want to send'),
                        rows: 3,
                    }"
                    v-model="state.message.content"
                    type="textarea"
                    @keyup.ctrl.enter="sendMessage"
                />
                <div class="message-editor-footer">
                    <el-button v-if="!state.batchDeletion" :loading="state.message.sendLoading" @click="sendMessage" v-blur>
                        {{ t('user.messageCenter.Send') }}（Ctrl+Enter）
                    </el-button>
                    <div class="batch-deletion-box" v-else>
                        <el-checkbox
                            :label="t('user.messageCenter.Select all')"
                            @change="onDelCheckboxChange"
                            v-model="state.checkboxAll"
                            size="large"
                        />
                        <el-button
                            :loading="state.message.delLoading"
                            @click="onDelMessage('batch')"
                            plain
                            class="batch-deletion-opt-item"
                            type="danger"
                            size="small"
                        >
                            {{ t('user.messageCenter.Delete') }}
                        </el-button>
                        <div @click="state.batchDeletion = false" class="batch-deletion-opt-item color-info">{{ t('Cancel') }}</div>
                    </div>
                    <span v-if="!state.batchDeletion" class="batch-deletion" @click="state.batchDeletion = true">
                        {{ t('user.messageCenter.Batch delete message') }}
                    </span>
                </div>
            </div>
            <div class="message-list">
                <Loading v-if="state.message.loading" />
                <div class="message-item" v-for="(message, idx) in state.message.list" :key="idx">
                    <div class="message-user-info">
                        <el-checkbox v-if="state.batchDeletion" class="message-checkbox" v-model="message.checkbox" size="large" />
                        <img class="avatar" :src="message.user.avatar" :alt="message.user.nickname" />
                        <div>
                            <div class="nickname">
                                <span class="nickname-hover" @click.stop="openUserCard(message.user.id)">{{ message.user.nickname }}</span>
                                <el-tag v-if="state.officialAccount.includes(message.user.id.toString())" class="nickname-tag" size="small">
                                    {{ t('user.messageCenter.Official account') }}
                                </el-tag>
                                <el-popconfirm
                                    :confirm-button-text="t('Confirm')"
                                    :cancel-button-text="t('Cancel')"
                                    :title="t('user.messageCenter.Are you sure to delete it?')"
                                    @confirm="onDelMessage(message.id)"
                                >
                                    <template #reference>
                                        <div class="message-operation">
                                            {{ t('user.messageCenter.Delete message') }}
                                        </div>
                                    </template>
                                </el-popconfirm>
                            </div>
                            <div class="create_time">{{ message.create_time }}</div>
                        </div>
                    </div>
                    <div v-html="message.content" class="message-content"></div>
                </div>
                <div v-if="state.message.total > 0" class="message-list-footer">
                    <el-pagination
                        :currentPage="state.message.currentPage"
                        :page-size="state.message.pageSize"
                        :page-sizes="[10, 20, 50, 100]"
                        background
                        :layout="memberCenter.state.shrink ? 'prev, next, jumper' : 'sizes, ->, prev, pager, next, jumper'"
                        :total="state.message.total"
                        @size-change="onMessageInfoSizeChange"
                        @current-change="onMessageInfoCurrentChange"
                    ></el-pagination>
                </div>
                <el-empty v-if="state.message.total <= 0 && !state.message.loading" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { getMessageList, postMarkRead, loadDialog, postSendMessage, postDelMessage } from '~/api/interaction'
import { useRoute } from 'vue-router'

definePageMeta({
    layout: 'user',
    name: 'messageCenter',
})

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const memberCenter = useMemberCenter()

const state: {
    messageList: {
        list: any[]
        total: number
        currentPage: number
        pageSize: number
        loading: boolean
        keywords: string
        nickname: string
    }
    message: {
        userInfo: anyObj | null
        list: any[]
        total: number
        currentPage: number
        pageSize: number
        loading: boolean
        content: string
        sendLoading: boolean
        delLoading: boolean
    }
    officialAccount: string[]
    existUnread: boolean
    batchDeletion: boolean
    checkboxAll: boolean
} = reactive({
    messageList: {
        list: [],
        total: 0,
        currentPage: 1,
        pageSize: 10,
        loading: false,
        keywords: '',
        nickname: '',
    },
    message: {
        userInfo: null,
        list: [],
        total: 0,
        currentPage: 1,
        pageSize: 10,
        loading: false,
        content: '',
        sendLoading: false,
        delLoading: false,
    },
    officialAccount: [],
    existUnread: false,
    batchDeletion: false,
    checkboxAll: false,
})

const openUserCard = (id: string) => {
    router.push({ name: 'user/card', params: { id } })
}

const onMessageListSizeChange = (val: number) => {
    state.messageList.pageSize = val
    loadMessage()
}

const onMessageListCurrentChange = (val: number) => {
    state.messageList.currentPage = val
    loadMessage()
}

const onMarkRead = (val: string) => {
    postMarkRead(val).then(() => {
        fetchMessage()
        let existUnread = false
        for (const key in state.messageList.list) {
            if (val == 'all' || state.messageList.list[key].id == val) {
                state.messageList.list[key].status = 'read'
            }
            if (state.messageList.list[key].status == 'unread') {
                existUnread = true
            }
        }
        state.existUnread = existUnread
    })
}

const onDelCheckboxChange = () => {
    for (const key in state.message.list) {
        state.message.list[key].checkbox = state.checkboxAll
    }
}
const onDelMessage = (val: string) => {
    state.message.delLoading = true
    const ids: string[] = []
    if (val == 'batch') {
        for (const key in state.message.list) {
            if (state.message.list[key].checkbox) {
                ids.push(state.message.list[key].id)
            }
        }
    } else {
        ids.push(val)
    }
    postDelMessage(ids).then(() => {
        state.message.delLoading = false
        state.checkboxAll = false
        loadDialogInfo()
    })
}

const loadMessage = () => {
    state.messageList.loading = true
    state.messageList.list = []
    getMessageList({
        page: state.messageList.currentPage,
        limit: state.messageList.pageSize,
        keywords: state.messageList.keywords,
        nickname: state.messageList.nickname,
    })
        .then((res) => {
            if (res.code == 1) {
                state.messageList.list = res.data.list
                state.messageList.total = res.data.total
                state.officialAccount = res.data.officialAccount
                state.existUnread = res.data.existUnread
            }
        })
        .finally(() => {
            state.messageList.loading = false
        })
}

const onMessageInfoCurrentChange = (val: number) => {
    state.message.currentPage = val
    loadDialogInfo()
}

const onMessageInfoSizeChange = (val: number) => {
    state.message.pageSize = val
    loadDialogInfo()
}

const loadDialogInfo = () => {
    state.message.loading = true
    state.message.list = []
    loadDialog({
        userId: state.message.userInfo?.id,
        page: state.message.currentPage,
        limit: state.message.pageSize,
    }).then((res) => {
        fetchMessage()
        state.message.loading = false
        state.message.list = res.data.list
        state.message.total = res.data.total
        state.message.userInfo = res.data.userInfo
    })
}

const toggleMessageItem = (item: anyObj | null) => {
    const baseHref = window.location.href.replace(window.location.search, '')
    if (item) {
        state.message.userInfo = item.show_user
        loadDialogInfo()
        window.history.pushState(window.history.state, '', baseHref + '?id=' + item.show_user.id)
    } else {
        state.message.userInfo = null
        window.history.pushState(window.history.state, '', baseHref)
        loadMessage()
    }
}

const sendMessage = () => {
    if (!state.message.content) return
    state.message.sendLoading = true
    postSendMessage({
        userId: state.message.userInfo?.id,
        content: state.message.content,
    }).then((res) => {
        state.message.sendLoading = false
        if (res.code == 1) {
            state.message.content = ''
            state.message.currentPage = 1
            loadDialogInfo()
        }
    })
}

onMounted(() => {
    if (!route.query.id) {
        loadMessage()
    } else {
        state.message.userInfo = {
            id: route.query.id as string,
            nickname: t('utils.Loading'),
        }
        loadDialogInfo()
    }
})
</script>

<style scoped lang="scss">
.user-views-card :deep(.el-card__body) {
    padding: 0;
}
.card-header {
    display: flex;
    align-items: center;
    .header-right {
        display: flex;
        align-items: center;
        margin-left: auto;
    }
    .nickname {
        width: 150px;
    }
    .keywords {
        width: 220px;
        margin-left: 10px;
    }
    .all-read {
        cursor: pointer;
        color: var(--el-color-primary);
        font-size: var(--el-font-size-extra-small);
        margin-left: 10px;
    }
}
.message-list {
    .message-item {
        position: relative;
        padding: 10px 20px;
        border-bottom: 1px solid var(--el-border-color-extra-light);
        cursor: pointer;
        .message-operation {
            display: none;
        }
        &:hover {
            background-color: var(--el-color-info-light-9);
            .message-operation {
                display: inline-block;
                padding: 0 10px;
                font-weight: normal;
                color: var(--el-color-primary);
                font-size: var(--el-font-size-extra-small);
                opacity: 0.8;
                &:hover {
                    opacity: 1;
                }
            }
        }
        .message-user-info {
            display: flex;
            .message-checkbox {
                margin-right: 8px;
            }
            .avatar {
                height: 36px;
                width: 36px;
                border-radius: 50%;
                margin-right: 8px;
            }
            .nickname {
                display: flex;
                align-items: center;
                font-weight: bold;
                font-size: var(--el-font-size-base);
                margin: 3px 0;
                .nickname-tag {
                    margin: 0 3px;
                    font-weight: normal;
                }
            }
            .create_time {
                color: var(--el-color-info);
                font-size: var(--el-font-size-extra-small);
            }
        }
        .message-content {
            margin: 5px 0 5px 44px;
            :deep(a) {
                color: var(--el-color-primary);
                text-decoration: none;
            }
            :deep(a:hover) {
                color: var(--el-color-primary);
                text-decoration: underline;
            }
        }
        .red-dot {
            position: absolute;
            top: 5px;
            right: 10px;
            color: var(--el-color-error);
            font-size: 32px;
            font-weight: bold;
        }
    }
    .message-item:last-child {
        border-bottom: none;
    }
    .message-list-footer {
        padding: 15px 20px;
    }
    :deep(.ba-loading) {
        margin: 20px auto;
    }
}
.dialog-info {
    background-color: #fff;
}
.chat-header {
    padding: 20px 17px;
    display: flex;
    align-items: center;
    font-size: var(--el-font-size-large);
    font-weight: bold;
    .arrow-left {
        cursor: pointer;
        &:hover {
            color: var(--el-color-primary) !important;
        }
    }
    .nickname {
        cursor: pointer;
        margin-left: 5px;
    }
}
.message-editor-box {
    padding: 0 20px 10px 20px;
    border-bottom: 1px solid var(--el-border-color-extra-light);
    .message-editor-footer {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        .batch-deletion {
            color: var(--el-color-primary);
            cursor: pointer;
        }
        .batch-deletion-box {
            display: flex;
            align-items: center;
            .batch-deletion-opt-item {
                margin-left: 8px;
                cursor: pointer;
            }
        }
    }
}
.color-info {
    color: var(--el-color-info);
}
.nickname-hover:hover {
    color: var(--el-color-primary);
}
@media screen and (max-width: 1200px) {
    .card-header {
        flex-wrap: wrap;
        .card-title {
            display: block;
            margin: 10px;
        }
        .nickname {
            width: auto;
        }
        .keywords {
            width: auto;
        }
    }
}
</style>
