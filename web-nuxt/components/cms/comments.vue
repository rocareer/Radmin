<template>
    <div>
        <el-card class="comment-box-card base-card" shadow="never">
            <template #header>
                <div>评论（{{ props.commentCount }}）</div>
            </template>
            <div class="comment-list">
                <div v-if="state.comments.data.length">
                    <div class="comment-item" v-for="(item, idx) in state.comments.data" :key="idx">
                        <div class="comment-avatar">
                            <img :src="fullUrl(item.user.avatar)" :alt="item.user.nickname" />
                        </div>
                        <div class="comment-content">
                            <div class="comment-content-title">
                                <div class="user-name">{{ item.user.nickname }}</div>
                                <div class="comment-time">{{ item.create_time }}</div>
                                <div @click="onAt(item.user.id.toString(), item.user.nickname)" class="reply">回复TA</div>
                            </div>
                            <div
                                :class="cmsConfig.content_language == 'html' ? 'ba-wang-editor' : 'ba-markdown'"
                                class="comment-content-text"
                                v-html="item.content"
                            ></div>
                        </div>
                    </div>
                    <div class="comment-pagination">
                        <el-pagination
                            :currentPage="state.comments.current_page"
                            layout="total, ->, prev, pager, next, jumper"
                            :page-size="15"
                            background
                            :total="state.comments.total"
                            @current-change="onCurrentChange"
                        ></el-pagination>
                    </div>
                </div>
                <div v-else class="no-comment">{{ disable ? '暂无评论' : '暂无评论，期待您的发言...' }}</div>
                <div class="comment-publish" v-if="!disable" id="comment">
                    <div class="comment-publish-title">发表评论</div>
                    <div class="comment-publish-content">
                        <el-input
                            ref="commentRef"
                            :disabled="userInfo.isLogin() ? false : true"
                            v-model="state.comment"
                            @keyup.ctrl.enter="onPublishComment"
                            type="textarea"
                            :rows="5"
                            :placeholder="userInfo.isLogin() ? '请输入内容' : '请先登录后再发布评论'"
                            @input="onInput"
                        />
                        <div class="comment-publish-btn">
                            <el-button v-if="userInfo.isLogin()" :loading="state.pushLoading" @click="onPublishComment" type="primary">
                                发表评论
                            </el-button>
                            <el-button v-else @click="onPublishComment" type="warning">立即登录</el-button>
                        </div>
                    </div>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { publishComment, loadComments } from '~/api/cms/content'
import { InputInstance, ElNotification } from 'element-plus'
import { endsWith } from 'lodash-es'

interface Props {
    id: string
    type?: string
    comments: anyObj
    commentCount: number
    disable?: boolean
    // 评论后回调
    callback?: (res: anyObj) => void
}
const props = withDefaults(defineProps<Props>(), {
    id: '0',
    type: 'article',
    comments: () => {
        return {}
    },
    commentCount: 0,
    disable: false,
    callback: () => {},
})

const commentRef = ref<InputInstance>()
const userInfo = useUserInfo()
const router = useRouter()
const cmsConfig = useCmsConfig()
const state: {
    comment: string
    comments: anyObj
    pushLoading: boolean
    atUsers: Map<
        string,
        {
            id: string
            nickname: string
        }
    >
} = reactive({
    comment: '',
    comments: props.comments,
    pushLoading: false,
    atUsers: new Map(),
})

const onCurrentChange = (val: number) => {
    loadComments(props.id, val).then((res) => {
        state.comments = res.data
    })
}

const onPublishComment = () => {
    if (!userInfo.isLogin()) {
        router.push({ name: 'userLogin' })
        return
    }
    if (!state.comment) {
        ElNotification({
            type: 'error',
            message: '评论内容不能为空！',
        })
        return
    }
    state.pushLoading = true
    const atUsers: string[] = []
    for (const [key, value] of state.atUsers) {
        if (state.comment.search('@' + value.nickname + ' ') !== -1) {
            atUsers.push(key)
        }
    }

    publishComment(props.id, state.comment, atUsers).then((res) => {
        state.pushLoading = false
        if (res.code == 1) {
            state.comment = ''
            state.atUsers.clear()
            if (res.data.commentsReview == 'no') {
                setTimeout(() => {
                    router.go(0)
                }, 2500)
            }
            typeof props.callback == 'function' && props.callback(res.data)
        }
    })
}

const onAt = (id: string, nickname: string) => {
    state.atUsers.set(id, {
        id: id,
        nickname: nickname,
    })
    commentRef.value!.focus()
    insertStr(commentRef.value!.textarea!.selectionStart, '@' + nickname + ' ', true)
}

const onSelectAtUser = () => {
    if (commentRef.value?.textarea?.selectionStart) {
        const text = state.comment.slice(0, commentRef.value.textarea.selectionStart)
        for (const value of state.atUsers.values()) {
            const atTitle = '@' + value.nickname + ' '
            if (endsWith(text, atTitle)) {
                commentRef.value.textarea.setSelectionRange(text.length - atTitle.length, text.length)
                break
            }
            if (endsWith(text + ' ', atTitle)) {
                insertStr(text.length, ' ')
                setTimeout(() => {
                    onSelectAtUser()
                }, 100)
                break
            }
        }
    }
}

const onInput = () => {
    onSelectAtUser()
}

/**
 * 向评论框输入内容
 * @param start 开始位置
 * @param newStr 字符串
 * @param cursor 是否需要保留光标位置
 */
const insertStr = (start: number, newStr: string, cursor = false) => {
    state.comment = state.comment.slice(0, start) + newStr + state.comment.slice(start)
    if (cursor) {
        nextTick(() => {
            commentRef.value!.textarea!.setSelectionRange(start + newStr.length, start + newStr.length)
        })
    }
}
</script>

<style scoped lang="scss">
.base-card {
    border: none;
    border-radius: 0;
    margin-bottom: 10px;
    :deep(.el-card__header) {
        padding: 15px;
        border: none;
    }
    :deep(.el-card__body) {
        padding: 0 15px 5px 15px;
    }
}
.comment-box-card {
    :deep(.el-card__header) {
        border: none;
    }
    .comment-list {
        .comment-item {
            display: flex;
            padding: 10px 0;
            border-top: 1px solid var(--el-border-color-extra-light);
            .comment-avatar {
                width: 50px;
                height: 50px;
                img {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                }
            }
            .comment-content {
                margin-left: 10px;
                width: 100%;
                .comment-content-title {
                    display: flex;
                    align-items: center;
                    width: 100%;
                    .user-name {
                        font-weight: bold;
                    }
                    .comment-time {
                        padding-left: 10px;
                        color: var(--el-color-info);
                    }
                    .reply {
                        opacity: 0;
                        font-size: var(--el-font-size-extra-small);
                        padding-left: 10px;
                        color: var(--el-color-info);
                        cursor: pointer;
                        transition: all 0.2s ease-in-out;
                    }
                    .reply:hover {
                        color: var(--el-color-primary);
                    }
                }
                .comment-content-text {
                    margin-top: 5px;
                    font-size: 1em;
                    line-height: 1.6em;
                    :deep(img) {
                        max-width: 100%;
                    }
                }
            }
            .comment-content:hover {
                .reply {
                    opacity: 1;
                }
            }
        }
        .comment-pagination {
            display: flex;
            justify-content: center;
            width: 100%;
            border-top: 1px solid var(--el-border-color-extra-light);
            padding: 15px 0;
        }
        .no-comment {
            color: var(--el-color-info);
            padding-bottom: 10px;
        }
        .comment-publish {
            .comment-publish-title {
                font-size: 1em;
                padding: 10px 0;
                color: var(--el-text-color-primary);
            }
            .comment-publish-content {
                .comment-publish-btn {
                    display: flex;
                    justify-content: flex-start;
                    padding: 10px 0;
                }
            }
        }
    }
}
</style>
