<!-- 内容详情 -->
<template>
    <div>
        <component v-if="state.data.template" :data="state.data" :is="templates[state.data.template]" />
    </div>
</template>

<script setup lang="ts">
import { info } from '~/api/cms/content'

definePageMeta({
    name: 'cmsInfo',
    path: '/cms/info/:id',
})

const state: {
    data: ArticleInfoData
} = reactive({
    data: {
        template: '',
    },
})

const route = useRoute()
const { data } = await info(route.params.id as string)
if (data.value?.code == 1) {
    state.data = data.value.data
    if (state.data.content) {
        const meta = []
        if (state.data.content.keywords) {
            meta.push({ name: 'keywords', content: state.data.content.keywords })
        }
        if (state.data.content.description) {
            meta.push({ name: 'description', content: state.data.content.description })
        }
        useHead({
            title: state.data.content.title || state.data.content.seotitle,
            meta: meta,
        })
    }
}

const templates: Record<string, Component> = {}
const templateComponents = import.meta.glob('~/composables/template/cms/info/**.vue')
for (const key in templateComponents) {
    const res: any = await templateComponents[key]()
    const fileName = key.replace('/composables/template/cms/info/', '').replace('.vue', '')
    templates[fileName] = res.default
}
</script>

<style scoped lang="scss"></style>
