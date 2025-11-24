/**
 * 1. 从API获取新消息数
 * 2. 渲染新消息红点时防止组件尚未渲染完成做了计时器，尝试渲染3次后放弃
 * 3. 每隔 xx 秒重新获取一次新消息数
 */
export default defineNuxtPlugin(() => {
    const router = useRouter()
    router.beforeEach(() => {
        debounce(fetchMessage, 500)()
    })
})
