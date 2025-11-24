<template>
    <div class="default-main">
        <el-row v-loading="state.loading" :gutter="20">
            <el-col :span="12">
                <el-tabs type="border-card" @tab-change="handleClick" v-model="state.active">
                    <el-tab-pane class="config-tab-pane" :label="t('questionnaire.config.h5')" name="H5">
                        <H5 :h5="state.h5"></H5>
                    </el-tab-pane>
                    <el-tab-pane class="config-tab-pane" :label="t('questionnaire.config.mini')" name="mini">
                        <Mini :mini="state.mini"></Mini>
                    </el-tab-pane>
                    <el-tab-pane class="config-tab-pane" :label="t('questionnaire.config.other')" name="other">
                        <Other :other="state.other"></Other>
                    </el-tab-pane>
                </el-tabs>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

import { getConfig } from '/@/api/backend/questionnaire/common'
import H5 from './h5.vue'
import Mini from './mini.vue'
import Other from './other.vue'

defineOptions({
    name: 'questionnaire/config',
})

const state = reactive({
    loading: true,
    h5: {
        domain: '',
    },
    mini: {
        appid: '',
        secret: '',
    },
    other: {
        num: 1,
        size: 10,
        picture: 'jpg, png, jpeg',
        video: 'mp4',
        file: 'pdf,ppt,xls,xlsx,zip,doc,docx',
    },
    active: 'H5',
})

const handleClick = (tab: any) => {
    state.active = tab
}

const getIndex = () => {
    getConfig()
        .then((res) => {
            state.h5 = res.data.questionnaire_h5
            state.mini = res.data.questionnaire_mini
            state.other = res.data.questionnaire_other
            state.loading = false
        })
        .catch(() => {
            state.loading = false
        })
}

onMounted(() => {
    getIndex()
})
</script>

<style scoped lang="scss">
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}

.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--ba-bg-color);
    border-bottom: none;
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}

.el-tabs--border-card :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}

.el-tabs--border-card :deep(.el-tabs__nav-wrap) {
    border-top-left-radius: var(--el-border-radius-base);
    border-top-right-radius: var(--el-border-radius-base);
}

.el-card :deep(.el-card__header) {
    height: 40px;
    padding: 0;
    line-height: 40px;
    border: none;
    padding-left: 20px;
    background-color: var(--ba-bg-color);
}
</style>
