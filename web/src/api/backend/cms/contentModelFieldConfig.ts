import createAxios from '/@/utils/axios'

const controllerUrl = '/admin/cms.ContentModel/'

export function info(id: string) {
    return createAxios({
        url: controllerUrl + 'info',
        method: 'get',
        params: {
            id: id,
        },
    })
}
