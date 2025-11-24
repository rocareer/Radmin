<template>
    <div>
        <el-form label-position="top" ref="formRef" :rules="rules" :model="mini" label-width="120px">
            <FormItem
                type="string"
                label="APPID"
                v-model="mini!.appid"
                prop="appid"
                :placeholder="t('Please input field', { field: t('questionnaire.config.appid') })"
                :input-attr="{
                    clearable: true,
                }"
            />
            <FormItem
                type="string"
                label="SECRET"
                v-model="mini!.secret"
                prop="secret"
                :placeholder="t('Please input field', { field: t('questionnaire.config.secret') })"
                :input-attr="{
                    clearable: true,
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
    mini: any
}

const props = withDefaults(defineProps<Props>(), {
    mini: {},
})

const formRef = ref<FormInstance>()
const state = reactive({
    loading: false,
})

// 此处直接使用 buildValidatorData 生成了表单验证规则
const rules = reactive<FormRules>({
    appid: [buildValidatorData({ name: 'required', title: t('questionnaire.config.appid') })],
    secret: [buildValidatorData({ name: 'required', title: t('questionnaire.config.secret') })],
})

const onSubmit = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.validate((valid) => {
        if (valid) {
            // 验证通过
            state.loading = true
            saveConfig('questionnaire_mini', props.mini).finally(() => {
                state.loading = false
            })
        }
    })
}
</script>
