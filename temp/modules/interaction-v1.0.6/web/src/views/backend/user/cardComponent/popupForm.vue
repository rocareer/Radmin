<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
        top="8vh"
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
                    <el-form-item>
                        <el-alert :title="t('Reminder')" type="success">
                            <template #default>
                                <span class="card-tips">1. {{ t('user.cardComponent.card tips 1') }}</span>
                                <span class="card-tips">2. {{ t('user.cardComponent.card tips 2') }}</span>
                                <span class="card-tips">3. {{ t('user.cardComponent.card tips 3') }}</span>
                            </template>
                        </el-alert>
                    </el-form-item>
                    <FormItem
                        :label="t('user.cardComponent.title')"
                        type="string"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :placeholder="t('Please input field', { field: t('user.cardComponent.title') })"
                    />
                    <FormItem
                        :label="t('user.cardComponent.position')"
                        type="radio"
                        v-model="baTable.form.items!.position"
                        prop="position"
                        :data="{
                            content: {
                                statistic: t('user.cardComponent.position statistic'),
                                under_motto: t('user.cardComponent.position under_motto'),
                                opt: t('user.cardComponent.position opt'),
                                middle: t('user.cardComponent.position middle'),
                                tab: t('user.cardComponent.position tab'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('user.cardComponent.position') })"
                        :attr="{
                            blockHelp: t('user.cardComponent.' + baTable.form.items!.position + ' tips')
                        }"
                    />
                    <FormItem
                        :label="t('user.cardComponent.component')"
                        type="string"
                        v-model="baTable.form.items!.component"
                        prop="component"
                        :placeholder="t('Please input field', { field: t('user.cardComponent.component') })"
                        :attr="{
                            blockHelp: t('user.cardComponent.component tips'),
                        }"
                    />
                    <FormItem
                        :label="t('user.cardComponent.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('user.cardComponent.weigh') })"
                    />
                    <FormItem
                        :label="t('State')"
                        v-model="baTable.form.items!.status"
                        type="radio"
                        :data="{ content: { '0': t('Disable'), '1': t('Enable') }, childrenAttr: { border: true } }"
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
    title: [buildValidatorData({ name: 'required', title: t('user.cardComponent.title') })],
    component: [buildValidatorData({ name: 'required', title: t('user.cardComponent.component') })],
})
</script>

<style scoped lang="scss">
.card-tips {
    display: block;
    line-height: 20px;
}
</style>
