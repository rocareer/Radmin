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
                :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem
                        :label="t('kb.kb.kb_type_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.kb_type_id"
                        prop="kb_type_id"
                        :input-attr="{ pk: 'type.id', field: 'name', remoteUrl: '/admin/kb/Type/index' }"
                        :placeholder="t('Please select field', { field: t('kb.kb.kb_type_id') })"
                    />
                    <FormItem
                        :label="t('kb.kb.kb_category_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.kb_category_id"
                        prop="kb_category_id"
                        :input-attr="{ pk: 'category.id', field: 'name', remoteUrl: '/admin/kb/Category/index' }"
                        :placeholder="t('Please select field', { field: t('kb.kb.kb_category_id') })"
                    />
                    <FormItem
                        :label="t('kb.kb.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('kb.kb.name') })"
                    />
                    <FormItem
                        :label="t('kb.kb.admin_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.admin_id"
                        prop="admin_id"
                        :input-attr="{ pk: 'admin.id', field: 'username', remoteUrl: '/admin/auth/Admin/index' }"
                        :placeholder="t('Please select field', { field: t('kb.kb.admin_id') })"
                    />
                    <FormItem
                        :label="t('kb.kb.user_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.user_id"
                        prop="user_id"
                        :input-attr="{ pk: 'user.id', field: 'username', remoteUrl: '/admin/user/User/index' }"
                        :placeholder="t('Please select field', { field: t('kb.kb.user_id') })"
                    />
                    <FormItem
                        :label="t('kb.kb.count')"
                        type="number"
                        v-model="baTable.form.items!.count"
                        prop="count"
                        :input-attr="{ step: 1 }"
                        :placeholder="t('Please input field', { field: t('kb.kb.count') })"
                    />
                    <FormItem
                        :label="t('kb.kb.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :input-attr="{ content: { '0': t('kb.kb.status 0'), '1': t('kb.kb.status 1') } }"
                    />
                    <FormItem
                        :label="t('kb.kb.remark')"
                        type="textarea"
                        v-model="baTable.form.items!.remark"
                        prop="remark"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('kb.kb.remark') })"
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
import type { FormItemRule } from 'element-plus'
import { inject, reactive, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import type baTableClass from '/@/utils/baTable'
import { buildValidatorData } from '/@/utils/validate'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    count: [buildValidatorData({ name: 'number', title: t('kb.kb.count') })],
    update_time: [buildValidatorData({ name: 'date', title: t('kb.kb.update_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('kb.kb.create_time') })],
})
</script>

<style scoped lang="scss"></style>
