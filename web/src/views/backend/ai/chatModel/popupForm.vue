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
                        :label="t('ai.chatModel.title')"
                        type="string"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :placeholder="t('Please input field', { field: t('ai.chatModel.title') })"
                    />
                    <FormItem :label="t('ai.chatModel.logo')" type="image" v-model="baTable.form.items!.logo" prop="logo" />
                    <FormItem
                        :label="t('ai.chatModel.name')"
                        type="select"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :data="{
                            content: baTable.table.extend!.selectModel,
                        }"
                        :placeholder="t('Please select field', { field: t('ai.chatModel.name') })"
                    />
                    <FormItem
                        :label="t('ai.chatModel.greeting')"
                        type="string"
                        v-model="baTable.form.items!.greeting"
                        prop="greeting"
                        :placeholder="t('Please input field', { field: t('ai.chatModel.greeting') })"
                    />
                    <FormItem
                        :label="t('ai.chatModel.api_url')"
                        type="string"
                        v-model="baTable.form.items!.api_url"
                        prop="api_url"
                        :placeholder="t('Please input field', { field: t('ai.chatModel.api_url') })"
                        :attr="{
                            blockHelp: '模型均有默认接口URL，但当你需要自定义时(自建转发服务等)，可以在此填写',
                        }"
                    />
                    <FormItem
                        :label="t('ai.chatModel.api_key')"
                        type="string"
                        v-model="baTable.form.items!.api_key"
                        prop="api_key"
                        :placeholder="t('Please input field', { field: t('ai.chatModel.api_key') })"
                    />
                    <FormItem
                        :label="t('ai.chatModel.proxy')"
                        type="string"
                        v-model="baTable.form.items!.proxy"
                        prop="proxy"
                        :placeholder="t('Please input field', { field: t('ai.chatModel.proxy') })"
                        :attr="{
                            blockHelp: '若需要代理，请在此填写',
                        }"
                    />
                    <FormItem
                        :label="t('ai.chatModel.tokens_multiplier')"
                        type="number"
                        prop="tokens_multiplier"
                        :input-attr="{ step: 1, valueOnClear: 0 }"
                        v-model.number="baTable.form.items!.tokens_multiplier"
                        :placeholder="t('Please input field', { field: t('ai.chatModel.tokens_multiplier') })"
                        :attr="{
                            blockHelp: '若乘0，则表示用户使用此模型不会收取tokens',
                        }"
                    />
                    <FormItem
                        :label="t('ai.chatModel.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('ai.chatModel.weigh') })"
                    />
                    <FormItem
                        :label="t('ai.chatModel.user_status')"
                        type="switch"
                        v-model="baTable.form.items!.user_status"
                        prop="user_status"
                        :data="{ content: { '0': t('ai.chatModel.user_status 0'), '1': t('ai.chatModel.user_status 1') } }"
                    />
                    <FormItem
                        :label="t('ai.chatModel.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { '0': t('ai.chatModel.status 0'), '1': t('ai.chatModel.status 1') } }"
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
    name: [buildValidatorData({ name: 'required', title: t('ai.chatModel.name') })],
    api_key: [buildValidatorData({ name: 'required', title: t('ai.chatModel.api_key') })],
    tokens_multiplier: [
        buildValidatorData({ name: 'required', title: 'tokens收取乘数' }),
        buildValidatorData({ name: 'number', title: t('ai.chatModel.tokens_multiplier') }),
    ],
    create_time: [buildValidatorData({ name: 'date', title: t('ai.chatModel.create_time') })],
})
</script>

<style scoped lang="scss"></style>
