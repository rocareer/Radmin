<!-- AI会话组件，对话窗口与配置平铺 -->
<template>
    <div class="h100">
        <client-only>
            <el-row class="chat-row" :gutter="10">
                <el-col class="window-col h100" :xs="24" :sm="18">
                    <Window v-if="ai.state.loading" />
                    <div v-else class="ba-center">
                        <Loading />
                    </div>
                </el-col>
                <el-col class="hidden-xs-only" :sm="6">
                    <Config v-if="ai.state.loading" />
                    <div v-else class="ba-center">
                        <Loading />
                    </div>
                </el-col>
            </el-row>
        </client-only>
    </div>
</template>

<script setup lang="ts">
import Window from '~/composables/ai/sessionWindow.vue'
import Config from '~/composables/ai/sessionConfig.vue'
import { getInitData } from '~/composables/ai/index'

const ai = useAi()

getInitData()
</script>

<style scoped lang="scss">
.chat-row {
    height: 100%;
    width: 100%;
    box-sizing: border-box;
}
@media screen and (max-width: 768px) {
    .chat-row {
        margin: 0 !important;
        .el-col {
            padding: 0 !important;
        }
    }
}
</style>
