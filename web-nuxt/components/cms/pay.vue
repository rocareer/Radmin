<template>
    <div>
        <el-dialog
            width="680px"
            :close-on-press-escape="false"
            :close-on-click-modal="false"
            class="ba-cms-pay-dialog"
            top="20vh"
            :append-to-body="true"
            :model-value="props.modelValue"
            @close="onClose"
        >
            <template #header>
                <img v-if="type == 'wx'" class="pay-logo" src="~/assets/images/cms/wechat-pay.png" alt="" />
                <img v-else-if="type == 'alipay'" class="pay-logo" src="~/assets/images/cms/alipay.png" alt="" />
            </template>
            <div class="pay-box">
                <div class="left">
                    <div class="order-info" :style="orderInfoStyle">
                        <div v-if="title" class="order-info-items">订单标题：{{ title }}</div>
                        <div v-if="sn" class="order-info-items">订单编号：{{ sn }}</div>
                        <div v-if="desc" class="order-info-items">{{ desc }}</div>
                        <div v-if="amount" class="order-info-items">
                            <span>订单金额：</span>
                            <span class="rmb-symbol">
                                <span>￥</span>
                                <span class="amount">{{ amount }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="pay_qr">
                        <qrcode-vue v-if="type == 'wx'" :value="qrcode" :size="220" :margin="0" level="H" />
                        <iframe
                            v-else-if="type == 'alipay'"
                            :srcdoc="qrcode"
                            frameborder="no"
                            border="0"
                            marginwidth="0"
                            marginheight="0"
                            scrolling="no"
                            width="220"
                            height="220"
                            style="overflow: hidden"
                        >
                        </iframe>
                        <div v-if="state.status == 'success'" class="pay-success">
                            <Icon name="fa fa-check" color="var(--el-color-success)" size="30" />
                        </div>
                    </div>
                    <el-alert class="qr-tips" :closable="false" type="success" center>
                        <div class="qr-tips-content">
                            <Icon color="var(--el-color-success)" name="fa fa-wechat" />
                            <span>使用{{ type == 'wx' ? '微信' : '支付宝' }}扫码完成支付</span>
                        </div>
                    </el-alert>
                </div>
                <div class="right">
                    <img v-if="type == 'wx'" class="pay-screenshot" src="~/assets/images/cms/screenshot-wechat.png" />
                    <img v-else-if="type == 'alipay'" class="pay-screenshot" src="~/assets/images/cms/screenshot-alipay.png" />
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { check } from '~/api/cms/pay'
import { CSSProperties } from 'vue'
import QrcodeVue from 'qrcode.vue'

interface Props {
    modelValue: boolean
    // 支付二维码内容
    qrcode: string
    // 支付记录ID，用于轮询支付结果
    logId: number
    // 支付方式
    type?: string
    // 订单标题
    title?: string
    // 订单sn
    sn?: string
    // 描述信息
    desc?: string
    // 金额
    amount?: number | string
    // 成功回调
    callback?: (res: anyObj) => void
}
const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    qrcode: '',
    logId: 0,
    type: 'wx',
    title: '',
    sn: '',
    desc: '',
    amount: '',
    callback: () => {},
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
}>()

let timer: NodeJS.Timeout
const state = reactive({
    status: '',
})

const orderInfoStyle = computed((): CSSProperties => {
    let padding = 15
    const paddingKey = ['title', 'sn', 'desc', 'amount']
    for (const key in paddingKey) {
        if (!props[paddingKey[key] as keyof typeof props]) {
            padding += 3.75
        }
    }
    padding = parseInt(padding.toFixed(0))
    return {
        paddingTop: padding + 'px',
        paddingBottom: padding + 'px',
    }
})

const onClose = () => {
    emits('update:modelValue', false)
}

watch(
    () => props.modelValue,
    (value) => {
        if (value) {
            timer = setInterval(() => {
                check({ id: props.logId }).then((res) => {
                    if (res.code == 1) {
                        state.status = 'success'
                        typeof props.callback == 'function' && props.callback(res.data)
                        onClose()
                    }
                })
            }, 3000)
        } else {
            clearInterval(timer)
        }
    }
)
</script>

<style lang="scss">
.ba-cms-pay-dialog {
    .el-dialog__header {
        border-bottom: 1px solid var(--el-border-color-lighter);
        margin: 0;
    }
    .el-dialog__body {
        padding-top: 0;
    }
}
@media screen and (max-width: 700px) {
    .ba-cms-pay-dialog {
        --el-dialog-width: 96% !important;
    }
    .pay-box {
        .right {
            display: none;
        }
        .left {
            width: 100% !important;
            .order-info {
                text-align: center;
            }
        }
    }
}
</style>

<style scoped lang="scss">
.pay-logo {
    height: 30px;
    user-select: none;
}
.pay-box {
    display: flex;
    .right {
        margin-left: auto;
        .pay-screenshot {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }
    .left {
        width: 280px;
        .order-info {
            padding: 15px 0;
            .order-info-items {
                line-height: 24px;
                font-size: var(--el-font-size-base);
                .rmb-symbol {
                    color: var(--el-color-danger);
                    font-size: var(--el-font-size-small);
                }
                .amount {
                    color: var(--el-color-danger);
                    font-size: var(--el-font-size-medium);
                }
            }
        }
        .pay_qr {
            display: flex;
            margin-bottom: 25px;
            justify-content: center;
            position: relative;
            .pay-success {
                border-radius: 50%;
                border: 3px solid rgba($color: #67c23a, $alpha: 0.8);
                padding: 5px;
                position: absolute;
                left: calc(50% - 15px);
                top: calc(50% - 15px);
            }
        }
        .qr-tips {
            margin-top: 15px;
            .qr-tips-content {
                .icon {
                    margin-right: 5px;
                }
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    }
}
</style>
