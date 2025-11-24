export default defineNuxtRouteMiddleware((to) => {
    const userInfo = useUserInfo()
    
    // 检查用户是否已登录
    if (!userInfo.isLogin() && !to.meta.noNeedLogin) {
        return navigateTo({ name: 'userLogin' })
    }
    
    // 检查Token是否即将过期，如果是则尝试刷新
    if (userInfo.isLogin() && userInfo.shouldRefreshToken()) {
        // 这里可以添加刷新Token的逻辑
        // 由于Nuxt的限制，这里可能需要通过API调用实现
    }
})
