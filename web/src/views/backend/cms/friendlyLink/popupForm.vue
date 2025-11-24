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
                        :label="t('cms.friendlyLink.user_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.user_id"
                        prop="user_id"
                        :input-attr="{
                            pk: 'user.id',
                            field: 'username',
                            remoteUrl: '/admin/user.User/index',
                            emptyValues: ['', null, undefined, 0],
                            valueOnClear: 0,
                        }"
                        :placeholder="t('Please select field', { field: t('cms.friendlyLink.user_id') })"
                    />
                    <FormItem
                        :label="t('cms.friendlyLink.title')"
                        type="string"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :placeholder="t('Please input field', { field: t('cms.friendlyLink.title') })"
                    />
                    <FormItem
                        :label="t('cms.friendlyLink.link')"
                        type="string"
                        v-model="baTable.form.items!.link"
                        prop="link"
                        :placeholder="t('Please input field', { field: t('cms.friendlyLink.link') })"
                    />
                    <FormItem
                        :label="t('cms.friendlyLink.target')"
                        type="radio"
                        v-model="baTable.form.items!.target"
                        prop="target"
                        :data="{
                            content: {
                                _blank: t('cms.friendlyLink.target _blank'),
                                _self: t('cms.friendlyLink.target _self'),
                                _top: t('cms.friendlyLink.target _top'),
                                _parent: t('cms.friendlyLink.target _parent'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.friendlyLink.target') })"
                    />
                    <FormItem :label="t('cms.friendlyLink.logo')" type="image" v-model="baTable.form.items!.logo" prop="logo" />
                    <FormItem
                        :label="t('cms.friendlyLink.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('cms.friendlyLink.weigh') })"
                    />
                    <FormItem
                        :label="t('cms.friendlyLink.status')"
                        type="radio"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{
                            content: {
                                disable: t('cms.friendlyLink.status disable'),
                                enable: t('cms.friendlyLink.status enable'),
                                pending_trial: t('cms.friendlyLink.status pending_trial'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.friendlyLink.status') })"
                    />
                    <FormItem
                        :label="t('cms.friendlyLink.remark')"
                        type="textarea"
                        v-model="baTable.form.items!.remark"
                        prop="remark"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.friendlyLink.remark') })"
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
    title: [buildValidatorData({ name: 'required', title: t('cms.friendlyLink.title') })],
    link: [buildValidatorData({ name: 'required', title: t('cms.friendlyLink.link') })],
    logo: [
        buildValidatorData({
            name: 'required',
            title: t('cms.friendlyLink.logo'),
            message: t('Please select field', { field: t('cms.friendlyLink.logo') }),
        }),
    ],
    update_time: [buildValidatorData({ name: 'date', title: t('cms.friendlyLink.update_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('cms.friendlyLink.create_time') })],
})
</script>

<style scoped lang="scss"></style>
