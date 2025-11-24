<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.extend!.showPreview"
        @close="baTable.form.extend!.showPreview = false"
        width="30%"
        @open="open"
        @closed="close"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ t('questionnaire.examination.preview') }}-{{ t('questionnaire.examination.preview_tip') }}
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
                                <el-radio-group v-model="item.select">
                                    <div v-for="(ite, inde) in item.options" :key="inde">
                                        <el-radio :value="inde">{{ ite }}</el-radio>
                                    </div>
                                </el-radio-group>
                            </div>
                            <!-- 多选题 -->
                            <div class="type" v-if="item.type == 1">
                                <div v-for="(ite, inde) in item.options" :key="inde">
                                    <el-checkbox :value="inde">{{ ite }}</el-checkbox>
                                </div>
                            </div>
                            <!-- 填空题 -->
                            <div class="type" v-if="item.type == 2">
                                <div>
                                    <el-input v-model="item.select" :placeholder="t('questionnaire.examination.please_input')" />
                                </div>
                            </div>
                            <!-- 简答题 -->
                            <div class="type" v-if="item.type == 3">
                                <div>
                                    <el-input
                                        type="textarea"
                                        :rows="4"
                                        v-model="item.select"
                                        :placeholder="t('questionnaire.examination.please_input')"
                                    />
                                </div>
                            </div>
                            <!-- 下拉框 -->
                            <div class="type" v-if="item.type == 4">
                                <div>
                                    <el-select v-model="item.select" :placeholder="t('questionnaire.examination.please_select')">
                                        <el-option v-for="(ite, inde) in item.options" :key="inde" :label="ite" :value="ite" />
                                    </el-select>
                                </div>
                            </div>
                            <!-- 图片 -->
                            <div class="type" v-if="item.type == 5">
                                <el-row :gutter="20">
                                    <el-col :span="5">
                                        <Icon class="file" name="fa fa-photo" />
                                    </el-col>
                                </el-row>
                            </div>
                            <!-- 视频 -->
                            <div class="type" v-if="item.type == 6">
                                <el-row :gutter="20">
                                    <el-col :span="12">
                                        <Icon class="file" name="fa fa-play-circle" />
                                    </el-col>
                                </el-row>
                            </div>
                            <!-- 文件 -->
                            <div class="type" v-if="item.type == 7">
                                <el-row :gutter="20">
                                    <el-col :span="4">
                                        <Icon class="file" name="fa fa-file-text-o" />
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
import { previewApi } from '/@/api/backend/questionnaire/common'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const state = reactive({
    row: [] as anyObj,
    loading: true,
})

const open = () => {
    state.loading = true
    let id = baTable.form.extend!.id
    previewApi({ id: id })
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
