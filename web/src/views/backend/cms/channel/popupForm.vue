<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
        :destroy-on-close="true"
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
                        :label="t('cms.channel.pid')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.pid"
                        prop="pid"
                        :input-attr="{
                            params: { isTree: true },
                            pk: 'channel.id',
                            field: 'name',
                            remoteUrl: '/admin/cms.Channel/index',
                            emptyValues: ['', null, undefined, 0],
                            valueOnClear: 0,
                        }"
                        :placeholder="t('Please select field', { field: t('cms.channel.pid') })"
                    />
                    <FormItem
                        :label="t('cms.channel.type')"
                        type="radio"
                        v-model="baTable.form.items!.type"
                        prop="type"
                        :data="{
                            content: { cover: t('cms.channel.type cover'), list: t('cms.channel.type list') },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.channel.type') })"
                        :attr="{
                            blockHelp: t('cms.channel.The display effect of channel home page of cover channel and list channel is different'),
                        }"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type != 'link'"
                        :label="t('cms.channel.content_model_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.content_model_id"
                        prop="content_model_id"
                        :input-attr="{
                            pk: 'content_model.id',
                            field: 'name',
                            remoteUrl: '/admin/cms.ContentModel/index',
                        }"
                        :placeholder="t('Please select field', { field: t('cms.channel.content_model_id') })"
                        :attr="{
                            blockHelp: '向以上模型添加内容时，可以选择本频道',
                        }"
                    />
                    <FormItem
                        :label="t('cms.channel.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('cms.channel.name') })"
                    />
                    <FormItem
                        :label="t('cms.channel.template')"
                        type="string"
                        v-model="baTable.form.items!.template"
                        prop="template"
                        placeholder="封面频道首页模板"
                        :attr="{
                            blockHelp:
                                '请将模板放入 /web-nuxt/composables/template/cms/channel 文件夹，仅填写模板文件名即可，默认可用:bigPicList,doubleColumnList,news,products',
                        }"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type == 'link'"
                        :label="t('cms.channel.url')"
                        type="string"
                        v-model="baTable.form.items!.url"
                        prop="url"
                        :placeholder="t('Please input field', { field: t('cms.channel.url') })"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type == 'link'"
                        :label="t('cms.channel.target')"
                        type="radio"
                        v-model="baTable.form.items!.target"
                        prop="target"
                        :data="{
                            content: {
                                _blank: t('cms.channel.target _blank'),
                                _self: t('cms.channel.target _self'),
                                _top: t('cms.channel.target _top'),
                                _parent: t('cms.channel.target _parent'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('cms.channel.target') })"
                    />
                    <FormItem
                        :label="t('cms.channel.seotitle')"
                        type="string"
                        v-model="baTable.form.items!.seotitle"
                        prop="seotitle"
                        :placeholder="t('cms.channel.seo placeholder')"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type != 'link'"
                        :label="t('cms.channel.keywords')"
                        type="textarea"
                        v-model="baTable.form.items!.keywords"
                        prop="keywords"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.channel.keywords') })"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type != 'link'"
                        :label="t('cms.channel.description')"
                        type="textarea"
                        v-model="baTable.form.items!.description"
                        prop="description"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('cms.channel.description') })"
                    />
                    <FormItem
                        :label="t('cms.channel.index_rec')"
                        type="string"
                        prop="index_rec"
                        v-model.number="baTable.form.items!.index_rec"
                        placeholder="不填写则不推荐，比如：推荐资讯"
                    />
                    <FormItem
                        :label="t('cms.channel.weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('cms.channel.weigh') })"
                    />
                    <FormItem
                        :label="t('cms.channel.allow_visit_groups')"
                        type="radio"
                        v-model="baTable.form.items!.allow_visit_groups"
                        prop="allow_visit_groups"
                        :data="{ content: { all: t('cms.channel.allow_visit_groups all'), user: t('cms.channel.allow_visit_groups user') } }"
                        :placeholder="t('Please select field', { field: t('cms.channel.allow_visit_groups') })"
                    />
                    <FormItem
                        v-if="baTable.form.items!.type != 'link'"
                        :label="t('cms.channel.frontend_contribute')"
                        type="switch"
                        v-model="baTable.form.items!.frontend_contribute"
                        prop="frontend_contribute"
                        :data="{ content: { 0: t('cms.channel.frontend_contribute 0'), 1: t('cms.channel.frontend_contribute 1') } }"
                    />
                    <FormItem
                        :label="t('cms.channel.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { 0: t('cms.channel.status 0'), 1: t('cms.channel.status 1') } }"
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
    pid: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val) {
                    return callback()
                }
                if (parseInt(val) == parseInt(baTable.form.items!.id)) {
                    return callback(new Error(t('auth.menu.The superior menu rule cannot be the rule itself')))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
    content_model_id: [buildValidatorData({ name: 'required', title: t('cms.channel.content_model_id') })],
    name: [buildValidatorData({ name: 'required', title: t('cms.channel.name') })],
    update_time: [buildValidatorData({ name: 'date', title: t('cms.channel.update_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('cms.channel.create_time') })],
})
</script>

<style scoped lang="scss"></style>
