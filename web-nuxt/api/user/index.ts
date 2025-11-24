const accountUrl = '/api/account/'
const userUrl = '/user/Index/'

export function login(method: 'get' | 'post', params: object = {}) {
    const opt = method == 'get' ? {} : { body: params }
    return Http.$fetch({
        url: userUrl + 'login',
        method: method,
        ...opt,
    })
}

export function overview() {
    return Http.fetch({
        url: accountUrl + 'overview',
        method: 'get',
    })
}

export function getProfile() {
    return Http.fetch({
        url: accountUrl + 'profile',
        method: 'get',
    })
}

export function getIntegralLog(page: number, pageSize: number) {
    return Http.$fetch({
        url: accountUrl + 'integral',
        method: 'GET',
        params: {
            page: page,
            limit: pageSize,
        },
    })
}

export function getBalanceLog(page: number, pageSize: number) {
    return Http.$fetch({
        url: accountUrl + 'balance',
        method: 'GET',
        params: {
            page: page,
            limit: pageSize,
        },
    })
}

export function postLogout() {
    const userInfo = useUserInfo()
    return Http.$fetch({
        url: userUrl + 'logout',
        method: 'POST',
        body: {
            refresh_token: userInfo.getToken('refresh'),
        },
    })
}

export function retrievePassword(params: anyObj) {
    return Http.$fetch(
        {
            url: accountUrl + 'retrievePassword',
            method: 'POST',
            body: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function postProfile(params: anyObj) {
    return Http.$fetch(
        {
            url: accountUrl + 'profile',
            method: 'POST',
            body: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function postVerification(data: anyObj) {
    return Http.$fetch({
        url: accountUrl + 'verification',
        method: 'post',
        body: data,
    })
}

export function postChangeBind(data: anyObj) {
    return Http.$fetch(
        {
            url: accountUrl + 'changeBind',
            method: 'post',
            body: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function changePassword(params: anyObj) {
    return Http.$fetch(
        {
            url: accountUrl + 'changePassword',
            method: 'POST',
            body: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}
