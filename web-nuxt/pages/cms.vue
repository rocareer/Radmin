<template>
    <div>
        <NuxtPage />
    </div>
</template>

<script setup lang="ts">
import { init } from '~/api/cms/index'
const cmsConfig = useCmsConfig()
const { data } = await init()
if (data.value?.code == 1) {
    cmsConfig.dataFill(data.value.data.config)
}

definePageMeta({
    layout: 'cms',
    name: 'cms',
})
</script>

<style scoped lang="scss"></style>
