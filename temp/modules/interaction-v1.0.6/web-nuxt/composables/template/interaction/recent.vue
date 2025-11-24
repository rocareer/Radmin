<template>
    <div>
        <el-timeline class="timeline">
            <el-timeline-item v-for="(recent, index) in state.data" :key="index" placement="top" :timestamp="recent.create_time">
                <span class="ba-recent-content" v-html="recent.content"></span>
            </el-timeline-item>
            <el-timeline-item v-if="!state.nextPage" placement="top" :timestamp="props.user.join_time">
                <span>{{ $t('user.card.Joined us') }}</span>
            </el-timeline-item>
        </el-timeline>
        <div v-if="state.loading" class="recent-loading" v-loading="true"></div>
    </div>
</template>

<script setup lang="ts">
import { loadMoreUserRecent } from '~/api/interaction'
import { User } from '~/types/userCard'

interface Props {
    user?: Partial<User>
    item?: anyObj
}

const props = withDefaults(defineProps<Props>(), {
    user: () => {
        return {}
    },
    item: () => {
        return {}
    },
})

const state: {
    page: number
    limit: number
    loading: boolean
    nextPage: boolean
    data: anyObj[]
} = reactive({
    page: 1,
    limit: 10,
    loading: false,
    nextPage: true,
    data: props.item.data || [],
})

state.nextPage = props.item.data.length < state.limit ? false : true

onMounted(() => {
    const scrollEl = document.querySelectorAll('.ba-user-card-scrollbar .el-scrollbar__wrap')
    scrollEl[0].addEventListener('scroll', () => {
        if (scrollEl[0].scrollTop + scrollEl[0].clientHeight >= scrollEl[0].scrollHeight && state.nextPage) {
            if (state.loading) return
            state.page++
            state.loading = true
            loadMoreUserRecent({
                page: state.page,
                limit: state.limit,
                userId: props.user.id,
            })
                .then((res) => {
                    if (res.data.data.length < state.limit) {
                        state.nextPage = false
                    }
                    state.data = state.data.concat(res.data.data)
                })
                .finally(() => {
                    state.loading = false
                })
        }
    })
})
</script>

<style scoped lang="scss">
.recent-loading {
    height: 80px;
}
.timeline {
    margin: 2px;
}
</style>

<style lang="scss">
.ba-recent-content {
    a {
        color: var(--el-color-primary);
    }
    a:hover {
        color: var(--el-color-success);
    }
    code {
        color: #3594f7;
        background-color: #3baafa1a;
        display: inline-block;
        padding: 0 4px;
        border-radius: 2px;
        line-height: 22px;
    }
    img {
        margin: 0 auto;
        max-width: 30%;
        box-sizing: border-box;
        padding: 5px;
        border: 1px solid #e6e6e6;
        border-radius: 3px;
    }
}
</style>
