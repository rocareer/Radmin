<template>
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
                    :label-width="baTable.form.labelWidth! + 'px'"
                    :rules="rules"
                >
                    <FormItem
                        :label="t('kb.tencentConfig.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('kb.tencentConfig.name placeholder')"
                    />
                    <FormItem
                        :label="t('kb.tencentConfig.secret_id')"
                        type="string"
                        v-model="baTable.form.items!.secret_id"
                        prop="secret_id"
                        :placeholder="t('kb.tencentConfig.secret_id placeholder')"
                    />
                    <FormItem
                        :label="t('kb.tencentConfig.secret_key')"
                        type="string"
                        v-model="baTable.form.items!.secret_key"
                        prop="secret_key"
                        :placeholder="t('kb.tencentConfig.secret_key placeholder')"
                        :input-attr="{ showPassword: true }"
                    />
                    <FormItem
                        :label="t('kb.tencentConfig.region')"
                        type="select"
                        v-model="baTable.form.items!.region"
                        prop="region"
                        :data="{ content: regionOptions }"
                    />
                    <FormItem
                        :label="t('kb.tencentConfig.endpoint')"
                        type="string"
                        v-model="baTable.form.items!.endpoint"
                        prop="endpoint"
                        :placeholder="t('kb.tencentConfig.endpoint placeholder')"
                    />
                    <FormItem
                        :label="t('kb.tencentConfig.status')"
                        type="radio"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { 1: t('Enable'), 0: t('Disable') } }"
                    />
                    <FormItem
                        :label="t('kb.tencentConfig.remark')"
                        type="textarea"
                        v-model="baTable.form.items!.remark"
                        prop="remark"
                        :placeholder="t('kb.tencentConfig.remark placeholder')"
                        :input-attr="{ rows: 3 }"
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

// 地域选项
const regionOptions = reactive([
    { value: 'ap-beijing', label: '北京' },
    { value: 'ap-shanghai', label: '上海' },
    { value: 'ap-guangzhou', label: '广州' },
    { value: 'ap-chengdu', label: '成都' },
    { value: 'ap-chongqing', label: '重庆' },
    { value: 'ap-hongkong', label: '香港' },
    { value: 'ap-singapore', label: '新加坡' },
    { value: 'na-toronto', label: '多伦多' },
    { value: 'na-siliconvalley', label: '硅谷' },
    { value: 'eu-frankfurt', label: '法兰克福' },
])

// 表单验证规则
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', message: t('kb.tencentConfig.name required') })],
    secret_id: [buildValidatorData({ name: 'required', message: t('kb.tencentConfig.secret_id required') })],
    secret_key: [buildValidatorData({ name: 'required', message: t('kb.tencentConfig.secret_key required') })],
    region: [buildValidatorData({ name: 'required', message: t('kb.tencentConfig.region required') })],
    endpoint: [buildValidatorData({ name: 'required', message: t('kb.tencentConfig.endpoint required') })],
})
</script>