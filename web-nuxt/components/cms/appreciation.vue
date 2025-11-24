<template>
    <div>
        <el-dialog
            width="40%"
            class="ba-cms-appreciation-dialog"
            :append-to-body="true"
            :model-value="props.modelValue"
            title="赞赏"
            @close="onClose"
        >
            <el-form ref="formRef" :model="state.form" :rules="rules" @keyup.enter="onSubmit" label-width="100px" label-position="right">
                <el-form-item class="appreciation-info-box">
                    <el-image class="avatar" fit="cover" :src="object.userInfo!.avatar" loading="lazy" :alt="object.userInfo!.nickname">
                        <template #placeholder>
                            <div class="img-loading-placeholder">
                                <Loading />
                            </div>
                        </template>
                    </el-image>
                    <div class="appreciation-info">
                        <div class="user-nickname">{{ truncate(object.userInfo!.nickname, { length: 20 }) }}</div>
                        <div class="appreciation-title">{{ truncate(object.title, { length: 20 }) }}</div>
                        <div class="appreciation-desc">您的支持是我们前进的动力，非常感谢！</div>
                    </div>
                </el-form-item>
                <el-form-item prop="amount" class="balance-recharge-amount" label="赞助金额">
                    <el-radio-group v-model="state.form.amount">
                        <el-radio :value="10" label="10 元" border></el-radio>
                        <el-radio :value="20" label="20 元" border></el-radio>
                        <el-radio :value="50" label="50 元" border></el-radio>
                    </el-radio-group>
                    <el-input
                        class="appreciation-amount-input"
                        type="number"
                        placeholder="其他金额"
                        v-model="state.form.other_amount"
                        @input="state.form.amount = 0"
                    ></el-input>
                </el-form-item>
                <FormItem
                    class="message-textarea"
                    label="留言"
                    @keyup.enter.stop=""
                    @keyup.ctrl.enter="onSubmit"
                    type="textarea"
                    v-model="state.form.message"
                    :input-attr="{
                        rows: 3,
                    }"
                    placeholder="对作者说点什么"
                />
                <FormItem
                    label="支付方式"
                    type="radio"
                    v-model="state.form.pay_type"
                    :data="{
                        content: { wx: '微信支付', alipay: '支付宝支付', balance: '余额支付' },
                        childrenAttr: {
                            border: true,
                        },
                    }"
                />
                <div class="appreciation-footer">
                    <el-button size="large" v-blur :loading="state.submitLoading" @click="onSubmit" type="success"> 确认捐赠 </el-button>
                    <el-button @click="onClose" size="large" text> 取消 </el-button>
                </div>
            </el-form>
        </el-dialog>
        <CmsPay
            v-model="state.payInfo.show"
            :qrcode="state.payInfo.qrcode"
            :log-id="state.payInfo.id"
            :type="state.form.pay_type"
            :key="state.form.pay_type"
            :callback="onPayCallback"
        />
    </div>
</template>

<script setup lang="ts">
import { create } from '~/api/cms/pay'
import { truncate } from 'lodash-es'
import type { FormInstance, FormItemRule } from 'element-plus'
import { ElNotification } from 'element-plus'

interface Props {
    // 是否显示赞赏窗口
    modelValue: boolean
    // 赞赏对象信息
    object: {
        userInfo?: {
            avatar: string
            nickname: string
        }
        title?: string
    }
    // 支付请求参数
    payParams?: anyObj
}
const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    object: () => {
        return {}
    },
    payParams: () => {
        return {}
    },
})

const formRef = ref<FormInstance>()
const state: {
    form: {
        amount: number
        message: string
        pay_type: string
        other_amount: string
    }
    submitLoading: boolean
    payInfo: {
        show: boolean
        id: number
        qrcode: string
    }
} = reactive({
    form: {
        amount: 10,
        message: '',
        pay_type: 'wx',
        other_amount: '',
    },
    submitLoading: false,
    payInfo: {
        show: false,
        id: 0,
        qrcode: '',
    },
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    amount: [
        {
            required: true,
            trigger: 'change',
            validator(rule, value, callback) {
                const amount = state.form.amount || parseFloat(state.form.other_amount)
                if (amount <= 0) {
                    return callback(new Error('请输入正确的充值金额！'))
                }
                return callback()
            },
        },
    ],
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
}>()

const onClose = () => {
    emits('update:modelValue', false)
}

const onSubmit = () => {
    formRef.value?.validate((valid) => {
        if (!valid) return
        state.submitLoading = true
        create({
            project: 'admire',
            amount: state.form.amount || parseFloat(state.form.other_amount),
            type: state.form.pay_type,
            remark: state.form.message,
            ...props.payParams,
        }).then((res) => {
            state.submitLoading = false
            if (res.code == 1) {
                onClose()
                if (['wx', 'alipay'].includes(state.form.pay_type)) {
                    state.payInfo = {
                        show: true,
                        id: res.data.info.id,
                        qrcode: res.data.pay.code_url,
                    }
                } else if ('balance' == state.form.pay_type) {
                    onPayCallback()
                }
            }
        })
    })
}

const onPayCallback = () => {
    ElNotification({
        type: 'success',
        message: '感谢您的赞赏~',
    })
}
</script>

<style lang="scss">
@media screen and (max-width: 1460px) {
    .ba-cms-appreciation-dialog {
        --el-dialog-width: 50% !important;
    }
}
@media screen and (max-width: 1180px) {
    .ba-cms-appreciation-dialog {
        --el-dialog-width: 92% !important;
    }
}
@media screen and (max-width: 860px) {
    .ba-cms-appreciation-dialog {
        .el-radio {
            margin-right: 15px;
            margin-bottom: 15px;
        }
        .appreciation-amount-input {
            margin-left: 0px !important;
            margin-bottom: 15px;
        }
    }
}
@media screen and (max-width: 560px) {
    .appreciation-info-box {
        .el-form-item__content {
            margin-left: 0 !important;
            .avatar {
                margin: 0 auto;
            }
            .appreciation-info {
                width: 100%;
                margin-top: 10px;
                text-align: center;
            }
        }
    }
}
</style>

<style scoped lang="scss">
.appreciation-info-box {
    display: flex;
    align-items: center;
    .avatar {
        height: 80px;
        width: 80px;
        border-radius: 50%;
    }
    .appreciation-info {
        padding-left: 10px;
        .user-nickname {
            font-size: var(--el-font-size-medium);
            font-weight: bold;
            line-height: 30px;
        }
        .appreciation-desc {
            color: var(--el-color-info);
            line-height: 20px;
        }
    }
}
.appreciation-amount-input {
    width: 100px;
    margin-left: 32px;
}
.message-textarea {
    max-width: 540px;
}
.appreciation-footer {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
