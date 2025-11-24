<template>
    <div class="ba-editor md-editor-v3">
        <md-editor
            ref="editorRef"
            :theme="state.theme"
            :preview="preview"
            :language="state.language"
            :toolbarsExclude="state.toolbarsExclude"
            v-bind="$attrs"
            @uploadImg="onUploadImg"
            v-loading="state.uploadLoading"
            element-loading-text="Uploading..."
            showCodeRowNumber
        />
    </div>
</template>

<script setup lang="ts">
import { MdEditor, Themes, ToolbarNames, MdPreviewProps, config as mdConfig, ExposeParam } from 'md-editor-v3'
import screenfull from 'screenfull'
import prettier from 'prettier'
import { fileUpload } from '~/api/common'
import { getDark } from '~/utils/dark'
import { getLanguage } from '~/lang'
import 'md-editor-v3/lib/style.css'

interface Props extends /* @vue-ignore */ Partial<MdPreviewProps> {
    theme?: Themes
    preview?: boolean
    language?: string
    toolbarsExclude?: ToolbarNames[]
    // 安装了云存储之后，图片/文件任然只上传到服务器而不是云存储
    fileForceLocal?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    preview: false,
    language: '',
    toolbarsExclude: () => [],
    fileForceLocal: false,
})

const defaultToolbarsExclude = [
    'sub',
    'sup',
    'mermaid',
    'katex',
    'revoke',
    'next',
    'save',
    'fullscreen',
    'task',
    '=',
    'codeRow',
    'htmlPreview',
    'catalog',
    'github',
]

const editorRef = ref<ExposeParam>()
const state: {
    toolbarsExclude: ToolbarNames[]
    uploadLoading: boolean
    language: string
    theme: Themes
} = reactive({
    toolbarsExclude: props.toolbarsExclude.length ? props.toolbarsExclude : (defaultToolbarsExclude as ToolbarNames[]),
    uploadLoading: false,
    language: props.language ? props.language : getLanguage() == 'zh-cn' ? 'zh-CN' : 'en-US',
    theme: props.theme ? props.theme : getDark() ? 'dark' : 'light',
})

mdConfig({
    editorExtensions: {
        screenfull: {
            instance: screenfull,
        },
        prettier: {
            prettierInstance: prettier,
        },
    },
})

const onUploadImg = async (files: File[], callback: (urls: string[]) => void) => {
    state.uploadLoading = true
    const res = await Promise.all(
        files.map((file) => {
            return new Promise((rev, rej) => {
                const fd = new FormData()
                fd.append('file', file)
                fileUpload(fd, { uuid: uuid() }, props.fileForceLocal).then((res) => {
                    if (res.code == 1) {
                        rev(res)
                    } else {
                        rej(res.msg)
                    }
                })
            })
        })
    ).finally(() => {
        state.uploadLoading = false
    })
    callback(
        res.map((item) => {
            let url = (item as ApiResponse).data.file.full_url
            url = url.replace(/\(/g, '\\(').replace(/\)/g, '\\)')
            return url
        })
    )
}

const getRef = () => {
    return editorRef.value
}

defineExpose({
    getRef,
})
</script>

<style scoped lang="scss">
.ba-editor.md-editor-v3 {
    width: 100%;
    :deep(svg.md-editor-icon) {
        box-sizing: content-box;
    }
}
.ba-editor.md-editor-v3 :deep(.md-editor-footer) {
    height: 30px;
    line-height: 30px;
}
</style>
