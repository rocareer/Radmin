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
                    <FormItem
                        :label="t('ai.sessionMessage.type')"
                        type="select"
                        v-model="baTable.form.items!.type"
                        prop="type"
                        :data="{
                            content: {
                                text: t('ai.sessionMessage.type text'),
                                image: t('ai.sessionMessage.type image'),
                                link: t('ai.sessionMessage.type link'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('ai.sessionMessage.type') })"
                    />
                    <FormItem
                        :label="t('ai.sessionMessage.session_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.session_id"
                        prop="session_id"
                        :input-attr="{
                            pk: 'session.id',
                            field: 'title',
                            remoteUrl: '/admin/ai.Session/index',
                        }"
                        :placeholder="t('Please select field', { field: t('ai.sessionMessage.session_id') })"
                    />
                    <FormItem
                        :label="t('ai.sessionMessage.sender_type')"
                        type="radio"
                        v-model="baTable.form.items!.sender_type"
                        prop="sender_type"
                        :data="{ content: { ai: t('ai.sessionMessage.sender_type ai'), user: t('ai.sessionMessage.sender_type user') } }"
                        :placeholder="t('Please select field', { field: t('ai.sessionMessage.sender_type') })"
                    />
                    <FormItem
                        :label="t('ai.sessionMessage.sender_id')"
                        type="string"
                        v-model="baTable.form.items!.sender_id"
                        prop="sender_id"
                        :placeholder="t('Please input field', { field: t('ai.sessionMessage.sender_id') })"
                        :attr="{
                            blockHelp: 'AI为模型名称，用户为AI会员昵称',
                        }"
                    />
                    <FormItem
                        :label="t('ai.sessionMessage.content')"
                        type="editor"
                        v-model="baTable.form.items!.content"
                        prop="content"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('ai.sessionMessage.content') })"
                        :input-attr="{
                            class: 'w100',
                        }"
                    />
                    <FormItem
                        :label="t('ai.sessionMessage.tokens')"
                        type="number"
                        prop="tokens"
                        :input-attr="{ step: 1, valueOnClear: 0 }"
                        v-model.number="baTable.form.items!.tokens"
                        :placeholder="t('Please input field', { field: t('ai.sessionMessage.tokens') })"
                    />
                    <FormItem
                        :label="t('ai.sessionMessage.kbs')"
                        type="remoteSelects"
                        v-model="baTable.form.items!.kbs"
                        prop="kbs"
                        :input-attr="{ pk: 'kbs_content.id', field: 'title', remoteUrl: '/admin/ai.KbsContent/index' }"
                        :placeholder="t('Please select field', { field: t('ai.sessionMessage.kbs') })"
                    />
                    <FormItem
                        :label="t('ai.sessionMessage.consumption_tokens')"
                        type="number"
                        prop="consumption_tokens"
                        :input-attr="{ step: 1, valueOnClear: 0 }"
                        v-model.number="baTable.form.items!.consumption_tokens"
                        :placeholder="t('Please input field', { field: t('ai.sessionMessage.consumption_tokens') })"
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
    type: [buildValidatorData({ name: 'required', title: t('ai.sessionMessage.type') })],
    session_id: [buildValidatorData({ name: 'required', title: t('ai.sessionMessage.session_id') })],
    sender_type: [buildValidatorData({ name: 'required', title: t('ai.sessionMessage.sender_type') })],
    sender_id: [buildValidatorData({ name: 'required', title: t('ai.sessionMessage.sender_id') })],
    content: [buildValidatorData({ name: 'editorRequired', title: t('ai.sessionMessage.content') })],
    tokens: [buildValidatorData({ name: 'number', title: t('ai.sessionMessage.tokens') })],
    create_time: [buildValidatorData({ name: 'date', title: t('ai.sessionMessage.create_time') })],
})
</script>

<style scoped lang="scss"></style>
