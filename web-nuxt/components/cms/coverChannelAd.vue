<!-- 封面频道顶部轮播图和焦点图 -->
<template>
    <div>
        <el-row class="cover-carousel" :gutter="10">
            <el-col :md="12" :xs="24">
                <el-carousel height="360px">
                    <el-carousel-item v-for="(item, idx) in props.data.carousel" :key="idx">
                        <div class="carousel-img-item suspension">
                            <a :href="item.link" :target="item.target">
                                <el-image fit="cover" loading="lazy" :src="getFirstImage(item.image)" :alt="item.title">
                                    <template #placeholder>
                                        <div class="img-loading-placeholder">
                                            <Loading />
                                        </div>
                                    </template>
                                </el-image>
                            </a>
                            <div class="hot-title">{{ item.title }}</div>
                        </div>
                    </el-carousel-item>
                </el-carousel>
            </el-col>
            <el-col class="cover-hot" :md="6" :xs="12">
                <CmsBackgroundCard
                    v-for="(item, idx) in data.focus.slice(0, 2)"
                    :key="idx"
                    class="cover-hot-item suspension"
                    :data="{ title: item.title, images: item.image, url: item.link, target: item.target }"
                />
            </el-col>
            <el-col class="cover-hot" :md="6" :xs="12">
                <CmsBackgroundCard
                    v-for="(item, idx) in data.focus.slice(2)"
                    :key="idx"
                    class="cover-hot-item suspension"
                    :data="{ title: item.title, images: item.image, url: item.link, target: item.target }"
                />
            </el-col>
        </el-row>
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
.cover-carousel {
    .carousel-img-item {
        position: relative;
        cursor: pointer;
        width: 100%;
        height: 100%;
        .hot-title {
            display: block;
            width: 100%;
            position: absolute;
            bottom: 26px;
            color: #ffffff;
            text-align: center;
        }
        .el-image {
            width: 100%;
            height: 100%;
        }
    }
    .cover-hot {
        .cover-hot-item:last-child {
            margin-top: 10px;
        }
    }
}
@media screen and (min-width: 768px) and (max-width: 1000px) {
    .products-hot {
        display: none;
    }
}
</style>
