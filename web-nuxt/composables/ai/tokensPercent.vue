<template>
    <div>
        <el-divider content-position="left"> Token占比 </el-divider>
        <el-form-item label="短期记忆">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_short_memory_percent')"
                    v-model="ai.tokens.percent.ai_short_memory_percent.value"
                    :min="ai.tokens.percent.ai_short_memory_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(ai.tokens.percent.ai_short_memory_percent.value) }} tokens</div>
            </div>
            <div class="block-help">当前模型支持 {{ ai.tokens.tokenData.sum }} tokens上下文</div>
        </el-form-item>
        <el-form-item label="身份提示词">
            <div class="ai-percent">
                <el-slider disabled show-input v-model="ai.tokens.percent.ai_prompt_percent.value" />
                <div class="token-count">{{ geTokenCount(ai.tokens.percent.ai_prompt_percent.value) }} tokens</div>
            </div>
            <div class="block-help">已经根据当前设置的身份提示词计算了大概的 token 占比</div>
        </el-form-item>
        <el-form-item label="知识库">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_kbs_percent')"
                    v-model="ai.tokens.percent.ai_kbs_percent.value"
                    :min="ai.tokens.percent.ai_kbs_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(ai.tokens.percent.ai_kbs_percent.value) }} tokens</div>
            </div>
        </el-form-item>
        <el-form-item label="用户输入">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_user_input_percent')"
                    v-model="ai.tokens.percent.ai_user_input_percent.value"
                    :min="ai.tokens.percent.ai_user_input_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(ai.tokens.percent.ai_user_input_percent.value) }} tokens</div>
            </div>
        </el-form-item>
        <el-form-item label="AI响应">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_response_percent')"
                    v-model="ai.tokens.percent.ai_response_percent.value"
                    :min="ai.tokens.percent.ai_response_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(ai.tokens.percent.ai_response_percent.value, ai.tokens.tokenData.reserve) }} tokens</div>
            </div>
            <div class="block-help">
                {{
                    ai.tokens.tokenData.reserve > 0
                        ? '当前模型已经为输出预留了 ' + ai.tokens.tokenData.reserve + ' tokens，此值设置为 0% 不影响输出'
                        : ''
                }}
            </div>
        </el-form-item>
    </div>
</template>

<script setup lang="ts">
import { formatSliderTooltip, onSliderChange, geTokenCount } from '~/composables/ai/index'

const ai = useAi()
</script>

<style scoped lang="scss">
.ai-percent {
    display: flex;
    width: 100%;
    :deep(.el-slider) {
        flex: 1;
    }
    .token-count {
        margin-left: 15px;
    }
    :deep(.el-input-number) {
        width: 100px;
    }
}
</style>
