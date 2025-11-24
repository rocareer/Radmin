<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.extend!.showLook"
        @close="baTable.form.extend!.showLook = false"
        width="30%"
        @open="open"
        @closed="close"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ t('questionnaire.answerSheet.look') }}
            </div>
        </template>
        <el-scrollbar v-loading="state.loading" class="ba-table-form-scrollbar">
            <div class="preview">
                <div class="top">
                    <div class="title">{{ state.row.title }}</div>
                    <div class="desc">{{ state.row.description }}</div>
                </div>
                <div class="questions" v-if="state.row.questions?.length > 0">
                    <div class="item" v-for="(item, index) in state.row.questions" :key="index">
                        <div class="title">
                            <span v-if="item.must == 1" style="color: red">*</span>
                            <span>{{ index + 1 }}.</span>
                            <p>【{{ item.type_str }}】{{ item.title }}</p>
                        </div>
                        <div class="options">
                            <!-- 单选题 -->
                            <div class="type" v-if="item.type == 0">
                                <el-radio-group v-model="item.checked">
                                    <div v-for="(ite, inde) in item.options" :key="inde">
                                        <el-radio :value="ite" disabled>{{ ite }}</el-radio>
                                    </div>
                                </el-radio-group>
                            </div>
                            <!-- 多选题 -->
                            <div class="type" v-if="item.type == 1">
                                <el-checkbox-group v-model="item.checked">
                                    <div v-for="(ite, inde) in item.options" :key="inde">
                                        <el-checkbox :value="ite" disabled>{{ ite }}</el-checkbox>
                                    </div>
                                </el-checkbox-group>
                            </div>
                            <!-- 填空题 -->
                            <div class="type" v-if="item.type == 2">
                                <div class="mt10">
                                    <el-input v-model="item.checked" disabled />
                                </div>
                            </div>
                            <!-- 简答题 -->
                            <div class="type" v-if="item.type == 3">
                                <div class="mt10">
                                    <el-input type="textarea" :rows="4" v-model="item.checked" disabled />
                                </div>
                            </div>
                            <!-- 下拉框 -->
                            <div class="type" v-if="item.type == 4">
                                <div class="mt10">
                                    <el-select v-model="item.checked" disabled>
                                        <el-option v-for="(ite, inde) in item.options" :key="inde" :label="ite" :value="ite" />
                                    </el-select>
                                </div>
                            </div>
                            <!-- 图片 -->
                            <div class="type" v-if="item.type == 5">
                                <el-row :gutter="20">
                                    <el-col :span="5" v-for="(ite, inde) in item.checked" :key="inde">
                                        <el-image :src="ite" :preview-src-list="item.checked" fit="cover" :hide-on-click-modal="true"
                                    /></el-col>
                                </el-row>
                            </div>
                            <!-- 视频 -->
                            <div class="type" v-if="item.type == 6">
                                <el-row :gutter="20">
                                    <el-col :span="12" v-for="(ite, inde) in item.checked" :key="inde" @click="openFile(ite.url)">
                                        <el-tooltip :content="ite.name" placement="top">
                                            <Icon class="file" name="fa fa-play-circle" />
                                        </el-tooltip>
                                    </el-col>
                                </el-row>
                            </div>
                            <!-- 文件 -->
                            <div class="type" v-if="item.type == 7">
                                <el-row :gutter="20">
                                    <el-col :span="4" v-for="(ite, inde) in item.checked" :key="inde" @click="openFile(ite.url)">
                                        <el-tooltip :content="ite.name" placement="top">
                                            <Icon class="file" name="fa fa-file-pdf-o" v-if="ite.type == 'pdf'" />
                                            <Icon class="file" name="fa fa-file-word-o" v-if="ite.type == 'word'" />
                                            <Icon class="file" name="fa fa-file-excel-o" v-if="ite.type == 'excel'" />
                                            <Icon class="file" name="fa fa-file-zip-o" v-if="ite.type == 'zip'" />
                                            <Icon class="file" name="fa fa-file-text-o" v-if="ite.type == 'file'" />
                                        </el-tooltip>
                                    </el-col>
                                </el-row>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </el-scrollbar>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject, reactive } from 'vue'
import type baTableClass from '/@/utils/baTable'
const baTable = inject('baTable') as baTableClass
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

import { lookApi } from '/@/api/backend/questionnaire/common'
const state = reactive({
    row: [] as anyObj,
    loading: true,
})

const openFile = (url: string) => {
    window.open(url, '_blank')
}

const open = () => {
    state.loading = true
    let id = baTable.form.extend!.id
    lookApi({ id: id })
        .then((res) => {
            state.row = res.data.row
            state.loading = false
        })
        .catch((err) => {
            console.log(err)
        })
}
const close = () => {
    state.row = []
}
</script>

<style scoped lang="scss">
.el-radio-group {
    display: block;
}
.preview {
    margin: 0 10px;
    .top {
        text-align: center;
        margin-top: 10px;
        .title {
            font-size: 18px;
            font-weight: 800;
        }
        .desc {
            margin-top: 5px;
            color: #73767a;
        }
    }
    .questions {
        margin-top: 10px;
        .item {
            .options {
                margin-left: 15px;
                .type {
                    margin: 8px 0px;
                    .file {
                        background: rgb(0 0 0 / 5%);
                        padding: 30px 25px;
                        border-radius: 2px;
                        cursor: pointer;
                    }
                    .mt10 {
                        margin-top: 10px;
                    }
                    :deep(.el-image) {
                        width: 100px !important;
                        height: 100px !important;
                    }
                }
            }
            .title {
                display: flex;
                align-items: baseline;
            }
        }
    }
}
</style>
