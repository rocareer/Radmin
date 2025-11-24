<!-- 本页面，是由主体+多个动态渲染的组件拼凑而成的（模块化） -->
<!-- 动态渲染的组件存放于 ./mixins 它们可能是一个按钮、tab，甚至单行文本，程序将根据后台配置，按需的显示它们 -->
<!-- 有多个动态渲染组件的位置，我们根据页面主体对这些位置进行了取名 -->
<template>
    <div>
        <el-container class="is-vertical">
            <BaHeader />
            <el-scrollbar class="ba-user-card-scrollbar" :style="calcHeight(60)">
                <el-main class="layouts-main">
                    <el-row justify="center">
                        <el-col v-if="!isEmpty(state.user)" :span="16" :xs="24">
                            <el-card shadow="hover">
                                <div class="user-info">
                                    <img class="avatar" :src="state.user.avatar" alt="" />
                                    <div class="base-info-box">
                                        <div class="nickname">
                                            <span class="name">{{ state.user.nickname }}</span>
                                            <span v-if="state.officialAccount.includes(state.user.id!.toString())" class="desc">
                                                <span> - </span>
                                                <span>{{ t('user.card.Official account') }}</span>
                                            </span>
                                        </div>
                                        <div class="motto">{{ state.user.motto ? state.user.motto : t('defaultMotto') }}</div>

                                        <!-- 个性签名之下-统计信息(inline)-start -->
                                        <div v-if="!isEmpty(state.components['statistic'])" class="statistics">
                                            <template v-for="(item, idx) in state.components['statistic']" :key="idx">
                                                <component :user="state.user" :item="item" :is="mixins[item.component]" />
                                            </template>
                                        </div>
                                        <!-- 个性签名之下-统计信息(inline)-end -->

                                        <!-- 个性签名之下-独占行(block)-start -->
                                        <template v-if="!isEmpty(state.components['under_motto'])">
                                            <template v-for="(item, idx) in state.components['under_motto']" :key="idx">
                                                <component :user="state.user" :item="item" :is="mixins[item.component]" />
                                            </template>
                                        </template>
                                        <!-- 个性签名之下-独占行(block)-end -->

                                        <!-- 统计信息之下-操作按钮(inline)-start -->
                                        <div v-if="!isEmpty(state.components['opt'])" class="footer">
                                            <div class="footer-opts">
                                                <template v-for="(item, idx) in state.components['opt']" :key="idx">
                                                    <component :user="state.user" :item="item" :is="mixins[item.component]" />
                                                </template>
                                            </div>
                                        </div>
                                        <!-- 统计信息之下-操作按钮(inline)-end -->
                                    </div>
                                </div>
                            </el-card>

                            <!-- 会员信息版块与选项卡版块之间(block)-start -->
                            <template v-if="!isEmpty(state.components['middle'])">
                                <template v-for="(item, idx) in state.components['middle']" :key="idx">
                                    <component :user="state.user" :item="item" :is="mixins[item.component]" />
                                </template>
                            </template>
                            <!-- 会员信息版块与选项卡版块之间(block)-end -->

                            <!-- 作为底部选项卡之一(block)-start -->
                            <el-tabs v-if="!isEmpty(state.components['tab'])" type="border-card" class="border-card-tabs" v-model="state.activeTab">
                                <el-tab-pane v-for="(item, idx) in state.components['tab']" :key="idx" :label="item.title" :name="item.component">
                                    <component :user="state.user" :item="item" :is="mixins[item.component]" />
                                </el-tab-pane>
                            </el-tabs>
                            <!-- 作为底部选项卡之一(block)-end -->
                        </el-col>
                    </el-row>
                </el-main>
                <BaFooter />
            </el-scrollbar>
        </el-container>
    </div>
</template>

<script setup lang="ts">
import { isEmpty } from 'lodash-es'
import { useI18n } from 'vue-i18n'
import { userCard } from '~/api/interaction'
import { User } from '~/types/userCard'

definePageMeta({
    layout: false,
    name: 'user/card',
    noNeedLogin: true,
})

const { t } = useI18n()
const route = useRoute()
const state: {
    user: Partial<User>
    activeTab: string
    components: anyObj
    officialAccount: string[]
} = reactive({
    user: {},
    activeTab: '',
    components: {},
    officialAccount: [],
})

const mixins: Record<string, Component> = {}
const mixinComponents = import.meta.glob('~/composables/template/interaction/**.vue')
for (const key in mixinComponents) {
    const res: any = await mixinComponents[key]()
    const fileName = key.replace('/composables/template/interaction/', '').replace('.vue', '')
    mixins[fileName] = res.default
}

const { data } = await userCard({ userId: route.params.id })
if (data.value?.code == 1) {
    state.user = data.value.data.user
    state.components = data.value.data.components
    state.officialAccount = data.value.data.officialAccount
    if (!isEmpty(state.components['tab'])) {
        state.activeTab = data.value.data.components['tab'][0]['component']
    }
}
</script>

<style scoped lang="scss">
.user-info {
    display: flex;
    align-items: center;
    .avatar {
        height: 150px;
        width: 150px;
        border-radius: 50%;
        border: 1px solid var(--el-border-color-extra-light);
    }
    .base-info-box {
        margin: 0 18px;
        flex: 1;
        .nickname {
            .name {
                font-weight: bold;
                font-size: var(--el-font-size-large);
            }
            .desc {
                color: var(--el-color-info);
            }
        }
        .motto {
            padding: 10px 0;
            color: var(--el-color-info);
        }
        .statistics {
            display: flex;
            align-items: center;
            .statistic-item {
                display: flex;
                align-items: center;
                margin-right: 10px;
                color: var(--el-text-color-placeholder);
                font-size: var(--el-font-size-extra-small);
                :deep(.icon) {
                    margin-right: 2px;
                }
            }
        }
        .footer {
            display: flex;
            align-items: center;
            padding: 10px 0;
            margin-top: 10px;
            border-top: 1px solid var(--el-border-color-extra-light);
        }
    }
}

.border-card-tabs {
    margin-top: 20px;
    border: none;
    border-radius: var(--el-border-radius-base);
}
.border-card-tabs :deep(.el-tabs__header) {
    background-color: var(--ba-bg-color);
    border-bottom: none;
    border-radius: var(--el-border-radius-base);
}
.border-card-tabs :deep(.el-tabs__content) {
    padding-bottom: 0;
}
.border-card-tabs :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}
.border-card-tabs :deep(.el-tabs__nav-wrap) {
    border-radius: var(--el-border-radius-base);
}
</style>
