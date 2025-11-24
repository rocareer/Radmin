<template>
    <div>
        <el-form label-position="top" ref="formRef" :rules="rules" :model="h5" label-width="120px">
            <FormItem
                type="string"
                :label="t('questionnaire.config.domain')"
                v-model="h5!.domain"
                prop="domain"
                :placeholder="t('Please input field', { field: t('questionnaire.config.domain') })"
                :input-attr="{
                    clearable: true,
                }"
                :attr="{
                    blockHelp: t('questionnaire.config.domain_tip'),
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
    h5: any
}

const props = withDefaults(defineProps<Props>(), {
    h5: {},
})

const formRef = ref<FormInstance>()
const state = reactive({
    loading: false,
})

// 此处直接使用 buildValidatorData 生成了表单验证规则
const rules = reactive<FormRules>({
    domain: [
        buildValidatorData({ name: 'required', title: t('questionnaire.config.domain') }),
        buildValidatorData({ name: 'url', title: t('questionnaire.config.domain') }),
    ],
})

const onSubmit = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            // 验证通过
            state.loading = true
            saveConfig('questionnaire_h5', props.h5).finally(() => {
                state.loading = false
            })
        }
    })
}
</script>
