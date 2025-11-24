import createAxios from '/@/utils/axios'

const controllerUrl = '/admin/cms.Comment/'

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
