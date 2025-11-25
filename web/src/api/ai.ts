import createAxios from '/@/utils/axios'

const controllerUrl = '/api/ai/'

/**
 * AI初始化
 */
export function chatInit() {
    return createAxios({
        url: controllerUrl + 'init',
        method: 'get',
    })
}

/**
 * 操作会话
 */
export function sessionOperate(data: anyObj) {
    return createAxios({
        url: controllerUrl + 'sessionOperate',
        method: 'post',
        data: data,
    })
}

/**
 * 聊天记录
 */
export function records(id: number, page = 1, unexpectedRecords = 0) {
    return createAxios({
        url: controllerUrl + 'records',
        method: 'get',
        params: { id, page, unexpectedRecords },
    })
}

/**
 * 停止生成后检查是否有会话缓存
 */
export function checkStop(data: anyObj) {
    return createAxios({
        url: controllerUrl + 'checkStop',
        method: 'post',
        data: data,
    })
}

/**
 * 计算大概的token数量
 */
export function calcTokens(text: string) {
    return createAxios({
        url: controllerUrl + 'calcTokens',
        method: 'post',
        data: { text },
    })
}
