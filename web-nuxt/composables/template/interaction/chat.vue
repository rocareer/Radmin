<template>
    <div>
        <el-button v-blur @click="onChat">{{ userInfo.id == parseInt(user.id!) ? t('user.card.messageCenter') : t('user.card.Chat') }}</el-button>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { User } from '~/types/userCard'

const { t } = useI18n()
const router = useRouter()
const userInfo = useUserInfo()

interface Props {
    user?: Partial<User>
}

const props = withDefaults(defineProps<Props>(), {
    user: () => {
        return {}
    },
})

const onChat = () => {
    if (parseInt(props.user.id!) == userInfo.id) {
        router.push({ name: 'messageCenter' })
    } else {
        router.push({ name: 'messageCenter', query: { id: props.user.id } })
    }
}
</script>

<style scoped lang="scss"></style>
