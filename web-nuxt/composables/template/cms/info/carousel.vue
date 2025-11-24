<template>
    <div>
        <DefaultVue :data="props.data" :carousel="true" />
    </div>
</template>

<script setup lang="ts">
import DefaultVue from './default.vue'

interface Props {
    data: ArticleInfoData
}
const props = withDefaults(defineProps<Props>(), {
    data: () => {
        return {}
    },
})
</script>

<style scoped lang="scss"></style>
