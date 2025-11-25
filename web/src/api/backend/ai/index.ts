import createAxios from '/@/utils/axios'

const controllerUrl = '/admin/ai.Config/'
const kbsContentControllerUrl = '/admin/ai.KbsContent/'

export function embeddings(ids: string[]) {
    return createAxios(
        {
            url: kbsContentControllerUrl + 'embeddings',
            method: 'post',
            data: { ids },
            timeout: 0,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function search(method: 'get' | 'post', data: anyObj = {}) {
    const body: anyObj = {}
    if (method == 'get') {
        body['params'] = data
    } else {
        body['data'] = data
    }

    return createAxios({
        url: kbsContentControllerUrl + 'search',
        method: method,
        timeout: 0,
        ...body,
    })
}

/**
 * 重建redis索引
 */
export function indexSet() {
    return createAxios(
        {
            url: kbsContentControllerUrl + 'indexSet',
            method: 'get',
            timeout: 0,
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 检查redis缓存，没有缓存的则立即缓存
 */
export function checkCache(ids: string[] = []) {
    return createAxios(
        {
            url: kbsContentControllerUrl + 'checkCache',
            method: 'post',
            timeout: 0,
            data: { ids },
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function generateQa(model: string, content: string) {
    return createAxios({
        url: kbsContentControllerUrl + 'generateQa',
        method: 'post',
        data: { model, content },
        timeout: 0,
    })
}

export function getFileContent(file: string) {
    return createAxios({
        url: kbsContentControllerUrl + 'getFileContent',
        method: 'get',
        params: { file },
    })
}

export function getUrlContent(url: string) {
    return createAxios({
        url: kbsContentControllerUrl + 'getUrlContent',
        method: 'get',
        params: { url },
    })
}

export function batchAdd(data: anyObj) {
    return createAxios(
        {
            url: kbsContentControllerUrl + 'batchAdd',
            method: 'post',
            data: data,
            timeout: 0,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function index() {
    return createAxios({
        url: controllerUrl + 'index',
        method: 'get',
    })
}

export function save(data: any) {
    return createAxios(
        {
            url: controllerUrl + 'save',
            method: 'post',
            data,
            timeout: 0,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function clear() {
    return createAxios(
        {
            url: controllerUrl + 'clear',
            method: 'post',
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function getModelData() {
    return createAxios({
        url: '/admin/ai.ChatModel/getModelData',
        method: 'get',
    })
}
