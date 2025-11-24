<template>
    <!-- 对话框表单 -->
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
                        :label="t('cms.comment.user_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.user_id"
                        prop="user_id"
                        :input-attr="{ pk: 'user.id', field: 'username', remoteUrl: '/admin/user.User/index' }"
                        :placeholder="t('Please select field', { field: t('cms.comment.user_id') })"
                    />
                    <FormItem
                        :label="t('cms.comment.type')"
                        type="radio"
                        v-model="baTable.form.items!.type"
                        prop="type"
                        :data="{ content: { content: t('cms.comment.type content') } }"
                        :placeholder="t('Please select field', { field: t('cms.comment.type') })"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type == 'content'"
                        :label="t('cms.comment.content_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.content_id"
                        prop="content_id"
                        :input-attr="{
                            pk: 'content.id',
                            field: 'title',
                            remoteUrl: '/admin/cms.Content/index',
                            emptyValues: ['', null, undefined, 0],
                            valueOnClear: 0,
                        }"
                        :placeholder="t('Please select field', { field: t('cms.comment.content_id') })"
                    />
                    <FormItem
                        :label="t('cms.comment.content')"
                        type="editor"
                        v-model="baTable.form.items!.content"
                        prop="content"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.comment.content') })"
                    />
                    <FormItem
                        :label="t('cms.comment.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('cms.comment.weigh') })"
                    />
                    <FormItem
                        :label="t('cms.comment.status')"
                        type="radio"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{
                            content: {
                                normal: t('cms.comment.status normal'),
                                unaudited: t('cms.comment.status unaudited'),
                                refused: t('cms.comment.status refused'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.comment.status') })"
                    />
                    <FormItem
                        :label="t('cms.comment.remark')"
                        type="textarea"
                        v-model="baTable.form.items!.remark"
                        prop="remark"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.comment.remark') })"
                        :attr="{
                            blockHelp: '当设定状态为拒绝时，请在此填写拒绝理由',
                        }"
                    />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
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
import type { ElForm, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    user_id: [buildValidatorData({ name: 'required', message: t('Please select field', { field: t('cms.comment.user_id') }) })],
    content: [buildValidatorData({ name: 'editorRequired', title: t('cms.comment.content') })],
    create_time: [buildValidatorData({ name: 'date', title: t('cms.comment.create_time') })],
})
</script>

<style scoped lang="scss"></style>
