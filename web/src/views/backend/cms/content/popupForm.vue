<template>
    <!-- 对话框表单 -->
    <div>
        <el-dialog
            class="ba-operate-dialog"
            :close-on-click-modal="false"
            :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
            @close="baTable.toggleForm"
            width="70%"
            top="10vh"
            :destroy-on-close="true"
        >
            <template #header>
                <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                    {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
                </div>
            </template>
            <template #default>
                <div class="content-form" v-loading="baTable.form.loading" element-loading-custom-class="loading-custom">
                    <el-form
                        v-if="!baTable.form.loading"
                        ref="formRef"
                        @submit.prevent=""
                        @keyup.enter="baTable.onSubmit(formRef)"
                        :model="baTable.form.items"
                        label-position="right"
                        :label-width="baTable.form.labelWidth + 'px'"
                        :rules="baTable.form.extend!.rules"
                        class="cms-content-form"
                    >
                        <el-scrollbar class="ba-table-form-scrollbar" :style="{ width: state.disTeleport ? '60%' : '100%' }">
                            <div class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'">
                                <FormItem
                                    v-if="baTable.form.extend!.modelInfo.fields['channel_id']?.backend_publish"
                                    :label="t('cms.content.channel_id')"
                                    type="remoteSelect"
                                    v-model="baTable.form.items!.channel_id"
                                    prop="channel_id"
                                    :input-attr="{
                                        pk: 'channel.id',
                                        field: 'name',
                                        remoteUrl: '/admin/cms.Channel/index',
                                        params: {
                                            search: [
                                                {
                                                    field: 'content_model_id',
                                                    val: baTable.form.extend!.modelInfo.modelInfo.id,
                                                    operator: '=',
                                                },
                                            ],
                                        },
                                        emptyValues: ['', null, undefined, 0],
                                        valueOnClear: 0,
                                    }"
                                    :placeholder="t('Please select field', { field: t('cms.content.channel_id') })"
                                />
                                <FormItem
                                    v-if="baTable.form.extend!.modelInfo.fields['channel_ids']?.backend_publish"
                                    :label="t('cms.content.channel_ids')"
                                    type="remoteSelect"
                                    v-model="baTable.form.items!.channel_ids"
                                    prop="channel_ids"
                                    :input-attr="{
                                        pk: 'channel.id',
                                        field: 'name',
                                        remoteUrl: '/admin/cms.Channel/index',
                                        multiple: true,
                                    }"
                                    :placeholder="t('Please select field', { field: t('cms.content.channel_ids') })"
                                />
                                <el-form-item
                                    v-if="baTable.form.extend!.modelInfo.fields['title']?.backend_publish"
                                    :label="t('cms.content.title')"
                                    prop="title"
                                >
                                    <div class="title-box">
                                        <el-input
                                            class="title"
                                            type="string"
                                            v-model="baTable.form.items!.title"
                                            :placeholder="t('Please input field', { field: t('cms.content.title') })"
                                        ></el-input>
                                        <div v-if="baTable.form.extend!.modelInfo.fields['title_style']?.backend_publish">
                                            <el-color-picker v-model="baTable.form.items!.title_style.color" />
                                            <el-checkbox
                                                size="large"
                                                class="title-bold"
                                                v-model="baTable.form.items!.title_style.bold"
                                                label="加粗"
                                            />
                                        </div>
                                    </div>
                                </el-form-item>
                                <FormItem
                                    v-if="baTable.form.extend!.modelInfo.fields['tags']?.backend_publish"
                                    :label="t('cms.content.tags')"
                                    type="remoteSelect"
                                    v-model="baTable.form.items!.tags"
                                    prop="tags"
                                    @keyup.enter.stop=""
                                    :input-attr="{
                                        pk: 'tags.id',
                                        field: 'name',
                                        remoteUrl: '/admin/cms.Tags/index',
                                        multiple: true,
                                        allowCreate: true,
                                        defaultFirstOption: true,
                                    }"
                                    :attr="{
                                        blockHelp: '输入标签名并按下回车可以自动新建标签（保存后自动新建）',
                                    }"
                                    :placeholder="t('Please select field', { field: t('cms.content.tags') })"
                                />
                                <FormItem
                                    :label="t('cms.content.images')"
                                    v-if="baTable.form.extend!.modelInfo.fields['images']?.backend_publish"
                                    type="images"
                                    v-model="baTable.form.items!.images"
                                    prop="images"
                                />
                                <FormItem
                                    v-if="baTable.form.extend!.modelInfo.fields['content']?.backend_publish"
                                    :label="t('cms.content.content')"
                                    type="editor"
                                    v-model="baTable.form.items!.content"
                                    prop="content"
                                    @keyup.enter.stop=""
                                    @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                    :placeholder="t('Please input field', { field: t('cms.content.content') })"
                                />
                                <div id="cms-mian-form"></div>
                                <div class="model-fields" v-for="(item, idx) in baTable.form.extend!.modelInfo.fields" :key="idx">
                                    <div v-if="!item.main_field && item.backend_publish">
                                        <FormItem
                                            v-if="item.type == 'number'"
                                            :label="item.title"
                                            :type="item.type"
                                            v-model.number="baTable.form.items![item.full_name]"
                                            :attr="{
                                                prop: item.full_name,
                                                blockHelp: item.tip,
                                                ...item.extend,
                                            }"
                                            :input-attr="{ placeholder: item.tip, ...item.input_extend }"
                                            :key="'number-' + item.id"
                                        />
                                        <FormItem
                                            v-else-if="item.type == 'editor'"
                                            :label="item.title"
                                            :type="item.type"
                                            @keyup.enter.stop=""
                                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                            v-model="baTable.form.items![item.full_name]"
                                            :attr="{
                                                prop: item.full_name,
                                                blockHelp: item.tip,
                                                ...item.extend,
                                            }"
                                            :input-attr="{
                                                placeholder: item.tip,
                                                style: {
                                                    zIndex: 99,
                                                },
                                                ...item.input_extend,
                                            }"
                                            :key="'editor-' + item.id"
                                        />
                                        <FormItem
                                            v-else-if="item.type == 'textarea'"
                                            :label="item.title"
                                            :type="item.type"
                                            @keyup.enter.stop=""
                                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                            v-model="baTable.form.items![item.full_name]"
                                            :attr="{
                                                prop: item.full_name,
                                                blockHelp: item.tip,
                                                ...item.extend,
                                            }"
                                            :input-attr="{ placeholder: item.tip, rows: 3, ...item.input_extend }"
                                            :key="'textarea-' + item.id"
                                        />
                                        <FormItem
                                            v-else
                                            :label="item.title"
                                            :type="item.type"
                                            v-model="baTable.form.items![item.full_name]"
                                            :attr="{
                                                prop: item.full_name,
                                                blockHelp: item.tip,
                                                ...item.extend,
                                            }"
                                            :input-attr="{ placeholder: item.tip, ...item.input_extend }"
                                            :data="!isEmpty(item.content) ? { content: item.content } : {}"
                                            :key="'other-' + item.id"
                                        />
                                    </div>
                                </div>
                            </div>
                        </el-scrollbar>
                        <el-scrollbar class="ba-table-form-scrollbar" :style="{ width: state.disTeleport ? '39%' : '0%' }">
                            <div class="ba-operate-form related" :class="'ba-' + baTable.form.operate + '-form'">
                                <Teleport v-if="state.formMainMounted" :disabled="state.disTeleport" to="#cms-mian-form">
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['flag']?.backend_publish"
                                        :label="t('cms.content.flag')"
                                        type="checkbox"
                                        v-model="baTable.form.items!.flag"
                                        prop="flag"
                                        :data="{
                                            content: {
                                                top: t('cms.content.flag top'),
                                                hot: t('cms.content.flag hot'),
                                                recommend: t('cms.content.flag recommend'),
                                                new: t('cms.content.flag new'),
                                            },
                                        }"
                                        :placeholder="t('Please select field', { field: t('cms.content.flag') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['user_id']?.backend_publish"
                                        :label="t('cms.content.user_id')"
                                        type="remoteSelect"
                                        v-model="baTable.form.items!.user_id"
                                        prop="user_id"
                                        :input-attr="{
                                            pk: 'user.id',
                                            field: 'username',
                                            remoteUrl: '/admin/user.User/index',
                                            emptyValues: ['', null, undefined, 0],
                                            valueOnClear: 0,
                                        }"
                                        :placeholder="t('Please select field', { field: t('cms.content.user_id') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['admin_id']?.backend_publish"
                                        :label="t('cms.content.admin_id')"
                                        type="remoteSelect"
                                        v-model="baTable.form.items!.admin_id"
                                        prop="admin_id"
                                        :input-attr="{
                                            pk: 'admin.id',
                                            field: 'username',
                                            remoteUrl: '/admin/auth.Admin/index',
                                            emptyValues: ['', null, undefined, 0],
                                            valueOnClear: 0,
                                        }"
                                        :placeholder="t('Please select field', { field: t('cms.content.admin_id') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['seotitle']?.backend_publish"
                                        :label="t('cms.content.seotitle')"
                                        type="string"
                                        v-model="baTable.form.items!.seotitle"
                                        prop="seotitle"
                                        :placeholder="t('Please input field', { field: t('cms.content.seotitle') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['keywords']?.backend_publish"
                                        :label="t('cms.content.keywords')"
                                        type="textarea"
                                        v-model="baTable.form.items!.keywords"
                                        prop="keywords"
                                        :input-attr="{ rows: 3 }"
                                        @keyup.enter.stop=""
                                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                        :placeholder="t('Please input field', { field: t('cms.content.keywords') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['description']?.backend_publish"
                                        :label="t('cms.content.description')"
                                        type="textarea"
                                        v-model="baTable.form.items!.description"
                                        prop="description"
                                        :input-attr="{ rows: 3 }"
                                        @keyup.enter.stop=""
                                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                        :placeholder="t('Please input field', { field: t('cms.content.description') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['url']?.backend_publish"
                                        :label="t('cms.content.url')"
                                        type="string"
                                        v-model="baTable.form.items!.url"
                                        prop="url"
                                        placeholder="外部链接URL（预留）"
                                    />
                                    <FormItem
                                        v-if="baTable.form.items!.url"
                                        :label="t('cms.content.target')"
                                        type="select"
                                        v-model="baTable.form.items!.target"
                                        prop="target"
                                        :data="{
                                            content: {
                                                _blank: t('cms.content.target _blank'),
                                                _self: t('cms.content.target _self'),
                                                _top: t('cms.content.target _top'),
                                                _parent: t('cms.content.target _parent'),
                                            },
                                        }"
                                        :placeholder="t('Please select field', { field: t('cms.content.target') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['views']?.backend_publish"
                                        :label="t('cms.content.views')"
                                        type="number"
                                        prop="views"
                                        :input-attr="{ step: 1, valueOnClear: 0 }"
                                        v-model.number="baTable.form.items!.views"
                                        :placeholder="t('Please input field', { field: t('cms.content.views') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['comments']?.backend_publish"
                                        :label="t('cms.content.comments')"
                                        type="number"
                                        prop="comments"
                                        :input-attr="{ step: 1, valueOnClear: 0 }"
                                        v-model.number="baTable.form.items!.comments"
                                        :placeholder="t('Please input field', { field: t('cms.content.comments') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['likes']?.backend_publish"
                                        :label="t('cms.content.likes')"
                                        type="number"
                                        prop="likes"
                                        :input-attr="{ step: 1, valueOnClear: 0 }"
                                        v-model.number="baTable.form.items!.likes"
                                        :placeholder="t('Please input field', { field: t('cms.content.likes') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['dislikes']?.backend_publish"
                                        :label="t('cms.content.dislikes')"
                                        type="number"
                                        prop="dislikes"
                                        :input-attr="{ step: 1, valueOnClear: 0 }"
                                        v-model.number="baTable.form.items!.dislikes"
                                        :placeholder="t('Please input field', { field: t('cms.content.dislikes') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['weigh']?.backend_publish"
                                        :label="t('cms.content.weigh')"
                                        type="number"
                                        prop="weigh"
                                        :input-attr="{ step: 1 }"
                                        v-model.number="baTable.form.items!.weigh"
                                        :placeholder="t('Please input field', { field: t('cms.content.weigh') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['price']?.backend_publish"
                                        :label="t('cms.content.price')"
                                        type="number"
                                        prop="price"
                                        :input-attr="{ step: 1, valueOnClear: 0 }"
                                        v-model.number="baTable.form.items!.price"
                                        :placeholder="t('Please input field', { field: t('cms.content.price') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['currency']?.backend_publish"
                                        :label="t('cms.content.currency')"
                                        type="radio"
                                        v-model="baTable.form.items!.currency"
                                        prop="currency"
                                        :data="{ content: { RMB: t('cms.content.currency RMB'), integral: t('cms.content.currency integral') } }"
                                        :placeholder="t('Please select field', { field: t('cms.content.currency') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['allow_visit_groups']?.backend_publish"
                                        :label="t('cms.content.allow_visit_groups')"
                                        type="radio"
                                        v-model="baTable.form.items!.allow_visit_groups"
                                        prop="allow_visit_groups"
                                        :data="{
                                            content: { all: t('cms.content.allow_visit_groups all'), user: t('cms.content.allow_visit_groups user') },
                                        }"
                                        :placeholder="t('Please select field', { field: t('cms.content.allow_visit_groups') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['allow_comment_groups']?.backend_publish"
                                        :label="t('cms.content.allow_comment_groups')"
                                        type="radio"
                                        v-model="baTable.form.items!.allow_comment_groups"
                                        prop="allow_comment_groups"
                                        :data="{
                                            content: {
                                                disable: t('cms.content.allow_comment_groups disable'),
                                                user: t('cms.content.allow_comment_groups user'),
                                            },
                                        }"
                                        :placeholder="t('Please select field', { field: t('cms.content.allow_comment_groups') })"
                                    />
                                    <FormItem
                                        v-if="['refused', 'offline'].includes(baTable.form.items!.status)"
                                        :label="t('cms.content.memo')"
                                        type="textarea"
                                        v-model="baTable.form.items!.memo"
                                        prop="memo"
                                        :input-attr="{ rows: 3 }"
                                        @keyup.enter.stop=""
                                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                        placeholder="稿件审核失败时，请在此输入拒绝原因"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['status']?.backend_publish"
                                        :label="t('cms.content.status')"
                                        type="radio"
                                        v-model="baTable.form.items!.status"
                                        prop="status"
                                        :data="{
                                            content: {
                                                normal: t('cms.content.status normal'),
                                                unaudited: t('cms.content.status unaudited'),
                                                refused: t('cms.content.status refused'),
                                                offline: t('cms.content.status offline'),
                                            },
                                        }"
                                        :placeholder="t('Please select field', { field: t('cms.content.status') })"
                                    />
                                    <FormItem
                                        v-if="baTable.form.extend!.modelInfo.fields['publish_time']?.backend_publish"
                                        :label="t('cms.content.publish_time')"
                                        type="datetime"
                                        v-model="baTable.form.items!.publish_time"
                                        prop="publish_time"
                                        :placeholder="t('Please select field', { field: t('cms.content.publish_time') })"
                                    />
                                </Teleport>
                            </div>
                        </el-scrollbar>
                    </el-form>
                </div>
            </template>
            <template #footer>
                <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                    <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                    <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                        {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref, inject, watch, nextTick, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElForm } from 'element-plus'
import { useEventListener } from '@vueuse/core'
import { isEmpty } from 'lodash-es'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const state = reactive({
    formMainMounted: false,
    disTeleport: true,
})

const resize = () => {
    state.disTeleport = document.body.clientWidth < 740 ? false : true
}

/**
 * 主表单已经渲染完毕
 */
watch(
    () => baTable.form.loading,
    (newValue) => {
        nextTick(() => {
            state.formMainMounted = !newValue
        })
    }
)

watch(
    () => baTable.form.operate,
    (newValue) => {
        if (!newValue) state.formMainMounted = false
        nextTick(() => {
            if (newValue == 'Add') state.formMainMounted = true
        })
    }
)

onMounted(() => {
    resize()
    useEventListener(window, 'resize', resize)
})
</script>

<style scoped lang="scss">
:deep(.loading-custom) {
    margin-top: 40px;
}
.content-form {
    position: relative;
    height: 100%;
}
:deep(.ba-operate-dialog) .el-dialog__body {
    position: relative;
    height: 70vh;
    .cms-content-form {
        display: flex;
        justify-content: center;
        height: 100%;
    }
    .ba-operate-form {
        padding-right: 15px;
    }
    .title-box {
        display: flex;
        align-items: center;
        width: 100%;
        .title {
            flex: 1;
            margin-right: 10px;
        }
        .title-bold {
            margin-left: 10px;
        }
    }
}
@media screen and (max-width: 1024px) {
    :deep(.ba-operate-dialog) {
        --el-dialog-width: 96% !important;
    }
}
</style>
