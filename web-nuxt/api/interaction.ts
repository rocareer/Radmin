export const interactionUrl = '/api/interaction/'

export function getMessageCount() {
    return Http.$fetch({
        url: interactionUrl + 'count',
        method: 'get',
    })
}

export function getMessageList(params: anyObj) {
    return Http.$fetch({
        url: interactionUrl + 'messageList',
        method: 'get',
        params: params,
    })
}

export function postMarkRead(ids: string) {
    return Http.$fetch({
        url: interactionUrl + 'markRead',
        method: 'post',
        body: {
            ids,
        },
    })
}

export function loadDialog(params: anyObj) {
    return Http.$fetch({
        url: interactionUrl + 'dialog',
        method: 'get',
        params: params,
    })
}

export function postSendMessage(body: anyObj) {
    return Http.$fetch({
        url: interactionUrl + 'sendMessage',
        method: 'post',
        body: body,
    })
}

export function postDelMessage(ids: string[]) {
    return Http.$fetch({
        url: interactionUrl + 'delMessage',
        method: 'post',
        body: {
            ids,
        },
    })
}

export function loadMoreUserRecent(params: anyObj) {
    return Http.$fetch({
        url: interactionUrl + 'loadMoreUserRecent',
        method: 'get',
        params: params,
    })
}

export function userCard(params: anyObj) {
    return Http.fetch({
        url: interactionUrl + 'userCard',
        method: 'get',
        params: params,
    })
}
