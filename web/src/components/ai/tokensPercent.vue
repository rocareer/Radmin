<template>
    <div>
        <el-divider content-position="left"> Token占比配置 </el-divider>
        <el-form-item label="短期记忆">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_short_memory_percent')"
                    v-model="aiPercent.percent.ai_short_memory_percent.value"
                    :min="aiPercent.percent.ai_short_memory_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(aiPercent.percent.ai_short_memory_percent.value) }} tokens</div>
            </div>
            <div class="block-help">当前模型支持 {{ aiPercent.tokenData.sum }} tokens上下文</div>
        </el-form-item>
        <el-form-item label="身份提示词">
            <div class="ai-percent">
                <el-slider disabled show-input v-model="aiPercent.percent.ai_prompt_percent.value" />
                <div class="token-count">{{ geTokenCount(aiPercent.percent.ai_prompt_percent.value) }} tokens</div>
            </div>
            <div class="block-help">已经根据当前设置的身份提示词计算了大概的 token 占比</div>
        </el-form-item>
        <el-form-item label="知识库">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_kbs_percent')"
                    v-model="aiPercent.percent.ai_kbs_percent.value"
                    :min="aiPercent.percent.ai_kbs_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(aiPercent.percent.ai_kbs_percent.value) }} tokens</div>
            </div>
        </el-form-item>
        <el-form-item label="用户输入">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_user_input_percent')"
                    v-model="aiPercent.percent.ai_user_input_percent.value"
                    :min="aiPercent.percent.ai_user_input_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(aiPercent.percent.ai_user_input_percent.value) }} tokens</div>
            </div>
        </el-form-item>
        <el-form-item label="AI响应">
            <div class="ai-percent">
                <el-slider
                    :format-tooltip="formatSliderTooltip"
                    @change="onSliderChange('ai_response_percent')"
                    v-model="aiPercent.percent.ai_response_percent.value"
                    :min="aiPercent.percent.ai_response_percent.min"
                    show-input
                />
                <div class="token-count">{{ geTokenCount(aiPercent.percent.ai_response_percent.value, aiPercent.tokenData.reserve) }} tokens</div>
            </div>
            <div class="block-help">
                {{
                    aiPercent.tokenData.reserve > 0
                        ? '当前模型已经为输出预留了 ' + aiPercent.tokenData.reserve + ' tokens，此值设置为 0% 不影响输出'
                        : ''
                }}
            </div>
        </el-form-item>
    </div>
</template>

<script setup lang="ts">
import { aiPercent, formatSliderTooltip, onSliderChange, geTokenCount } from './index'
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
}
</style>
