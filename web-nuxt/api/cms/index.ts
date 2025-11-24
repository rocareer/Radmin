const indexUrl = '/api/cms.Index/'

export function init() {
    return Http.fetch({
        url: indexUrl + 'init',
        method: 'get',
    })
}

export function index() {
    return Http.fetch({
        url: indexUrl + 'index',
        method: 'get',
    })
}

export function getNewPublish(page: number) {
    return Http.$fetch({
        url: indexUrl + 'getNewPublish',
        method: 'get',
        params: {
            page,
        },
    })
}

export function applyFriendlyLink(data: any) {
    return Http.$fetch(
        {
            url: indexUrl + 'applyFriendlyLink',
            method: 'post',
            body: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 文章列表、频道、搜索等
 */
export function articleList(query: anyObj) {
    return Http.fetch({
        url: indexUrl + 'articleList',
        method: 'get',
        query,
    })
}

/**
 * 获取搜索页数据（非搜索）
 */
export function search(params: anyObj = {}) {
    return Http.fetch({
        url: indexUrl + 'search',
        method: 'get',
        params,
    })
}
