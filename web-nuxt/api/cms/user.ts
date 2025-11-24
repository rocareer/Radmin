const indexUrl = '/api/cms.User/'

/**
 * 操作【收藏、点赞、浏览】记录
 */
export function operateRecord(params: anyObj = {}) {
    return Http.$fetch({
        url: indexUrl + 'operateRecord',
        method: 'get',
        params: params,
    })
}

export function order(params: anyObj = {}) {
    return Http.$fetch({
        url: indexUrl + 'order',
        method: 'get',
        params: params,
    })
}

export function comment(params: anyObj = {}) {
    return Http.$fetch({
        url: indexUrl + 'comment',
        method: 'get',
        params: params,
    })
}

export function content(params: anyObj = {}) {
    return Http.$fetch({
        url: indexUrl + 'content',
        method: 'get',
        params: params,
    })
}

export function delContent(id: string) {
    return Http.$fetch({
        url: indexUrl + 'delContent',
        method: 'get',
        params: {
            id: id,
        },
    })
}

export function buyLog(params: anyObj) {
    return Http.$fetch({
        url: indexUrl + 'buyLog',
        method: 'get',
        params: params,
    })
}

export function contributeChannel() {
    return Http.$fetch({
        url: indexUrl + 'contributeChannel',
        method: 'get',
    })
}

export function getContribute(params: anyObj) {
    return Http.$fetch({
        url: indexUrl + 'contribute',
        method: 'get',
        params: params,
    })
}

export function postContribute(body: anyObj) {
    return Http.$fetch(
        {
            url: indexUrl + 'contribute',
            method: 'post',
            body: body,
        },
        {
            showSuccessMessage: true,
        }
    )
}
