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
                        :label="t('cms.block.platform')"
                        type="radio"
                        v-model="baTable.form.items!.platform"
                        prop="platform"
                        :data="{
                            content: {
                                nuxt: 'Nuxt',
                                uniapp: 'uni-app',
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.block.platform') })"
                        :input-attr="{
                            onChange: onPlatform,
                        }"
                    />
                    <FormItem
                        :label="t('cms.block.type')"
                        type="radio"
                        v-model="baTable.form.items!.type"
                        :key="baTable.form.items!.platform + '-type'"
                        prop="type"
                        :data="{
                            content:
                                baTable.form.items!.platform == 'nuxt'
                                    ? {
                                          carousel: t('cms.block.type carousel'),
                                          image: t('cms.block.type image'),
                                          rich_text: t('cms.block.type rich_text'),
                                      }
                                    : { carousel: t('cms.block.type carousel'), image: t('cms.block.type image') },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.block.type') })"
                    />
                    <FormItem
                        :label="t('cms.block.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('cms.block.name') })"
                        :attr="{
                            blockHelp: '开发者通过此名称调用区块数据，可通过重复的名称设置多张图片',
                        }"
                    />
                    <FormItem
                        :label="t('cms.block.title')"
                        type="string"
                        v-model="baTable.form.items!.title"
                        prop="title"
                        :placeholder="t('Please input field', { field: t('cms.block.title') })"
                    />
                    <FormItem
                        :label="t('cms.block.link')"
                        type="string"
                        v-model="baTable.form.items!.link"
                        prop="link"
                        :placeholder="t('Please input field', { field: t('cms.block.link') })"
                    />
                    <FormItem
                        :label="t('cms.block.target')"
                        type="radio"
                        v-model="baTable.form.items!.target"
                        prop="target"
                        :key="baTable.form.items!.platform + '-target'"
                        :data="{
                            content:
                                baTable.form.items!.platform == 'nuxt'
                                    ? {
                                          _blank: t('cms.block.target _blank'),
                                          _self: t('cms.block.target _self'),
                                          _top: t('cms.block.target _top'),
                                          _parent: t('cms.block.target _parent'),
                                      }
                                    : {
                                          navigateTo: '保留当前页面跳转',
                                          redirectTo: '关闭当前页面跳转',
                                          reLaunch: '关闭所有页面跳转',
                                          switchTab: '关闭所有 tabBar 页面跳转',
                                      },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.block.target') })"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type == 'image' || baTable.form.items!.type == 'carousel'"
                        :label="t('cms.block.image')"
                        type="image"
                        v-model="baTable.form.items!.image"
                        prop="image"
                        :attr="{
                            blockHelp: '单图上传，若需多图请添加相同名称的区块',
                        }"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type == 'rich_text'"
                        :label="t('cms.block.rich_text')"
                        type="editor"
                        v-model="baTable.form.items!.rich_text"
                        prop="rich_text"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.block.rich_text') })"
                    />
                    <FormItem
                        :label="t('cms.block.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('cms.block.weigh') })"
                    />
                    <FormItem
                        :label="t('cms.block.start_time')"
                        type="datetime"
                        v-model="baTable.form.items!.start_time"
                        prop="start_time"
                        :placeholder="t('Please select field', { field: t('cms.block.start_time') })"
                    />
                    <FormItem
                        :label="t('cms.block.end_time')"
                        type="datetime"
                        v-model="baTable.form.items!.end_time"
                        prop="end_time"
                        :placeholder="t('Please select field', { field: t('cms.block.end_time') })"
                    />
                    <FormItem
                        :label="t('cms.block.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { 0: t('cms.block.status 0'), 1: t('cms.block.status 1') } }"
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

const onPlatform = () => {
    if (baTable.form.items!.platform == 'nuxt') {
        baTable.form.items!.target = '_blank'
    } else {
        baTable.form.items!.target = 'navigateTo'
    }
}

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('cms.block.name') })],
    title: [buildValidatorData({ name: 'required', title: t('cms.block.title') })],
    image: [buildValidatorData({ name: 'required', message: t('Please select field', { field: t('cms.block.image') }) })],
    start_time: [buildValidatorData({ name: 'date', title: t('cms.block.start_time') })],
    end_time: [buildValidatorData({ name: 'date', title: t('cms.block.end_time') })],
    rich_text: [buildValidatorData({ name: 'editorRequired', title: t('cms.block.rich_text') })],
})
</script>

<style scoped lang="scss"></style>
