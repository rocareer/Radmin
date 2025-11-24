import createAxios from '/@/utils/axios'

const controllerUrl = '/admin/cms.Config/'

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
        },
        {
            showSuccessMessage: true,
        }
    )
}
