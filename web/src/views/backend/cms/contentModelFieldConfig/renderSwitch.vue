<template>
    <div>
        <div v-if="isSupport(props.renderField.prop!)">-</div>
        <el-switch
            v-else
            @change="onChangeField($event, props.renderField.prop!)"
            :model-value="props.renderValue.toString()"
            :loading="loading"
            active-value="1"
            inactive-value="0"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue'
import type baTableClass from '/@/utils/baTable'
import { notSupportPublish, notSupportShow, notSupportSortable, notSupportComSearch, notSupportContribute } from '/@/views/backend/cms/content/helper'

const baTable = inject('baTable') as baTableClass

interface Props {
    renderValue: any // 单元格值
    renderRow: TableRow // 当前行数据
    renderField: TableColumn
    renderIndex: number // 当前行号
}
const props = defineProps<Props>()
const loading = ref(false)

const onChangeField = (value: any, fieldName: string) => {
    loading.value = true
    baTable.api
        .postData('edit', { [baTable.table.pk!]: props.renderRow[baTable.table.pk!], [props.renderField.prop!]: value })
        .then(() => {
            baTable.table.data![props.renderIndex][fieldName] = value
        })
        .finally(() => {
            loading.value = false
        })
}

const isSupport = (prop: string) => {
    if (prop == 'backend_publish') {
        return notSupportPublish.includes(props.renderRow['name'])
    } else if (prop == 'backend_show') {
        return notSupportShow.includes(props.renderRow['name'])
    } else if (prop == 'backend_com_search') {
        return notSupportComSearch.includes(props.renderRow['name'])
    } else if (prop == 'backend_sort') {
        return notSupportSortable.includes(props.renderRow['name'])
    } else if (prop == 'frontend_contribute') {
        return notSupportContribute.includes(props.renderRow['name'])
    }
}
</script>

<style scoped lang="scss"></style>
