<template>
    <div>
        <CmsCoverChannelTitle :data="props.data" :params="params" />

        <!-- 轮播图和焦点图 -->
        <CmsCoverChannelAd :data="data" :params="params" />

        <!-- 产品列表 -->
        <div class="products" v-for="(content, idx) in data.content" :key="idx">
            <el-card class="product-card">
                <template #header>
                    <div class="product-card-title-box">
                        <div class="product-card-title">{{ content.info.name }}</div>
                        <div class="product-card-more">
                            <NuxtLink :to="{ name: 'cmsChannel', params: { value: content.info.id } }"> 查看全部 </NuxtLink>
                        </div>
                    </div>
                </template>
                <el-row :gutter="10">
                    <el-col v-for="(product, productIdx) in content.contentList" :key="productIdx" :xs="12" :sm="12" :md="6">
                        <CmsBigPictureCard :article="product" />
                    </el-col>
                </el-row>
            </el-card>
        </div>

        <!-- 友情链接 -->
        <CmsFriendlyLink class="friendly-link" />
    </div>
</template>

<script setup lang="ts">
interface Props {
    data: ArticleListData
    params: anyObj
}
const props = withDefaults(defineProps<Props>(), {
    data: () => {
        return {}
    },
    params: () => {
        return {}
    },
})
</script>

<style scoped lang="scss">
.product-card {
    border: none;
    border-radius: 0;
    margin-top: 10px;
    :deep(.el-card__header) {
        padding: 15px;
        border: none;
        border-bottom: 1px solid var(--el-border-color-extra-light);
    }
    :deep(.el-card__body) {
        padding: 10px 15px 5px 15px;
    }
    .product-card-title-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        .product-card-more {
            font-size: var(--el-font-size-base);
            a {
                text-decoration: none;
                color: var(--el-color-info);
            }
            &:hover {
                text-decoration: underline;
            }
        }
    }
}
.friendly-link {
    margin-top: 10px;
}
</style>
