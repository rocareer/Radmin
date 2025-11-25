<template>
    <div class="default-main">
        <el-row v-loading="state.loading" :gutter="20">
            <el-col class="xs-mb-20" :xs="24" :sm="16">
                <el-form
                    v-if="!state.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="onSubmit()"
                    :model="state.form"
                    :rules="state.rules"
                    :label-position="'top'"
                >
                    <el-tabs v-model="state.activeTab" type="border-card">
                        <el-tab-pane class="config-tab-pane" name="base" label="基础配置">
                            <div class="config-form-item">
                                <el-alert
                                    class="tab-pane-alert"
                                    title="腾讯云知识库配置，用于管理本地文件上传和云端同步功能"
                                    type="info"
                                    :closable="false"
                                />
                                <FormItem
                                    label="配置名称"
                                    prop="name"
                                    type="string"
                                    v-model="state.form.name"
                                    placeholder="请输入配置名称"
                                    :attr="{
                                        blockHelp: '用于标识此配置的用途，如：生产环境配置、测试环境配置等'
                                    }"
                                />
                                <FormItem
                                    label="SecretId"
                                    prop="secret_id"
                                    type="string"
                                    v-model="state.form.secret_id"
                                    placeholder="请输入腾讯云SecretId"
                                    :attr="{
                                        blockHelp: '腾讯云API访问密钥ID，可在腾讯云控制台获取'
                                    }"
                                />
                                <FormItem
                                    label="SecretKey"
                                    prop="secret_key"
                                    type="string"
                                    v-model="state.form.secret_key"
                                    placeholder="请输入腾讯云SecretKey"
                                    :input-attr="{ showPassword: true }"
                                    :attr="{
                                        blockHelp: '腾讯云API访问密钥，请妥善保管'
                                    }"
                                />
                                <FormItem
                                    label="地域"
                                    prop="region"
                                    type="select"
                                    v-model="state.form.region"
                                    :data="{ content: state.regionOptions }"
                                    :attr="{
                                        blockHelp: '选择腾讯云服务所在的地域'
                                    }"
                                />
                                <FormItem
                                    label="API端点"
                                    prop="endpoint"
                                    type="string"
                                    v-model="state.form.endpoint"
                                    placeholder="请输入API端点"
                                    :attr="{
                                        blockHelp: '腾讯云API服务端点地址，通常为 tcb.tencentcloudapi.com'
                                    }"
                                />
                                <FormItem
                                    label="状态"
                                    prop="status"
                                    type="radio"
                                    v-model="state.form.status"
                                    :data="{ content: { '1': t('Enable'), '0': t('Disable') } }"
                                    :attr="{
                                        blockHelp: '启用或禁用此配置'
                                    }"
                                />
                                <FormItem
                                    label="备注"
                                    prop="remark"
                                    type="textarea"
                                    v-model="state.form.remark"
                                    placeholder="请输入备注信息"
                                    :input-attr="{ rows: 3 }"
                                />
                            </div>
                            <el-button :loading="state.submitLoading" type="primary" @click="onSubmit()">{{ t('Save') }}</el-button>
                            <el-button @click="onTestConnection()" type="success">{{ t('kb.tencentConfig.测试连接') }}</el-button>
                        </el-tab-pane>
                        <el-tab-pane class="config-tab-pane" name="advanced" label="高级配置">
                            <div class="config-form-item">
                                <el-alert
                                    class="tab-pane-alert"
                                    title="高级配置选项，用于优化文件上传和同步性能"
                                    type="warning"
                                    :closable="false"
                                />
                                <FormItem
                                    label="上传文件大小限制(MB)"
                                    prop="upload_max_size"
                                    type="number"
                                    v-model.number="state.form.upload_max_size"
                                    :input-attr="{ step: 1, valueOnClear: 50 }"
                                    :attr="{
                                        blockHelp: '单个文件上传的最大大小限制，单位为MB'
                                    }"
                                />
                                <FormItem
                                    label="支持的文件格式"
                                    prop="allowed_extensions"
                                    type="string"
                                    v-model="state.form.allowed_extensions"
                                    placeholder="doc,docx,pdf,txt,md"
                                    :attr="{
                                        blockHelp: '支持上传的文件格式，多个格式用逗号分隔'
                                    }"
                                />
                                <FormItem
                                    label="同步间隔(分钟)"
                                    prop="sync_interval"
                                    type="number"
                                    v-model.number="state.form.sync_interval"
                                    :input-attr="{ step: 5, valueOnClear: 30 }"
                                    :attr="{
                                        blockHelp: '自动同步的时间间隔，设置为0则不自动同步'
                                    }"
                                />
                                <FormItem
                                    label="并发上传数量"
                                    prop="concurrent_upload"
                                    type="number"
                                    v-model.number="state.form.concurrent_upload"
                                    :input-attr="{ step: 1, valueOnClear: 3 }"
                                    :attr="{
                                        blockHelp: '同时上传的文件数量，建议根据服务器性能设置'
                                    }"
                                />
                                <FormItem
                                    label="启用增量同步"
                                    prop="enable_incremental_sync"
                                    type="radio"
                                    v-model="state.form.enable_incremental_sync"
                                    :data="{ content: { '1': t('Enable'), '0': t('Disable') } }"
                                    :attr="{
                                        blockHelp: '是否启用增量同步，仅同步有变化的文件'
                                    }"
                                />
                                <FormItem
                                    label="启用文件压缩"
                                    prop="enable_compression"
                                    type="radio"
                                    v-model="state.form.enable_compression"
                                    :data="{ content: { '1': t('Enable'), '0': t('Disable') } }"
                                    :attr="{
                                        blockHelp: '是否在上传前压缩文件以节省带宽'
                                    }"
                                />
                            </div>
                            <el-button :loading="state.submitLoading" type="primary" @click="onSubmit()">{{ t('Save') }}</el-button>
                        </el-tab-pane>
                    </el-tabs>
                </el-form>
            </el-col>
            <el-col :xs="24" :sm="8">
                <el-card :header="t('kb.tencentConfig.配置状态')">
                    <div class="config-status">
                        <div class="status-item">
                            <span class="label">配置状态：</span>
                            <el-tag :type="state.form.status === '1' ? 'success' : 'danger'">
                                {{ state.form.status === '1' ? t('Enable') : t('Disable') }}
                            </el-tag>
                        </div>
                        <div class="status-item">
                            <span class="label">最后连接时间：</span>
                            <span class="value">{{ state.lastConnectionTime || '未连接' }}</span>
                        </div>
                        <div class="status-item">
                            <span class="label">连接状态：</span>
                            <el-tag :type="state.connectionStatus === 'success' ? 'success' : state.connectionStatus === 'error' ? 'danger' : 'info'">
                                {{ state.connectionStatus === 'success' ? '连接正常' : state.connectionStatus === 'error' ? '连接失败' : '未测试' }}
                            </el-tag>
                        </div>
                        <div class="status-item">
                            <span class="label">文件统计：</span>
                            <span class="value">{{ state.fileStats.total || 0 }} 个文件</span>
                        </div>
                        <div class="status-item">
                            <span class="label">同步状态：</span>
                            <span class="value">{{ state.syncStatus || '未同步' }}</span>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <el-button type="primary" size="small" @click="onTestConnection()" :loading="state.testingConnection">
                            {{ t('kb.tencentConfig.测试连接') }}
                        </el-button>
                        <el-button type="success" size="small" @click="onSyncNow()" :loading="state.syncing">
                            立即同步
                        </el-button>
                        <el-button type="warning" size="small" @click="onClearCache()">
                            清理缓存
                        </el-button>
                    </div>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { FormInstance } from 'element-plus'
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { index, save, testConnection, syncNow, clearCache } from '/@/api/backend/kb/tencent-config'
import FormItem from '/@/components/formItem/index.vue'
import { buildValidatorData } from '/@/utils/validate'

defineOptions({
    name: 'kb/tencentConfig',
})

const { t } = useI18n()

const state = reactive({
    loading: false,
    form: {
        name: '',
        secret_id: '',
        secret_key: '',
        region: 'ap-beijing',
        endpoint: 'tcb.tencentcloudapi.com',
        status: '1',
        remark: '',
        upload_max_size: 50,
        allowed_extensions: 'doc,docx,pdf,txt,md',
        sync_interval: 30,
        concurrent_upload: 3,
        enable_incremental_sync: '1',
        enable_compression: '1',
    },
    rules: {
        name: [buildValidatorData({ name: 'required', title: t('kb.tencentConfig.name') })],
        secret_id: [buildValidatorData({ name: 'required', title: t('kb.tencentConfig.secret_id') })],
        secret_key: [buildValidatorData({ name: 'required', title: t('kb.tencentConfig.secret_key') })],
        region: [buildValidatorData({ name: 'required', title: t('kb.tencentConfig.region') })],
        endpoint: [buildValidatorData({ name: 'required', title: t('kb.tencentConfig.endpoint') })],
        upload_max_size: [buildValidatorData({ name: 'required', title: '上传文件大小限制' })],
    },
    activeTab: 'base',
    submitLoading: false,
    testingConnection: false,
    syncing: false,
    connectionStatus: '', // success, error, unknown
    lastConnectionTime: '',
    fileStats: {
        total: 0,
        uploaded: 0,
        synced: 0
    },
    syncStatus: '',
    regionOptions: [
        { value: 'ap-beijing', label: '北京' },
        { value: 'ap-shanghai', label: '上海' },
        { value: 'ap-guangzhou', label: '广州' },
        { value: 'ap-chengdu', label: '成都' },
        { value: 'ap-chongqing', label: '重庆' },
        { value: 'ap-hongkong', label: '香港' },
        { value: 'ap-singapore', label: '新加坡' },
        { value: 'na-toronto', label: '多伦多' },
        { value: 'na-siliconvalley', label: '硅谷' },
        { value: 'eu-frankfurt', label: '法兰克福' },
    ]
})

const formRef = ref<FormInstance>()

const onSubmit = () => {
    if (!formRef.value) return
    formRef.value.validate((valid: boolean) => {
        if (valid) {
            state.submitLoading = true
            save({ ...state.form, activeTab: state.activeTab })
                .then((res) => {
                    if (res.code === 1) {
                        ElMessage.success(res.msg)
                    } else {
                        ElMessage.error(res.msg)
                    }
                })
                .finally(() => {
                    state.submitLoading = false
                })
        }
    })
}

const onTestConnection = () => {
    state.testingConnection = true
    testConnection()
        .then((res) => {
            if (res.code === 1) {
                state.connectionStatus = 'success'
                state.lastConnectionTime = new Date().toLocaleString()
                ElMessage.success(res.msg)
            } else {
                state.connectionStatus = 'error'
                ElMessage.error(res.msg)
            }
        })
        .catch(() => {
            state.connectionStatus = 'error'
            ElMessage.error(t('kb.tencentConfig.测试连接失败'))
        })
        .finally(() => {
            state.testingConnection = false
        })
}

const onSyncNow = () => {
    state.syncing = true
    syncNow()
        .then((res) => {
            if (res.code === 1) {
                state.syncStatus = '同步完成'
                ElMessage.success(res.msg)
            } else {
                ElMessage.error(res.msg)
            }
        })
        .catch(() => {
            ElMessage.error('同步失败')
        })
        .finally(() => {
            state.syncing = false
        })
}

const onClearCache = () => {
    clearCache()
        .then((res) => {
            if (res.code === 1) {
                ElMessage.success(res.msg)
            } else {
                ElMessage.error(res.msg)
            }
        })
        .catch(() => {
            ElMessage.error('清理缓存失败')
        })
}

index().then((res) => {
    if (res.data && res.data.data) {
        state.form = { ...state.form, ...res.data.data }
    }
    if (res.data && res.data.stats) {
        state.fileStats = res.data.stats
    }
    if (res.data && res.data.lastConnectionTime) {
        state.lastConnectionTime = res.data.lastConnectionTime
    }
    if (res.data && res.data.connectionStatus) {
        state.connectionStatus = res.data.connectionStatus
    }
    if (res.data && res.data.syncStatus) {
        state.syncStatus = res.data.syncStatus
    }
})

import { ElMessage } from 'element-plus'
</script>

<style scoped lang="scss">
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--ba-bg-color);
    border-bottom: none;
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}
.el-tabs--border-card :deep(.el-tabs__nav-wrap) {
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}
.el-card :deep(.el-card__header) {
    height: 40px;
    padding: 0;
    line-height: 40px;
    border: none;
    padding-left: 20px;
    background-color: var(--ba-bg-color);
}
.config-tab-pane {
    padding: 5px;
}
.tab-pane-alert {
    margin-bottom: 10px;
}
.config-status {
    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        .label {
            font-weight: 500;
            color: var(--el-text-color-regular);
        }
        .value {
            color: var(--el-text-color-secondary);
        }
    }
}
.quick-actions {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    .el-button {
        width: 100%;
    }
}
@media screen and (max-width: 768px) {
    .xs-mb-20 {
        margin-bottom: 20px;
    }
}
</style>