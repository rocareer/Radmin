<template>
    <div class="card-content-style-a">
        <NuxtLink
            :to="props.data.id ? { name: 'cmsInfo', params: { id: props.data.id } } : undefined"
            :href="!props.data.id ? props.data.url : undefined"
            :target="props.data.id ? '_self' : props.data.target"
        >
            <el-image fit="cover" :src="getFirstImage(props.data.images!)" :alt="props.data.seotitle ?? props.data.title" loading="lazy">
                <template #placeholder>
                    <div class="img-loading-placeholder">
                        <Loading />
                    </div>
                </template>
            </el-image>
        </NuxtLink>
        <div class="style-a-title" :style="calcTitleStyle(data.title_style)">{{ props.data.title }}</div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    data: {
        id?: number
        title?: string
        title_style?: {
            color: string
            bold: boolean
        }
        images?: string | string[]
        url?: string
        target?: '_blank' | '_self' | '_top' | '_parent'
        seotitle?: string
        [key: string]: any
    }
    height?: string
    titleHeight?: string
    round?: string
}

const props = withDefaults(defineProps<Props>(), {
    data: () => {
        return {}
    },
    height: '175px',
    titleHeight: '36px',
    round: '0',
})

const titleBottom = computed(() => {
    return props.height == 'auto' ? '3px' : '0'
})
</script>

<style scoped lang="scss">
.card-content-style-a {
    position: relative;
    height: v-bind(height);
    cursor: pointer;
    .el-image {
        width: 100%;
        height: 100%;
        min-height: 60px;
        border-radius: v-bind(round);
    }
    .style-a-title {
        display: block;
        position: absolute;
        bottom: v-bind(titleBottom);
        width: 100%;
        height: v-bind(titleHeight);
        line-height: v-bind(titleHeight);
        background-color: rgba($color: #000000, $alpha: 0.3);
        color: #ffffff;
        padding: 0 10px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border-bottom-right-radius: v-bind(round);
        border-bottom-left-radius: v-bind(round);
    }
}
</style>
