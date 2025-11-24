<template>
    <div class="product-item suspension">
        <NuxtLink :to="{ name: 'cmsInfo', params: { id: props.article.id } }">
            <div class="image-box">
                <el-image fit="cover" loading="lazy" class="product-img" :src="getFirstImage(article.images)">
                    <template #placeholder>
                        <div class="img-loading-placeholder">
                            <Loading color="var(--el-color-info-light-7)" />
                        </div>
                    </template>
                </el-image>
            </div>
            <div class="product-info-box">
                <div class="product-title" :style="calcTitleStyle(article.title_style)">{{ article.title }}</div>
                <div class="product-desc">
                    {{ article.description }}
                </div>
                <div class="product-other">
                    <span class="tag-item"> <i class="fa fa-eye"></i> {{ article.views }} </span>
                    <span class="tag-item"> <i class="fa fa-thumbs-up"></i> {{ article.likes }} </span>
                    <el-button class="product-other-button" type="primary" size="small" plain>查看详情</el-button>
                </div>
            </div>
        </NuxtLink>
    </div>
</template>

<script setup lang="ts">
interface Props {
    article: anyObj
}
const props = withDefaults(defineProps<Props>(), {
    article: () => [],
})
</script>

<style scoped lang="scss">
.product-item {
    border: 1px solid var(--el-border-color-extra-light);
    border-radius: var(--el-border-radius-small);
    margin-bottom: 10px;
    cursor: pointer;
    a {
        text-decoration: none;
    }
    .image-box {
        width: 100%;
        height: 0;
        position: relative;
        padding-bottom: 75%;
    }
    .product-img {
        width: 100%;
        height: 100%;
        min-height: 120px;
        object-fit: cover;
        object-position: center;
        position: absolute;
        border-top-right-radius: var(--el-border-radius-small);
        border-top-left-radius: var(--el-border-radius-small);
    }
    .product-info-box {
        .product-title {
            display: block;
            font-size: 16px;
            color: var(--el-text-color-regular);
            padding: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .product-desc {
            font-size: 13px;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            line-height: 18px;
            height: 36px;
            color: var(--el-color-info);
            margin: 8px;
            margin-top: 0;
        }
        .product-other {
            display: flex;
            align-items: center;
            padding: 0 8px 12px 8px;
            color: var(--el-color-info-light-5);
            height: 26px;
            .tag-item {
                margin-right: 8px;
            }
            .product-other-button {
                margin-left: auto;
                display: none;
            }
        }
    }
}
.product-item:hover {
    .product-other-button {
        display: block !important;
    }
}
</style>
