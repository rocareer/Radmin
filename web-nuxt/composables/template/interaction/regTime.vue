<template>
    <div class="statistic-item">
        <Icon size="14px" color="var(--el-text-color-placeholder)" name="el-icon-Calendar" />
        <span>{{ t('user.card.Registration in') }}{{ props.user.join_time }}</span>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { User } from '~/types/userCard'
const { t } = useI18n()

interface Props {
    user?: Partial<User>
}

const props = withDefaults(defineProps<Props>(), {
    user: () => {
        return {}
    },
})
</script>

<style scoped lang="scss"></style>
