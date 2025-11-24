<template>
    <div class="statistic-item">
        <Icon size="14px" :color="genderInfo.color" :name="genderInfo.name" />
        <span>{{ genderInfo.text }}</span>
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

const genderInfo = computed(() => {
    let icon = { name: 'fa fa-transgender-alt', color: 'var(--el-text-color-secondary)', text: t('user.card.Secrecy') }
    switch (props.user.gender) {
        case 1:
            icon = { name: 'fa fa-mars-stroke-v', color: 'var(--el-color-primary)', text: t('user.card.Male') }
            break
        case 2:
            icon = { name: 'fa fa-mars-stroke', color: 'var(--el-color-danger)', text: t('user.card.Female') }
            break
    }
    return icon
})
</script>

<style scoped lang="scss"></style>
