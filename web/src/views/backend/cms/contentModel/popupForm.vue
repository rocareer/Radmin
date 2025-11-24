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
                        :label="t('cms.contentModel.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('cms.contentModel.name') })"
                        :attr="{
                            blockHelp: baTable.form.operate == 'Add' ? '自动建立对应管理功能，添加后请刷新后台' : '',
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModel.table')"
                        type="string"
                        v-model="baTable.form.items!.table"
                        prop="table"
                        :placeholder="t('Please input field', { field: t('cms.contentModel.table') })"
                        :attr="{
                            blockHelp: '建议以 cms_ 为前缀，一个内容模型对应一个数据表',
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModel.info')"
                        type="string"
                        v-model="baTable.form.items!.info"
                        prop="info"
                        placeholder="内容详情模板"
                        :attr="{
                            blockHelp:
                                '请将模板放入 /web-nuxt/composables/template/cms/info 文件夹，仅填写模板文件名即可，默认可用:default=默认,download=下载',
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModel.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { 0: t('cms.contentModel.status 0'), 1: t('cms.contentModel.status 1') } }"
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
    name: [buildValidatorData({ name: 'required', title: t('cms.contentModel.name') })],
    table: [buildValidatorData({ name: 'required', title: t('cms.contentModel.table') })],
    info: [buildValidatorData({ name: 'required', title: t('cms.contentModel.info') })],
    update_time: [buildValidatorData({ name: 'date', title: t('cms.contentModel.update_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('cms.contentModel.create_time') })],
})
</script>

<style scoped lang="scss"></style>
