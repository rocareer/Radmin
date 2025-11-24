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
                    :model="{ ...baTable.form.items, ...baTable.table.extend!.field }"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.cms_content_model__name')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.content_model_id"
                        prop="content_model_id"
                        :input-attr="{
                            pk: 'content_model.id',
                            field: 'name',
                            remoteUrl: '/admin/cms.ContentModel/index',
                            disabled: baTable.form.operate == 'Add' ? false : true,
                            placeholder: t('Please select field', { field: t('cms.contentModelFieldConfig.cms_content_model__name') }),
                        }"
                        :attr="{
                            blockHelp: '可对模型对应数据表的字段进行增删改查，模型不可修改，修改操作请通过删除、新增字段实现',
                        }"
                    />
                    <CreateFormItemData
                        :data-title="__('Field')"
                        v-model="baTable.table.extend!.field"
                        :options="{
                            name: {
                                inputAttr: {
                                    disabled: baTable.form.items!.main_field,
                                },
                            },
                            title: {
                                inputAttr: {
                                    disabled: baTable.form.items!.main_field,
                                },
                            },
                            type: {
                                show: !baTable.form.items!.main_field,
                            },
                            tip: {
                                show: !baTable.form.items!.main_field,
                            },
                            rule: {
                                show: !baTable.form.items!.main_field,
                            },
                            extend: {
                                show: !baTable.form.items!.main_field,
                            },
                            inputExtend: {
                                show: !baTable.form.items!.main_field,
                            },
                        }"
                    />
                    <FormItem
                        v-if="!baTable.form.items!.main_field"
                        :label="__('Field') + __('data type')"
                        type="string"
                        v-model="baTable.form.items!.data_type"
                        prop="data_type"
                        :attr="{
                            blockHelp: '创建到模型对应数据表的字段数据类型',
                        }"
                        :placeholder="t('Please input field', { field: __('Field') + __('data type') })"
                    />
                    <FormItem
                        v-if="!baTable.form.items!.main_field"
                        :label="__('Field') + __('default value')"
                        type="string"
                        v-model="baTable.form.items!.default_value"
                        prop="default_value"
                        :attr="{
                            blockHelp: '字段默认值',
                        }"
                        placeholder="可以直接输入null、0、empty string"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.frontend_filter')"
                        type="switch"
                        v-model="baTable.form.items!.frontend_filter"
                        prop="frontend_filter"
                        :data="{
                            content: { 0: t('cms.contentModelFieldConfig.frontend_filter 0'), 1: t('cms.contentModelFieldConfig.frontend_filter 1') },
                        }"
                        :attr="{
                            blockHelp: '将此字段作为前台列表筛选字段',
                        }"
                    />
                    <FormItem
                        v-if="parseInt(baTable.form.items!.frontend_filter) == 1"
                        :label="t('cms.contentModelFieldConfig.frontend_filter_dict')"
                        type="textarea"
                        v-model="baTable.form.items!.frontend_filter_dict"
                        prop="frontend_filter_dict"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        placeholder="一行一个，无需引号，比如：key=value"
                        :attr="{
                            blockHelp: '当此字段作为前台列表筛选字段时的字典数据，不输入则使用字段本身的字典数据',
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.frontend_contribute')"
                        type="switch"
                        v-model="baTable.form.items!.frontend_contribute"
                        prop="frontend_contribute"
                        :data="{
                            content: {
                                0: t('cms.contentModelFieldConfig.frontend_contribute 0'),
                                1: t('cms.contentModelFieldConfig.frontend_contribute 1'),
                            },
                        }"
                        :attr="{
                            blockHelp: '将此字段作为前台会员投稿时的表单字段之一',
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.backend_publish')"
                        type="switch"
                        v-model="baTable.form.items!.backend_publish"
                        prop="backend_publish"
                        :data="{
                            content: { 0: t('cms.contentModelFieldConfig.backend_publish 0'), 1: t('cms.contentModelFieldConfig.backend_publish 1') },
                        }"
                        :attr="{
                            blockHelp: '将此字段作为后台表格添加内容时的表单字段之一',
                        }"
                        :input-attr="{
                            disabled: baTable.form.operate == 'Edit' && notSupportPublish.includes(baTable.table.extend!.field.name),
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.backend_show')"
                        type="switch"
                        v-model="baTable.form.items!.backend_show"
                        prop="backend_show"
                        :data="{
                            content: {
                                0: t('cms.contentModelFieldConfig.backend_show 0'),
                                1: t('cms.contentModelFieldConfig.backend_show 1'),
                            },
                        }"
                        :attr="{
                            blockHelp: '将此字段作为后台表格显示的列字段之一',
                        }"
                        :input-attr="{
                            disabled: baTable.form.operate == 'Edit' && notSupportShow.includes(baTable.table.extend!.field.name),
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.backend_com_search')"
                        type="switch"
                        v-model="baTable.form.items!.backend_com_search"
                        prop="backend_com_search"
                        :data="{
                            content: {
                                0: t('cms.contentModelFieldConfig.backend_com_search 0'),
                                1: t('cms.contentModelFieldConfig.backend_com_search 1'),
                            },
                        }"
                        :attr="{
                            blockHelp: '将此字段作为后台表格的公共搜索字段之一',
                        }"
                        :input-attr="{
                            disabled: baTable.form.operate == 'Edit' && notSupportComSearch.includes(baTable.table.extend!.field.name),
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.backend_sort')"
                        type="switch"
                        v-model="baTable.form.items!.backend_sort"
                        prop="backend_sort"
                        :data="{
                            content: { 0: t('cms.contentModelFieldConfig.backend_sort 0'), 1: t('cms.contentModelFieldConfig.backend_sort 1') },
                        }"
                        :attr="{
                            blockHelp: '将此字段作为后台表格的排序字段之一',
                        }"
                        :input-attr="{
                            disabled: baTable.form.operate == 'Edit' && notSupportSortable.includes(baTable.table.extend!.field.name),
                        }"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.backend_column_attr')"
                        type="textarea"
                        v-model="baTable.form.items!.backend_column_attr"
                        prop="backend_column_attr"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        placeholder="由开发者定义，可使用表格列的所有属性，此处定义的属性权重最高"
                        :attr="{
                            blockHelp: '后台表格列属性配置，一行一个，无需引号，比如 show=false、remote.pk=test/index',
                        }"
                    />
                    <FormItem
                        :label="t('Weigh')"
                        type="number"
                        prop="weigh"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.weigh"
                        :placeholder="t('Please input field', { field: t('Weigh') })"
                    />
                    <FormItem
                        :label="t('cms.contentModelFieldConfig.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :data="{ content: { 0: t('cms.contentModelFieldConfig.status 0'), 1: t('cms.contentModelFieldConfig.status 1') } }"
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
import { reactive, ref, inject, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElForm, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
import CreateFormItemData from '/@/components/formItem/createData.vue'
import { __ } from '/@/utils/common'
import { fieldData } from '/@/components/baInput/helper'
import { notSupportComSearch, notSupportSortable, notSupportShow, notSupportPublish } from '/@/views/backend/cms/content/helper'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

// 表单字段信息初始化
baTable.table.extend!.field = {
    dict: `key1=value1
key2=value2`,
}

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    content_model_id: [buildValidatorData({ name: 'required', title: __('cms_content_model__name') })],
    name: [
        buildValidatorData({ name: 'required', title: __('Field') + __('name') }),
        buildValidatorData({ name: 'varName', message: __('Please enter the correct field', { field: __('Field') + __('name') }) }),
    ],
    title: [buildValidatorData({ name: 'required', title: __('Field') + __('title') })],
    type: [
        buildValidatorData({
            name: 'required',
            trigger: 'change',
            message: t('Please select field', { field: __('Field') + __('type') }),
        }),
    ],
    data_type: [buildValidatorData({ name: 'required', title: __('Field') + __('data type') })],
})

watch(
    () => baTable.table.extend!.field,
    (field) => {
        if (field.type) {
            if (
                baTable.table.extend!.dataEbak &&
                field.type == baTable.table.extend!.dataEbak.type &&
                field.dict == baTable.table.extend!.dataEbak.dict
            ) {
                baTable.form.items!.data_type = baTable.table.extend!.dataEbak.data_type
                return
            }
            for (const key in fieldData) {
                if (key == field.type) {
                    if (fieldData[key].length > 0) {
                        baTable.form.items!.data_type = `${fieldData[key].type}(${fieldData[key].length})`
                    } else if (['enum', 'set'].includes(fieldData[key].type)) {
                        let dict: string[] = field.dict ? field.dict.split(/[(\r\n)\r\n]+/) : []
                        dict = dict
                            .map((value) => {
                                if (!value) return ''
                                let temp = value.split('=')
                                if (temp[0] && temp[1]) {
                                    return `'${temp[0]}'`
                                }
                                return ''
                            })
                            .filter((str: string) => str != '')
                        baTable.form.items!.data_type = `${fieldData[key].type}(${dict.join(',')})`
                    } else if (fieldData[key].null && fieldData[key].default == 'null') {
                        baTable.form.items!.data_type = `${fieldData[key].type}`
                    } else {
                        baTable.form.items!.data_type = `varchar(255)`
                    }
                    break
                }
            }
        }
    }
)
</script>

<style scoped lang="scss"></style>
