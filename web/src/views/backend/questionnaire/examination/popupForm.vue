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
                        :label="t('questionnaire.examination.title')"
                        type="string"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :input-attr="{
                            clearable: true,
                        }"
                        :placeholder="t('Please input field', { field: t('questionnaire.examination.title') })"
                    />
                    <el-form-item :label="t('questionnaire.examination.date')" prop="date">
                        <el-date-picker
                            v-model="baTable.form.items!.date"
                            type="datetimerange"
                            range-separator="~"
                            :start-placeholder="t('questionnaire.examination.begin_time')"
                            :end-placeholder="t('questionnaire.examination.end_time')"
                            :default-time="defaultTime"
                            :disabled-date="disabledDate"
                            format="YYYY-MM-DD HH:mm:ss"
                            value-format="YYYY-MM-DD HH:mm:ss"
                        />
                    </el-form-item>

                    <FormItem
                        :label="t('questionnaire.examination.questions')"
                        type="remoteSelects"
                        v-model="baTable.form.items!.questions"
                        prop="questions"
                        :input-attr="{
                            pk: 'Question.id',
                            field: 'title',
                            'remote-url': '/admin/questionnaire.Question/getQuestions',
                        }"
                        :placeholder="t('Please select field', { field: t('questionnaire.examination.questions') })"
                    />
                    <FormItem
                        :label="t('questionnaire.examination.description')"
                        type="textarea"
                        v-model="baTable.form.items!.description"
                        prop="description"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('questionnaire.examination.description') })"
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

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

//处理时间
const now = new Date()
const defaultTime: [Date, Date] = [new Date(now), new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)]

//限制过去天不可
const disabledDate = (v: any) => {
    return v.getTime() < Date.now() - 8.64e7
}

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    title: [buildValidatorData({ name: 'required', title: t('questionnaire.examination.title') })],
    date: [buildValidatorData({ name: 'required', message: t('Please select field') + t('questionnaire.examination.date') })],
    questions: [buildValidatorData({ name: 'required', message: t('Please select field') + t('questionnaire.examination.questions') })],
})
</script>

<style scoped lang="scss"></style>
