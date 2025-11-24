<template>
    <div>
        <el-form label-position="top" ref="formRef" :rules="rules" :model="other" label-width="120px">
            <el-form-item :label="t('questionnaire.config.size')" prop="size">
                <el-input-number v-model="other!.size" :mini="1" :step="1">
                    <template #suffix>
                        <span>M</span>
                    </template>
                </el-input-number>
                <div class="block-help">{{ t('questionnaire.config.size_tip') }}</div>
            </el-form-item>

            <el-form-item :label="t('questionnaire.config.num')" prop="num">
                <el-input-number v-model="other!.num" :min="1" :step="1" />
                <div class="block-help">{{ t('questionnaire.config.num_tip') }}</div>
            </el-form-item>

            <FormItem
                type="string"
                :label="t('questionnaire.config.picture')"
                v-model="other!.picture"
                prop="picture"
                :placeholder="t('Please input field', { field: t('questionnaire.config.suffix') })"
                :input-attr="{
                    clearable: true,
                }"
                :attr="{
                    blockHelp: t('questionnaire.config.tip'),
                }"
            />
            <FormItem
                type="string"
                :label="t('questionnaire.config.video')"
                v-model="other!.video"
                prop="video"
                :placeholder="t('Please input field', { field: t('questionnaire.config.suffix') })"
                :input-attr="{
                    clearable: true,
                }"
                :attr="{
                    blockHelp: t('questionnaire.config.tip'),
                }"
            />
            <FormItem
                type="string"
                :label="t('questionnaire.config.file')"
                v-model="other!.file"
                prop="file"
                :placeholder="t('Please input field', { field: t('questionnaire.config.suffix') })"
                :input-attr="{
                    clearable: true,
                }"
                :attr="{
                    blockHelp: t('questionnaire.config.tip'),
                }"
            />

            <el-button style="margin-top: 15px" :loading="state.loading" @click="onSubmit(formRef)" type="primary">{{
                t('questionnaire.config.sub')
            }}</el-button>
        </el-form>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import FormItem from '/@/components/formItem/index.vue'
import { saveConfig } from '/@/api/backend/questionnaire/common'
import type { FormInstance, FormRules } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

interface Props {
    other: any
}

const props = withDefaults(defineProps<Props>(), {
    other: {},
})

const formRef = ref<FormInstance>()
const state = reactive({
    loading: false,
})

// 此处直接使用 buildValidatorData 生成了表单验证规则
const rules = reactive<FormRules>({
    size: [buildValidatorData({ name: 'required', title: t('questionnaire.config.size') })],
    num: [buildValidatorData({ name: 'required', title: t('questionnaire.config.num') })],
    picture: [buildValidatorData({ name: 'required', title: t('questionnaire.config.picture') + t('questionnaire.config.suffix') })],
    video: [buildValidatorData({ name: 'required', title: t('questionnaire.config.video') + t('questionnaire.config.suffix') })],
    file: [buildValidatorData({ name: 'required', title: t('questionnaire.config.file') + t('questionnaire.config.suffix') })],
})

const onSubmit = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            // 验证通过
            state.loading = true
            saveConfig('questionnaire_other', props.other).finally(() => {
                state.loading = false
            })
        }
    })
}
</script>
