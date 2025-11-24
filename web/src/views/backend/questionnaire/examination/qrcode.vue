<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.extend!.showQrcode"
        @close="baTable.form.extend!.showQrcode = false"
        width="20%"
        @open="open"
        @closed="close"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ t('questionnaire.examination.qrcode') }}
            </div>
        </template>
        <el-scrollbar v-loading="state.loading" class="ba-table-form-scrollbar">
            <el-divider content-position="left">H5 {{ t('questionnaire.examination.qrcode') }}</el-divider>
            <div class="qrcode">
                <div ref="qrcodeContainer" id="qrcode" v-if="state.link">
                    <qrcode-vue :value="state.link" :size="200"></qrcode-vue>
                </div>
                <div v-else>
                    <img src="" alt="" />
                </div>
                <div class="but">
                    <el-button type="primary" v-blur round @click="downloadQr()" :disabled="!state.link"
                        >{{ t('questionnaire.examination.download') }}{{ t('questionnaire.examination.qrcode') }}</el-button
                    >
                    <el-button type="success" round @click="onCopy()"> {{ t('questionnaire.examination.copy_link') }}</el-button>
                </div>
            </div>
            <el-divider content-position="left">{{ t('questionnaire.examination.mini_qrcode') }}</el-divider>
            <div class="qrcode">
                <div>
                    <img :src="state.mini" alt="" />
                </div>
                <div class="but">
                    <el-button type="primary" v-blur round @click="updateQrCode('mini')">{{ t('questionnaire.examination.reset') }}</el-button>
                    <el-button type="success" round @click="downloadMini()" :disabled="!state.mini"
                        >{{ t('questionnaire.examination.download') }}{{ t('questionnaire.examination.mini_qrcode') }}</el-button
                    >
                </div>
            </div>
        </el-scrollbar>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject, reactive, ref, nextTick } from 'vue'
import type baTableClass from '/@/utils/baTable'
const baTable = inject('baTable') as baTableClass
import { getQrCodeApi, updareQrCodeApi } from '/@/api/backend/questionnaire/common'
import { ElNotification } from 'element-plus'
import QrcodeVue from 'qrcode.vue'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const state = reactive({
    loading: true,
    mini: '', //小程序码图片
    link: '', //h5链接
})

const qrcodeContainer = ref<HTMLDivElement | null>(null)

//下载二维码
const downloadQr = async () => {
    await nextTick()

    if (!qrcodeContainer.value) return
    const container = qrcodeContainer.value
    let url = ''
    const canvas = container.querySelector('canvas')
    if (canvas) {
        url = canvas.toDataURL('image/png')
    }
    const img = container.querySelector('img')
    if (img) {
        url = img.src
    }
    const svg = container.querySelector('svg')
    if (svg) {
        const svgData = new XMLSerializer().serializeToString(svg)
        url = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgData)
    }

    const a = document.createElement('a')
    a.href = url
    a.download = '问卷二维码.png'
    a.click()
}

//下载小程序码
const downloadMini = () => {
    let url = state.mini
    const a = document.createElement('a')
    a.href = url
    a.download = '问卷小程序码.png'
    a.click()
}

//复制链接
const onCopy = () => {
    let link = state.link

    if (!link) {
        ElNotification({
            message: t('questionnaire.examination.no_link'),
            type: 'warning',
        })
    } else {
        navigator.clipboard.writeText(link).then(() => {
            ElNotification({
                message: t('questionnaire.examination.copy_success'),
                type: 'success',
            })
        })
    }
}

/**
 * 更新二维码
 */
const updateQrCode = (type: string) => {
    state.loading = true
    let id = baTable.form.extend!.id
    updareQrCodeApi({ id: id, type: type })
        .then((res) => {
            state.mini = res.data.mini
        })
        .catch((err) => {
            console.log(err)
        })
        .finally(() => {
            state.loading = false
        })
}

/**
 * 获取二维码
 */
const open = () => {
    state.loading = true
    let id = baTable.form.extend!.id
    getQrCodeApi({ id: id })
        .then((res) => {
            state.mini = res.data.mini
            state.link = res.data.link
        })
        .catch((err) => {
            console.log(err)
        })
        .finally(() => {
            state.loading = false
        })
}
//弹窗关闭
const close = () => {
    state.mini = ''
    state.link = ''
    baTable.onTableHeaderAction('refresh', {})
}
</script>

<style scoped lang="scss">
.qrcode {
    text-align: center;
    .but {
        margin-top: 5px;
    }
    img {
        width: 220px;
        height: 220px;
    }
}
</style>
