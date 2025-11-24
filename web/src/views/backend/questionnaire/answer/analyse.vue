<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.extend!.showAnalyse"
        @close="baTable.form.extend!.showAnalyse = false"
        @opened="opened"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ t('questionnaire.answer.answer') }}:{{ baTable.form.extend!.title }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div class="preview">
                <el-row>
                    <el-col :span="12">
                        <div ref="yuan" class="chart"></div>
                    </el-col>
                    <el-col :span="12">
                        <div ref="zhu" class="chart"></div>
                    </el-col>
                </el-row>
            </div>
        </el-scrollbar>
    </el-dialog>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue'
import type baTableClass from '/@/utils/baTable'
const baTable = inject('baTable') as baTableClass
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

import * as echarts from 'echarts'

const yuan = ref()
const zhu = ref()

var chartYuan: any
var chartZhu: any

//关闭弹窗
const opened = () => {
    if (chartYuan) {
        chartYuan.dispose()
    }
    chartYuan = echarts.init(yuan.value)
    chartYuan.setOption(baTable.form.extend!.optionYuan)
    if (chartZhu) {
        chartZhu.dispose()
    }
    chartZhu = echarts.init(zhu.value)
    chartZhu.setOption(baTable.form.extend!.optionZhu)

    baTable.form.loading = false
}
</script>

<style scoped lang="scss">
.preview {
    .chart {
        width: 100%;
        height: 400px;
    }
}
</style>
