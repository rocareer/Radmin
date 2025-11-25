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
                        :label="t('ai.userTokens.ai_user_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.ai_user_id"
                        prop="ai_user_id"
                        :input-attr="{ pk: 'ai_user.id', field: 'nickname', remoteUrl: '/admin/ai.AiUser/index' }"
                        :placeholder="t('Please select field', { field: t('ai.userTokens.ai_user_id') })"
                    />
                    <FormItem
                        :label="t('ai.userTokens.tokens')"
                        type="number"
                        prop="tokens"
                        :input-attr="{ step: 1, valueOnClear: 0 }"
                        v-model.number="baTable.form.items!.tokens"
                        :placeholder="t('Please input field', { field: t('ai.userTokens.tokens') })"
                    />
                    <FormItem
                        :label="t('ai.userTokens.memo')"
                        type="textarea"
                        v-model="baTable.form.items!.memo"
                        prop="memo"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('ai.userTokens.memo') })"
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
    ai_user_id: [buildValidatorData({ name: 'required', title: t('ai.userTokens.ai_user_id') })],
    tokens: [
        buildValidatorData({ name: 'required', title: t('ai.userTokens.tokens') }),
        buildValidatorData({ name: 'number', title: t('ai.userTokens.tokens') }),
    ],
    memo: [buildValidatorData({ name: 'required', title: t('ai.userTokens.memo') })],
    create_time: [buildValidatorData({ name: 'date', title: t('ai.userTokens.create_time') })],
})
</script>

<style scoped lang="scss"></style>
