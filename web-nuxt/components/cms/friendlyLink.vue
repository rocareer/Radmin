<template>
    <div>
        <el-row>
            <!-- 友情链接 -->
            <el-card class="link-box-card" shadow="never">
                <template #header>
                    <div class="card-header">
                        <span>友情链接</span>
                        <el-button @click="onApplyfriendlyLink" v-blur class="button" text>申请链接</el-button>
                    </div>
                </template>
                <ul class="links">
                    <a :href="item.link" :target="item.target" v-for="(item, idx) in cmsConfig.friendly_link" :key="idx">
                        <li>
                            <el-tooltip :content="item.title" placement="top">
                                <img :src="item.logo" :alt="item.title" />
                            </el-tooltip>
                        </li>
                    </a>
                </ul>
            </el-card>
            <el-dialog title="申请友情链接" class="apply-friendly-link" v-model="state.showApplyfriendlyLink" width="50%" :destroy-on-close="true">
                <div :style="'width: calc(100% - ' + (shrink ? 0 : 80) + 'px)'">
                    <el-form
                        ref="applyfriendlyLinkFormRef"
                        @keyup.enter="onSubmitFriendlyLink"
                        :model="state.applyfriendlyLinkForm"
                        :label-width="160"
                        :label-position="shrink ? 'top' : 'right'"
                        :rules="applyfriendlyLinkFormRules"
                    >
                        <FormItem
                            label="外链标题"
                            type="string"
                            v-model="state.applyfriendlyLinkForm.title"
                            prop="title"
                            :placeholder="$t('Please input field', { field: '外链标题' })"
                        />
                        <FormItem
                            label="链接地址"
                            type="string"
                            v-model="state.applyfriendlyLinkForm.link"
                            prop="link"
                            :placeholder="$t('Please input field', { field: '链接地址' })"
                        />
                        <FormItem label="LOGO图片" type="image" v-model="state.applyfriendlyLinkForm.logo" prop="logo" />
                        <FormItem
                            label="联系方式"
                            type="textarea"
                            v-model="state.applyfriendlyLinkForm.contact"
                            prop="contact"
                            :placeholder="$t('Please input field', { field: '联系方式' })"
                            :input-attr="{
                                rows: 3,
                            }"
                        />
                        <FormItem
                            label="申请备注"
                            type="textarea"
                            v-model="state.applyfriendlyLinkForm.remark"
                            prop="remark"
                            :placeholder="$t('Please input field', { field: '申请备注' })"
                            :input-attr="{
                                rows: 3,
                            }"
                        />
                    </el-form>
                </div>
                <template #footer>
                    <div :style="'width: calc(100% - ' + 90 + 'px)'">
                        <el-button @click="state.showApplyfriendlyLink = false">{{ $t('Cancel') }}</el-button>
                        <el-button v-blur :loading="state.applyfriendlyLinkForm.loading" @click="onSubmitFriendlyLink" type="primary">
                            {{ $t('Save') }}
                        </el-button>
                    </div>
                </template>
            </el-dialog>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { FormInstance, FormItemRule } from 'element-plus'
import { applyFriendlyLink } from '~/api/cms/index'
import clickCaptcha from '~/composables/clickCaptcha'

const cmsConfig = useCmsConfig()
const shrink = document && document.body.clientWidth < 992 ? true : false
const applyfriendlyLinkFormRef = ref<FormInstance>()
const state: {
    data: anyObj
    showApplyfriendlyLink: boolean
    applyfriendlyLinkForm: {
        title: string
        link: string
        logo: string
        contact: string
        remark: string
        loading: boolean
    }
    captchaId: string
} = reactive({
    data: {},
    showApplyfriendlyLink: false,
    applyfriendlyLinkForm: {
        title: '',
        link: '',
        logo: '',
        contact: '',
        remark: '',
        loading: false,
    },
    captchaId: uuid(),
})

const applyfriendlyLinkFormRules: Partial<Record<string, FormItemRule[]>> = reactive({
    title: [buildValidatorData({ name: 'required', title: '外链标题' })],
    link: [buildValidatorData({ name: 'required', title: '链接地址' })],
    logo: [buildValidatorData({ name: 'required', message: '请选择LOGO图片' })],
})

const onApplyfriendlyLink = () => {
    const userInfo = useUserInfo()
    if (!userInfo.isLogin()) {
        ElMessageBox.alert('请登录后提交友链申请~', '提示', {
            confirmButtonText: '确定',
        })
        return
    }
    state.showApplyfriendlyLink = true
}
const onSubmitFriendlyLink = () => {
    applyfriendlyLinkFormRef.value!.validate((res) => {
        if (!res) return
        clickCaptcha(state.captchaId, (captchaInfo: string) => postFriendlyLink(captchaInfo))
    })
}
const postFriendlyLink = (captchaInfo: string) => {
    state.applyfriendlyLinkForm.loading = true
    applyFriendlyLink({
        ...state.applyfriendlyLinkForm,
        captchaInfo,
        captchaId: state.captchaId,
    }).then((res) => {
        state.applyfriendlyLinkForm.loading = false
        if (res.code == 1) {
            state.showApplyfriendlyLink = false
        }
    })
}
</script>

<style scoped lang="scss">
.link-box-card {
    border: none;
    border-radius: 0;
    margin-bottom: 10px;
    width: 100%;
    :deep(.el-card__header) {
        padding: 15px;
        border: none;
        border-bottom: 1px solid var(--el-border-color-extra-light);
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    :deep(.el-card__body) {
        padding: 10px 15px 0px 15px;
    }
    .links {
        padding-left: 0;
        list-style: none;
        li {
            display: inline-block;
            margin: 0 12px 12px 0;
            width: 140px;
            text-align: center;
            border: 1px solid var(--el-color-info-light-9);
            cursor: pointer;
            img {
                width: 100%;
                object-fit: cover;
            }
        }
        li:hover {
            border: 1px solid var(--el-color-info-light-5);
        }
    }
}
@media screen and (max-width: 992px) {
    :deep(.apply-friendly-link) {
        width: 96%;
    }
}
</style>
