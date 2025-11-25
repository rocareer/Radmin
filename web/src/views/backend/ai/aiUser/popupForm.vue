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
                        :label="t('ai.aiUser.user_type')"
                        type="radio"
                        v-model="baTable.form.items!.user_type"
                        prop="user_type"
                        :data="{ content: { user: t('ai.aiUser.user_type user'), admin: t('ai.aiUser.user_type admin') } }"
                        :placeholder="t('Please select field', { field: t('ai.aiUser.user_type') })"
                    />
                    <FormItem
                        :key="baTable.form.items!.user_type"
                        :label="t('ai.aiUser.user_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.user_id"
                        prop="user_id"
                        :input-attr="{
                            pk: baTable.form.items!.user_type == 'user' ? 'user.id' : 'admin.id',
                            field: 'nickname',
                            remoteUrl: baTable.form.items!.user_type == 'user' ? '/admin/user.User/index' : '/admin/auth.Admin/index',
                        }"
                        :placeholder="t('Please select field', { field: t('ai.aiUser.user_id') })"
                    />
                    <el-form-item v-if="baTable.form.operate == 'Edit'" :label="t('ai.aiUser.tokens')">
                        <el-input v-model.number="baTable.form.items!.tokens" readonly>
                            <template #append>
                                <el-button @click="changeTokens()">调整</el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                    <FormItem
                        :label="t('ai.aiUser.messages')"
                        type="number"
                        prop="messages"
                        :input-attr="{ step: 1, valueOnClear: 0 }"
                        v-model.number="baTable.form.items!.messages"
                        :placeholder="t('Please input field', { field: t('ai.aiUser.messages') })"
                    />
                    <FormItem
                        :label="t('ai.aiUser.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { '0': t('ai.aiUser.status 0'), '1': t('ai.aiUser.status 1') } }"
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
import { useRouter } from 'vue-router'

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()
const router = useRouter()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    user_type: [buildValidatorData({ name: 'required', title: t('ai.aiUser.user_type') })],
    user_id: [buildValidatorData({ name: 'required', message: t('Please select field', { field: t('ai.aiUser.user_id') }) })],
    tokens: [buildValidatorData({ name: 'number', title: t('ai.aiUser.tokens') })],
    messages: [buildValidatorData({ name: 'number', title: t('ai.aiUser.messages') })],
    last_use_time: [buildValidatorData({ name: 'date', title: t('ai.aiUser.last_use_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('ai.aiUser.create_time') })],
})

const changeTokens = () => {
    router.push({
        name: 'ai/userTokens',
        query: {
            ai_user_id: baTable.form.items!.id,
        },
    })
}
</script>

<style scoped lang="scss"></style>
