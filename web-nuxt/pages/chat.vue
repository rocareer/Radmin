<template>
    <div>
        <el-container class="is-vertical">
            <BaHeader />
            <el-main class="chat-main">
                <el-row class="h100" justify="center">
                    <el-col class="h100" :xs="24" :sm="18">
                        <AiChat />
                    </el-col>
                </el-row>
            </el-main>
            <BaFooter />
        </el-container>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    layout: false,
    name: 'ai/chat',
})
</script>

<style scoped lang="scss">
.chat-main {
    padding: 20px 0;
    height: calc(100vh - 120px);
}
</style>
