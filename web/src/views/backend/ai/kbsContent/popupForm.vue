<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <el-form-item label="提示">
                        <div class="tips">
                            {{
                                '当前嵌入模型单次最大支持 ' + baTable.table.extend!.max_embedding_tokens + ' tokens'
                            }}，请确保知识点需要向量化的内容长度在此范围内，不然会被自动截断（一个中文字符约 1 tokens，3-4个字母约 1 tokens）
                        </div>
                    </el-form-item>
                    <FormItem
                        :label="t('ai.kbsContent.ai_kbs_ids')"
                        type="remoteSelects"
                        v-model="baTable.form.items!.ai_kbs_ids"
                        prop="ai_kbs_ids"
                        :input-attr="{ pk: 'kbs.id', field: 'name', remoteUrl: '/admin/ai.Kbs/index' }"
                        :placeholder="t('Please select field', { field: t('ai.kbsContent.ai_kbs_ids') })"
                    />
                    <FormItem
                        :label="t('ai.kbsContent.type')"
                        type="radio"
                        v-model="baTable.form.items!.type"
                        prop="type"
                        :data="{ content: { qa: t('ai.kbsContent.type qa') + '（推荐）', text: t('ai.kbsContent.type text') } }"
                        :placeholder="t('Please select field', { field: t('ai.kbsContent.type') })"
                        :attr="{
                            blockHelp: '问答对只向量化标题部分，普通文档标题和内容均向量化',
                        }"
                    />
                    <FormItem
                        :label="t('ai.kbsContent.title')"
                        type="textarea"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :input-attr="{ rows: 6 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('ai.kbsContent.title') })"
                    />
                    <FormItem
                        label="内容来源"
                        type="radio"
                        v-model="baTable.form.items!.content_source"
                        prop="content_source"
                        :data="{ content: { input: '手动输入', quote: '引用其他知识点内容' } }"
                    />
                    <FormItem
                        v-if="baTable.form.items!.content_source == 'input'"
                        :label="t('ai.kbsContent.content')"
                        type="editor"
                        v-model="baTable.form.items!.content"
                        prop="content"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        placeholder="内容可能被其他知识点引用，此时一个内容存在两个标题，所以内容中可以使用 ${title} 变量，它将被动态替换为当前匹配到的标题，就算内容没有被多个知识点引用，也建议使用上 ${title}，以便 AI 可以更好的理解"
                        :input-attr="{
                            class: 'w100',
                        }"
                    />
                    <FormItem
                        v-if="baTable.form.items!.content_source == 'quote'"
                        label="引用它的内容"
                        type="remoteSelect"
                        v-model="baTable.form.items!.content_quote"
                        prop="content_quote"
                        :input-attr="{
                            pk: 'kbs_content.id',
                            field: 'title',
                            remoteUrl: '/admin/ai.KbsContent/index',
                            valueOnClear: 0,
                        }"
                        :attr="{
                            blockHelp: '当前知识点的内容部分，直接使用被选择的知识点的内容',
                        }"
                        :placeholder="t('Please select field', { field: '被引用的知识点' })"
                    />
                    <FormItem
                        :label="t('ai.kbsContent.source')"
                        type="textarea"
                        v-model="baTable.form.items!.source"
                        prop="source"
                        :input-attr="{ rows: 2 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        placeholder="推荐以 markdown 格式填写来源链接，可以同时提供一个链接标题和URL"
                    />
                    <FormItem
                        :label="t('ai.kbsContent.hits')"
                        type="number"
                        prop="hits"
                        :input-attr="{ step: 1, valueOnClear: 0 }"
                        v-model.number="baTable.form.items!.hits"
                        :placeholder="t('Please input field', { field: t('ai.kbsContent.hits') })"
                    />
                    <FormItem
                        :label="t('ai.kbsContent.tokens')"
                        type="number"
                        prop="tokens"
                        :input-attr="{ step: 1, valueOnClear: 0 }"
                        v-model.number="baTable.form.items!.tokens"
                        :placeholder="t('Please input field', { field: t('ai.kbsContent.tokens') })"
                    />
                    <FormItem
                        :label="t('ai.kbsContent.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('ai.kbsContent.weigh') })"
                    />
                    <FormItem
                        v-if="baTable.table.extend!.text_type_switch"
                        :label="t('ai.kbsContent.text_type')"
                        type="radio"
                        v-model="baTable.form.items!.text_type"
                        prop="text_type"
                        :data="{ content: { query: t('ai.kbsContent.text_type query'), document: t('ai.kbsContent.text_type document') } }"
                        :placeholder="t('Please select field', { field: t('ai.kbsContent.text_type') })"
                        :attr="{
                            blockHelp: '知识点常用于检索请选择检索优化，以上两个方式向量化出来的数据不同，无法共用',
                        }"
                    />
                    <FormItem
                        :label="t('ai.kbsContent.status')"
                        type="radio"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{
                            content: {
                                auto: '自动判定',
                                offline: t('ai.kbsContent.status offline'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('ai.kbsContent.status') })"
                    />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    title: [buildValidatorData({ name: 'required', title: t('ai.kbsContent.title') })],
    content: [buildValidatorData({ name: 'editorRequired', title: t('ai.kbsContent.content') })],
    hits: [buildValidatorData({ name: 'number', title: t('ai.kbsContent.hits') })],
    tokens: [buildValidatorData({ name: 'number', title: t('ai.kbsContent.tokens') })],
    create_time: [buildValidatorData({ name: 'date', title: t('ai.kbsContent.create_time') })],
})
</script>

<style scoped lang="scss">
.tips {
    line-height: 1.2;
}
</style>
