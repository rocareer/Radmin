<template>
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.table.extend!.infoId ? true : false"
        @close="baTable.table.extend!.infoId = 0"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                <span>记录</span>
                <span class="window-loading">
                    <Icon v-show="state.loading" size="14" name="el-icon-Loading" class="is-loading" />
                </span>
            </div>
        </template>
        <TransitionGroup @scroll="onScroll" class="chat-records ba-scroll-style" name="el-fade-in" tag="div">
            <div class="chat-records-grow" key="chatRecordsGrow"></div>
            <div
                class="chat-record-item"
                v-for="(item, idx) in state.messages"
                :key="item.id || item.content"
                :class="item.sender_type == 'ai' ? 'you' : 'me'"
            >
                <span v-if="item.type == 'system'" class="system">{{ item.content }}</span>
                <div v-else class="record-content-box">
                    <div class="chat-record-nickname">
                        {{ item.sender_type == 'ai' ? item.sender_id : baTable.table.extend!.infoNickname }}
                    </div>
                    <div class="record-content">
                        <template v-if="item.type == 'text'">
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
                            </div>
                        </template>
                        <el-image
                            :hide-on-click-modal="true"
                            v-else-if="item.type == 'image'"
                            :preview-src-list="[item.content]"
                            :src="item.content"
                        ></el-image>
                        <el-link target="_blank" type="success" v-else-if="item.type == 'link'" :href="item.content">
                            {{ item.title ?? item.content }}
                        </el-link>
                    </div>
                    <div class="content-tags">
                        <el-tooltip
                            effect="dark"
                            :content="item.sender_type == 'ai' ? '输入输出消耗 tokens' : '向量化消耗 tokens'"
                            placement="bottom"
                        >
                            <el-tag>{{ item.tokens }}</el-tag>
                        </el-tooltip>
                        <template v-if="item.kbs?.length">
                            <el-tooltip effect="dark" :content="kb" placement="bottom" v-for="(kb, kbi) in item.kbsTable?.title" :key="kbi">
                                <el-tag @click="router.push({ path: '/admin/ai/kbsContent', query: { id: item.kbs![kbi] } })" class="kbs-item">
                                    {{ kb }}
                                </el-tag>
                            </el-tooltip>
                        </template>
                    </div>
                </div>
            </div>
        </TransitionGroup>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.table.extend!.infoId = 0">关闭</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject, reactive, watch } from 'vue'
import type baTableClass from '/@/utils/baTable'
import { records } from '/@/api/ai'
import { MdPreview } from 'md-editor-v3'
import { useRouter } from 'vue-router'
import { debounce } from 'lodash-es'

const baTable = inject('baTable') as baTableClass

const router = useRouter()
const state: {
    loading: boolean
    messages: anyObj[]
    page: number
    nextPage: boolean
} = reactive({
    loading: false,
    messages: [],
    page: 1,
    nextPage: true,
})

const getRecords = () => {
    if (!state.nextPage || state.loading) return
    state.loading = true
    records(baTable.table.extend!.infoId, state.page)
        .then((res) => {
            state.nextPage = res.data.nextPage

            for (const key in res.data.messages) {
                for (const message in res.data.messages[key].data) {
                    state.messages.push(res.data.messages[key].data[message])
                }
                state.messages.push({
                    type: 'system',
                    content: res.data.messages[key].datetime,
                })
            }

            if (!res.data.nextPage) {
                state.messages.push({
                    type: 'system',
                    content: '没有更多消息了',
                })
            }
        })
        .finally(() => {
            state.loading = false
        })
}

const onScroll = debounce((e: Event) => {
    const target = e.target as HTMLDivElement
    const scrollTop = Math.abs(target.scrollTop)
    const scrollHeight = target.scrollHeight
    const clientHeight = target.clientHeight

    if (scrollTop + clientHeight + 10 >= scrollHeight) {
        state.page++
        getRecords()
    }
}, 300)

watch(
    () => baTable.table.extend!.infoId,
    () => {
        if (baTable.table.extend!.infoId > 0) {
            state.messages = []
            state.page = 1
            state.nextPage = true
            getRecords()
        }
    }
)
</script>

<style scoped lang="scss">
.title {
    display: flex;
    align-items: center;
    .window-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 6px;
        margin-top: 2px;
    }
}
.message-md-content {
    background: transparent;
    :deep(.default-theme p) {
        padding: 0;
        line-height: 1.3;
    }
    :deep(.md-editor-preview-wrapper) {
        padding: 0;
        overflow: hidden;
    }
    :deep(.default-theme ul li) {
        margin: 0;
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
.chat-records {
    display: flex;
    flex-direction: column-reverse;
    padding: 0;
    padding-bottom: 10px;
    margin-right: -10px;
    height: 100%;
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
    .system {
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
        align-content: center;
        padding-right: 10px;
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
    }
    .content-tags {
        margin-top: 4px;
        .el-tag {
            margin-right: 4px;
            cursor: pointer;
        }
        .kbs-item.el-tag {
            display: inline-flex;
            width: 100px;
            :deep(.el-tag__content) {
                width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
    }
}
</style>
