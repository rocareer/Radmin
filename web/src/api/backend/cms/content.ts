import createAxios from '/@/utils/axios'

const controllerUrl = '/admin/cms.Content/'

export function fields(id: string) {
    return createAxios({
        url: controllerUrl + 'fields',
        method: 'get',
        params: {
            id: id,
        },
    })
}

export function statusChange(ids: string[], status: string) {
    return createAxios(
        {
            url: controllerUrl + 'status',
            method: 'post',
            params: {
                ids,
                status,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}
