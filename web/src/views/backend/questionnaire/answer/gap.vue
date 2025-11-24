<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.extend!.showGap"
        @close="baTable.form.extend!.showGap = false"
        @opened="open"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ t('questionnaire.answer.answer') }}:{{ baTable.form.extend!.title }}
            </div>
        </template>
        <div class="infinite-list" style="overflow: auto">
            <el-scrollbar v-loading="state.loading">
                <div v-infinite-scroll="load" :infinite-scroll-immediate="false">
                    <div v-for="(item, index) in state.list" :key="index" class="card-box">
                        <div>{{ item.create_time }}</div>
                        <el-card class="card">
                            <p>{{ item.checked }}</p>
                        </el-card>
                    </div>
                </div>
            </el-scrollbar>
        </div>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject, reactive } from 'vue'
import type baTableClass from '/@/utils/baTable'
const baTable = inject('baTable') as baTableClass
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
import { gapApi } from '/@/api/backend/questionnaire/common'

const state = reactive({
    page: 0,
    list: [] as any,
    loading: false,
    hasMore: true,
})

//初始化
const open = () => {
    state.page = 0
    state.list = []
    state.hasMore = true
    load()
}
//获取数据
const load = () => {
    if (state.hasMore == false) {
        return false
    }
    state.page = state.page + 1
    let params = {
        title: baTable.form.extend!.title,
        type: baTable.form.extend!.type,
        must: baTable.form.extend!.must,
        options: baTable.form.extend!.options,
        page: state.page,
    }
    state.loading = true
    gapApi(params)
        .then((res) => {
            state.list = state.list.concat(res.data.list)
            state.hasMore = res.data.hasMore
            state.loading = false
        })
        .catch((err) => {
            console.log(err)
        })
}
</script>

<style scoped lang="scss">
.infinite-list {
    height: 60vh;
    padding: 0px 0px;
    margin: 0;
    list-style: none;
}
.card-box {
    margin: 15px 0px;
    padding: 0px 10px;
}
.card {
    margin-top: 8px;
}
</style>
