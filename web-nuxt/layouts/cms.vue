<template>
    <div>
        <el-container class="is-vertical">
            <BaHeader />
            <el-row class="layouts-main" justify="center">
                <el-col class="layouts-main-col" :span="16" :xs="24">
                    <el-main class="layout-main">
                        <slot />
                    </el-main>
                </el-col>
            </el-row>
            <BaFooter />
        </el-container>
    </div>
</template>

<script setup lang="ts">
import '~/assets/scss/editor.scss'
</script>

<style scoped lang="scss">
.layout-main {
    padding: 0 !important;
    overflow-x: hidden;
}
</style>

<style>
.img-loading-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
