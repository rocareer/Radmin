const contentUrl = '/api/cms/content/'

export function info(id: string) {
    return Http.fetch({
        url: contentUrl + 'info',
        method: 'get',
        params: {
            id: id,
        },
    })
}

export function like(id: string) {
    return Http.$fetch(
        {
            url: contentUrl + 'like',
            method: 'post',
            body: {
                id: id,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function collect(id: string) {
    return Http.$fetch(
        {
            url: contentUrl + 'collect',
            method: 'post',
            body: {
                id: id,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function publishComment(id: string, content: string, atUser: string[]) {
    return Http.$fetch(
        {
            url: contentUrl + 'comment',
            method: 'post',
            body: {
                id,
                content,
                atUser,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function loadComments(id: string, page: number) {
    return Http.$fetch({
        url: contentUrl + 'loadComments',
        method: 'get',
        params: {
            id: id,
            page: page,
        },
    })
}
