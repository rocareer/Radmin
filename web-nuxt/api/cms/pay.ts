const indexUrl = '/api/cms.Pay/'

export function create(params: anyObj = {}) {
    return Http.$fetch({
        url: indexUrl + 'create',
        method: 'get',
        params: params,
    })
}

export function check(params: anyObj = {}) {
    return Http.$fetch({
        url: indexUrl + 'check',
        method: 'get',
        params: params,
    })
}
