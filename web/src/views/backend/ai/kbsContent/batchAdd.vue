<template>
    <div>
        <el-dialog :destroy-on-close="true" top="10vh" v-model="baTable.form.extend!.showBatchAdd" title="批量添加" width="50%">
            <el-alert
                title="实验性功能；可以将一段文本提交给AI模型，让其学习内容并输出多个问答对；若需批量导入 xls 中的知识点，请使用数据导入模块。"
                :closable="false"
                type="success"
            />
            <el-scrollbar style="height: 52vh" class="ba-table-form-scrollbar">
                <div class="ba-operate-form" :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'">
                    <el-form
                        ref="formRef"
                        @submit.prevent=""
                        @keyup.enter="onSubmit"
                        :model="state.items"
                        label-position="right"
                        :label-width="baTable.form.labelWidth + 'px'"
                        :rules="state.rules"
                    >
                        <FormItem
                            :key="state.items.type"
                            label="文本内容来源"
                            type="radio"
                            v-model="state.items.type"
                            prop="type"
                            :data="{ content: { url: '从URL读取', text: '手动输入', file: '上传文件' } }"
                            :attr="{
                                blockHelp: state.items.type == 'file' ? '支持txt、md格式（md需先于 config/upload.php 内允许上传）' : '',
                            }"
                        />
                        <template v-if="state.items.type == 'url'">
                            <FormItem label="URL" prop="url" placeholder="https://example.com" type="string" v-model="state.items.url" />
                            <el-form-item>
                                <el-button v-blur @click="onGetUrlContent">点击读取内容</el-button>
                                <div class="block-help">
                                    从URL读取的内容比较杂乱，大概率不能正常被AI分析，您可以于 /app/admin/controller/ai/KbsContent.php 的
                                    getUrlContent() 方法内自定义内容处理方案。
                                </div>
                            </el-form-item>
                        </template>
                        <FormItem
                            v-if="state.items.type == 'file'"
                            :input-attr="{
                                onSuccess: changeFile,
                                forceLocal: true,
                            }"
                            label="文件"
                            type="file"
                            v-model="state.items.file"
                        />
                        <FormItem
                            label="文本内容"
                            type="textarea"
                            v-model="state.items.content"
                            prop="content"
                            :input-attr="{ rows: 6 }"
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="onSubmit"
                        />
                        <FormItem
                            :key="state.items.model"
                            label="AI模型"
                            type="radio"
                            v-model="state.items.model"
                            prop="type"
                            :data="{ content: { 'qwen-turbo': '千问turbo', 'qwen-plus': '千问plus' } }"
                        />
                        <FormItem
                            label="添加到知识库"
                            type="remoteSelects"
                            v-model="state.items.ai_kbs_ids"
                            :input-attr="{ pk: 'kbs.id', field: 'name', remoteUrl: '/admin/ai.Kbs/index' }"
                        />
                        <el-divider v-if="state.qas.length" content-position="left"> 问答对（不需要添加的直接清空标题或内容） </el-divider>
                        <template v-for="(item, idx) in state.qas" :key="idx">
                            <FormItem label="问" type="string" v-model="item.q" />
                            <FormItem label="答" type="textarea" v-model="item.a" :input-attr="{ rows: 3 }" />
                        </template>
                    </el-form>
                </div>
            </el-scrollbar>
            <template #footer>
                <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                    <el-button @click="baTable.form.extend!.showBatchAdd = false">取消</el-button>
                    <el-button v-blur :loading="state.submitLoading" @click="onGenerate" type="primary"> 生成问答对 </el-button>
                    <el-button v-blur :loading="state.submitLoading" @click="onSubmit" type="success"> 保存问答对 </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { inject, ref, reactive } from 'vue'
import type baTableClass from '/@/utils/baTable'
import { ElNotification } from 'element-plus'
import type { FormInstance, FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import { buildValidatorData } from '/@/utils/validate'
import { getUrlContent, getFileContent, generateQa, batchAdd } from '/@/api/backend/ai/index'

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const state: {
    items: {
        type: 'url' | 'text' | 'file'
        url: string
        file: string
        content: string
        model: string
        ai_kbs_ids: string[]
    }
    rules: Partial<Record<string, FormItemRule[]>>
    submitLoading: boolean
    qas: {
        q: string
        a: string
    }[]
} = reactive({
    items: {
        type: 'text',
        url: '',
        file: '',
        content: '',
        model: 'qwen-turbo',
        ai_kbs_ids: [],
    },
    rules: {
        url: [buildValidatorData({ name: 'url', title: 'URL' })],
        content: [buildValidatorData({ name: 'required', title: '文本内容' })],
    },
    submitLoading: false,
    qas: [],
})
const onGetUrlContent = () => {
    formRef.value?.validateField(['url']).then((valid) => {
        if (!valid) return
        getUrlContent(state.items.url).then((res) => {
            state.items.content = res.data.content
        })
    })
}
const onGenerate = () => {
    state.submitLoading = true
    formRef.value?.validate((valid) => {
        if (!valid) return
        generateQa(state.items.model, state.items.content)
            .then((res) => {
                state.qas = res.data.output
            })
            .finally(() => {
                state.submitLoading = false
            })
    })
}
const onSubmit = () => {
    if (!state.qas.length) {
        ElNotification({
            type: 'error',
            message: '请先生成问答对',
        })
        return
    }
    state.submitLoading = true
    batchAdd({
        qas: state.qas,
        ai_kbs_ids: state.items.ai_kbs_ids,
        url: state.items.url,
    })
        .then(() => {
            state.qas = []
        })
        .finally(() => {
            state.submitLoading = false
        })
}
const changeFile = (e: { data: { file: { url: string } } }) => {
    getFileContent(e.data.file.url).then((res) => {
        state.items.content = res.data.content
    })
}
</script>

<style scoped lang="scss"></style>
