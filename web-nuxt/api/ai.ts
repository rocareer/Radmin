const controllerUrl = '/api/ai/'

/**
 * AI初始化
 */
export function chatInit() {
    return Http.$fetch({
        url: controllerUrl + 'init',
        method: 'get',
    })
}

/**
 * 操作会话
 */
export function sessionOperate(data: anyObj) {
    return Http.$fetch({
        url: controllerUrl + 'sessionOperate',
        method: 'post',
        body: data,
    })
}

/**
 * 聊天记录
 */
export function records(id: number, page = 1, unexpectedRecords = 0) {
    return Http.$fetch({
        url: controllerUrl + 'records',
        method: 'get',
        params: { id, page, unexpectedRecords },
    })
}

/**
 * 停止生成后检查是否有会话缓存
 */
export function checkStop(data: anyObj) {
    return Http.$fetch({
        url: controllerUrl + 'checkStop',
        method: 'post',
        body: data,
    })
}

/**
 * 计算大概的token数量
 */
export function calcTokens(text: string) {
    return Http.$fetch({
        url: controllerUrl + 'calcTokens',
        method: 'post',
        body: { text },
    })
}

export function exChange(method: 'get' | 'post', postData: anyObj = {}) {
    const options: anyObj = {
        url: controllerUrl + 'exChange',
        method: method,
    }
    if (method == 'post') options.body = postData
    return Http.$fetch(options, {
        showSuccessMessage: method == 'post' ? true : false,
    })
}
