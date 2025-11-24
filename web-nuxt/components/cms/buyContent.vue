<template>
    <div>
        <client-only>
            <el-alert class="price-alert" title="温馨提示" type="warning" :closable="false" show-icon>
                <template #default>
                    <div>{{ getPriceDescription() }}</div>
                    <div class="pay-button">
                        <div v-if="props.data.currency == 'RMB'">
                            <el-button @click="onPay('wx')" :loading="state.loadingPay" v-blur type="warning" plain>微信支付</el-button>
                            <el-button @click="onPay('alipay')" :loading="state.loadingPay" v-blur type="warning" plain>支付宝支付</el-button>
                            <el-popconfirm @confirm="onPay('balance')" title="确定使用余额购买当前内容吗？">
                                <template #reference>
                                    <el-button :loading="state.loadingPay" v-blur type="warning" plain>余额支付</el-button>
                                </template>
                            </el-popconfirm>
                        </div>
                        <div v-else>
                            <el-popconfirm @confirm="onPay('score')" title="确定使用积分购买当前内容吗？">
                                <template #reference>
                                    <el-button :loading="state.loadingPay" v-blur type="warning" plain>积分支付</el-button>
                                </template>
                            </el-popconfirm>
                        </div>
                    </div>
                </template>
            </el-alert>
        </client-only>

        <CmsPay
            v-model="state.payInfo.show"
            :qrcode="state.payInfo.qrcode"
            :log-id="state.payInfo.id"
            :type="state.payInfo.type"
            :key="state.payInfo.type"
            :title="'购买《' + props.data.title + '》'"
            :sn="state.payInfo.sn"
            :desc="'购买用户：' + userInfo.nickname + '（' + (userInfo.email ?? userInfo.mobile) + '）'"
            :amount="state.payInfo.amount"
            :callback="onPayCallback"
        />
    </div>
</template>

<script setup lang="ts">
import { create } from '~/api/cms/pay'
interface Props {
    data: anyObj
}
const props = withDefaults(defineProps<Props>(), {
    data: () => {
        return {}
    },
})

const userInfo = useUserInfo()

const state: {
    loadingPay: boolean
    payInfo: {
        id: number
        show: boolean
        qrcode: string
        type: string
        sn: string
        amount: string
    }
} = reactive({
    loadingPay: false,
    payInfo: {
        id: 0,
        show: false,
        qrcode: '',
        type: 'wx',
        sn: '',
        amount: '0',
    },
})

const getPriceDescription = () => {
    if (props.data.currency == 'RMB') {
        return '你需要支付 ￥' + props.data.price + ' 元后才能查看付费内容'
    } else {
        return '你需要支付 ' + props.data.price + ' 积分后才能查看内容'
    }
}

const onPay = (type: string) => {
    state.loadingPay = true
    state.payInfo.type = type
    create({
        project: 'content',
        object: props.data.id,
        type: state.payInfo.type,
    }).then((res) => {
        state.loadingPay = false
        if (res.code == 1) {
            if (['score', 'balance'].includes(state.payInfo.type)) {
                onPayCallback()
            } else {
                state.payInfo = {
                    show: true,
                    ...res.data.info,
                    qrcode: res.data.pay.code_url,
                }
            }
        }
    })
}

const onPayCallback = () => {
    const router = useRouter()
    router.go(0)
}
</script>

<style scoped lang="scss">
.price-alert {
    padding: 15px;
    :deep(.el-alert__content) {
        .el-alert__title {
            font-size: var(--el-font-size-medium);
        }
        .el-alert__description {
            font-size: var(--el-font-size-base);
        }
        .pay-button {
            margin-top: 10px;
        }
    }
}
</style>
