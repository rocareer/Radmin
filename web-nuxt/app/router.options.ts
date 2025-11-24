// app/router.options.ts 文件，没有请自行建立
import type { RouterOptions } from '@nuxt/schema'
import { cloneDeep } from 'lodash-es'
import { RouteRecordRaw } from 'vue-router'

/**
 * 修改路由 path
 */
const editPath = (routes: RouteRecordRaw[], oldPath: string) => {
    for (const key in routes) {
        if (routes[key].path.includes(oldPath)) {
            if (routes[key].path === oldPath) {
                // 将 oldPath 路由改为首页
                routes[key].path = '/'
                routes[key].name = '/'
            } else {
                // 子路由修改
                routes[key].path = routes[key].path.replace(oldPath + '/', '/')
            }
        }

        if (routes[key].children?.length) {
            routes[key].children = editPath(routes[key].children!, oldPath)
        }
    }
    return routes
}

// https://router.vuejs.org/api/interfaces/routeroptions.html
export default <RouterOptions>{
    routes: (routes) => {
        let routesTemp = cloneDeep(routes as Writeable<RouteRecordRaw[]>)

        // 对原首页的 path 和 name 进行修改
        const oldIndex = getArrayKey(routesTemp, 'path', '/')
        routesTemp[oldIndex].path = '/home'
        routesTemp[oldIndex].name = '/home'

        // 修改所有静态路由数据中的 /cms
        routesTemp = editPath(routesTemp, '/cms')

        // 社区模块仅需调整第二个参数
        // routesTemp = editPath(routesTemp, '/ask')

        return routesTemp
    },
}
