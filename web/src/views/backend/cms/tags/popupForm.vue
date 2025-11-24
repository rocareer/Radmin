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
                        :label="t('cms.tags.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('cms.tags.name') })"
                    />
                    <FormItem
                        :label="t('cms.tags.type')"
                        type="select"
                        v-model="baTable.form.items!.type"
                        prop="type"
                        :data="{
                            content: {
                                default: t('cms.tags.type default'),
                                success: t('cms.tags.type success'),
                                info: t('cms.tags.type info'),
                                warning: t('cms.tags.type warning'),
                                danger: t('cms.tags.type danger'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.tags.type') })"
                    />
                    <FormItem
                        :label="t('cms.tags.seotitle')"
                        type="string"
                        v-model="baTable.form.items!.seotitle"
                        prop="seotitle"
                        :placeholder="t('Please input field', { field: t('cms.tags.seotitle') })"
                    />
                    <FormItem
                        :label="t('cms.tags.keywords')"
                        type="textarea"
                        v-model="baTable.form.items!.keywords"
                        prop="keywords"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.tags.keywords') })"
                    />
                    <FormItem
                        :label="t('cms.tags.description')"
                        type="textarea"
                        v-model="baTable.form.items!.description"
                        prop="description"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.tags.description') })"
                    />
                    <FormItem
                        :label="t('Weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('Weigh') })"
                    />
                    <FormItem
                        :label="t('cms.tags.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { 0: t('cms.tags.status 0'), 1: t('cms.tags.status 1') } }"
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
    name: [buildValidatorData({ name: 'required', title: t('cms.tags.name') })],
    update_time: [buildValidatorData({ name: 'date', title: t('cms.tags.update_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('cms.tags.create_time') })],
})
</script>

<style scoped lang="scss"></style>
