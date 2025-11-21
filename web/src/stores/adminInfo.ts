import { defineStore } from 'pinia'
import router from '../router'
import { postLogout } from '/@/api/frontend/user'
import { ADMIN_INFO } from '/@/stores/constant/cacheKey'
import type { AdminInfo } from '/@/stores/interface'
import { Local } from '/@/utils/storage'

export const useAdminInfo = defineStore('adminInfo', {
    state: (): AdminInfo => {
        return {
            id: 0,
            username: '',
            nickname: '',
            avatar: '',
            last_login_time: '',
            token: '',
            refresh_token: '',
            super: false,
            role: 'admin', // 明确标识角色类型
        }
    },
    actions: {
        /**
         * 状态批量填充
         * @param state 新状态数据
         * @param [exclude=true] 是否排除某些字段（忽略填充），默认值 true 排除 token 和 refresh_token，传递 false 则不排除，还可传递 string[] 指定排除字段列表
         */
        dataFill(state: Partial<AdminInfo>, exclude: boolean | string[] = true) {
            if (exclude === true) {
                exclude = ['token', 'refresh_token']
            } else if (exclude === false) {
                exclude = []
            }

            if (Array.isArray(exclude)) {
                exclude.forEach((item) => {
                    delete state[item as keyof AdminInfo]
                })
            }

            this.$patch(state)
        },
        removeToken() {
            this.token = ''
            this.refresh_token = ''
        },
        setToken(token: string, type: 'auth' | 'refresh') {
            const field = type == 'auth' ? 'token' : 'refresh_token'
            this[field] = token
        },
        getToken(type: 'auth' | 'refresh' = 'auth') {
            return type === 'auth' ? this.token : this.refresh_token
        },
        /**
         * 检查Token是否即将过期（前端检查）
         * @param threshold 提前刷新时间阈值（秒）
         */
        shouldRefreshToken(threshold: number = 300): boolean {
            if (!this.token) return false

            try {
                // 解析JWT Token获取过期时间
                const payload = JSON.parse(atob(this.token.split('.')[1]))
                const currentTime = Math.floor(Date.now() / 1000)
                const timeRemaining = payload.exp - currentTime

                return timeRemaining > 0 && timeRemaining < threshold
            } catch (error) {
                console.error('Token解析失败:', error)
                return false
            }
        },
        setSuper(val: boolean) {
            this.super = val
        },
        logout() {
            postLogout().then((res) => {
                if (res.code == 1) {
                    Local.remove(ADMIN_INFO)
                    router.go(0)
                }
            })
        },
        /**
         * 检查是否登录
         */
        isLogin() {
            return this.id > 0 && this.token !== ''
        },
        /**
         * 完全清除状态
         */
        clear() {
            this.$reset()
        },
    },
    persist: {
        key: ADMIN_INFO,
    },
})
