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
                        :label="t('questionnaire.question.title')"
                        type="string"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :input-attr="{
                            clearable: true,
                        }"
                        :placeholder="t('Please input field', { field: t('questionnaire.question.title') })"
                    />
                    <FormItem
                        :label="t('questionnaire.question.must')"
                        type="radio"
                        v-model="baTable.form.items!.must"
                        prop="must"
                        :data="{
                            childrenAttr: { border: true },
                            content: {
                                '0': t('questionnaire.question.must 0'),
                                '1': t('questionnaire.question.must 1'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('questionnaire.question.must') })"
                    />
                    <FormItem
                        :label="t('questionnaire.question.type')"
                        type="select"
                        v-model="baTable.form.items!.type"
                        prop="type"
                        :data="{
                            content: {
                                '0': t('questionnaire.question.type 0'),
                                '1': t('questionnaire.question.type 1'),
                                '2': t('questionnaire.question.type 2'),
                                '3': t('questionnaire.question.type 3'),
                                '4': t('questionnaire.question.type 4'),
                                '5': t('questionnaire.question.type 5'),
                                '6': t('questionnaire.question.type 6'),
                                '7': t('questionnaire.question.type 7'),
                            },
                        }"
                        :input-attr="{
                            onChange: onChangeType,
                        }"
                        :placeholder="t('Please select field', { field: t('questionnaire.question.type') })"
                    />

                    <!-- 单选/多选/下拉框 -->
                    <template v-if="[0, 1, 4].includes(Number(baTable.form.items!.type))">
                        <el-form-item :label="t('questionnaire.question.options')" prop="options">
                            <el-row
                                :gutter="20"
                                style="width: 100%; margin-bottom: 8px"
                                v-for="(item, index) in baTable.form.items!.options"
                                :key="index"
                            >
                                <el-col :span="12">
                                    <el-input
                                        v-model="baTable.form.items!.options[index]"
                                        clearable
                                        :placeholder="t('Please input field', { field: t('questionnaire.question.options') })"
                                    />
                                </el-col>
                                <el-col :span="12">
                                    <el-button :icon="Delete" circle @click="delOption(index)" />
                                </el-col>
                            </el-row>
                            <el-button type="primary" size="small" @click="addOption">{{ t('questionnaire.question.add_options') }}</el-button>
                        </el-form-item>
                    </template>

                    <!-- 图片/视频/文件 -->
                    <template v-if="[5, 6, 7].includes(Number(baTable.form.items!.type))">
                        <el-form-item :label="t('questionnaire.question.file_num')" prop="file_num">
                            <el-input-number v-model="baTable.form.items!.file_num" :min="1" :max="baTable.form.extend!.num" :step="1" />
                        </el-form-item>
                        <el-form-item :label="t('questionnaire.question.file_size')" prop="file_size">
                            <el-input-number v-model="baTable.form.items!.file_size" :min="1" :max="baTable.form.extend!.size" :step="1">
                                <template #suffix>
                                    <span>M</span>
                                </template>
                            </el-input-number>
                        </el-form-item>
                        <el-form-item :label="t('questionnaire.question.file_suffix')" prop="file_suffix">
                            <el-checkbox-group v-model="baTable.form.items!.file_suffix">
                                <div v-show="baTable.form.items!.type == 5">
                                    <el-checkbox v-for="(ite, inde) in baTable.form.extend!.picture" :key="inde" :value="ite">{{ ite }}</el-checkbox>
                                </div>
                                <div v-show="baTable.form.items!.type == 6">
                                    <el-checkbox v-for="(ite, inde) in baTable.form.extend!.video" :key="inde" :value="ite">{{ ite }}</el-checkbox>
                                </div>
                                <div v-show="baTable.form.items!.type == 7">
                                    <el-checkbox v-for="(ite, inde) in baTable.form.extend!.file" :key="inde" :value="ite">{{ ite }}</el-checkbox>
                                </div>
                            </el-checkbox-group>
                        </el-form-item>
                    </template>

                    <FormItem
                        :label="t('questionnaire.question.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { '0': t('questionnaire.question.status 0'), '1': t('questionnaire.question.status 1') } }"
                    />
                    <FormItem
                        :label="t('questionnaire.question.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('questionnaire.question.weigh') })"
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
import { Delete } from '@element-plus/icons-vue'

const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    title: [buildValidatorData({ name: 'required', title: t('questionnaire.question.title') })],
    must: [buildValidatorData({ name: 'required', title: t('questionnaire.question.must') })],
    type: [buildValidatorData({ name: 'required', title: t('questionnaire.question.type') })],
    file_num: [buildValidatorData({ name: 'required', title: t('questionnaire.question.file_num') })],
    file_size: [buildValidatorData({ name: 'required', title: t('questionnaire.question.file_size') })],
    file_suffix: [buildValidatorData({ name: 'required', message: t('questionnaire.question.file_suffix_msg') })],

    options: [
        buildValidatorData({ name: 'required', message: t('questionnaire.question.add_options') }),
        {
            validator: (rule: any, val: number, callback: Function) => {
                checkOptions(rule, val, callback)
            },
            trigger: 'blur',
        },
    ],
    status: [buildValidatorData({ name: 'required', title: t('questionnaire.question.status') })],
})

//更改类型
const onChangeType = (type: number) => {
    if (baTable.form.extend!.operate == 'Add') {
        if (type == 5) {
            baTable.form.items!.file_suffix = baTable.form.extend!.picture
        }
        if (type == 6) {
            baTable.form.items!.file_suffix = baTable.form.extend!.video
        }
        if (type == 7) {
            baTable.form.items!.file_suffix = baTable.form.extend!.file
        }
    }
}

//添加选项
const addOption = () => {
    if (!baTable.form.items!.options) {
        baTable.form.items!.options = []
    }
    baTable.form.items!.options.push([])
}
//删除选项
const delOption = (i: number) => {
    baTable.form.items!.options.splice(i, 1)
}
//验证选项
const checkOptions = (rule: any, value: any, callback: any) => {
    let list = baTable.form.items!.options

    for (let i = 0; i < list.length; i++) {
        if (list[i].length === 0) {
            return callback(new Error(t('questionnaire.question.perfect_options')))
        }
    }
    return callback()
}
</script>

<style scoped lang="scss"></style>
