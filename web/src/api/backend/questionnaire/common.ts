import createAxios from '/@/utils/axios'

//预览
export function previewApi(data: anyObj) {
    return createAxios(
        {
            url: '/admin/questionnaire.examination/preview',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: false,
        }
    )
}

//查阅
export function lookApi(data: anyObj) {
    return createAxios(
        {
            url: '/admin/questionnaire.answer_sheet/look',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: false,
        }
    )
}

//分析-单，多选题
export function analyseApi(data: anyObj) {
    return createAxios(
        {
            url: '/admin/questionnaire.answer/analyse',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: false,
        }
    )
}

//分析-填空题
export function gapApi(data: anyObj) {
    return createAxios(
        {
            url: '/admin/questionnaire.answer/gap',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: false,
        }
    )
}

/**
 * 获取配置信息
 */
export function getConfig() {
    return createAxios({
        url: '/admin/questionnaire.Config/getConfig',
        method: 'get',
    })
}

/**
 * 编辑配置信息
 */
export function saveConfig(type: string, data: anyObj) {
    return createAxios(
        {
            url: '/admin/questionnaire.Config/saveConfig',
            method: 'post',
            params: {
                type: type,
            },
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

//获取二维码
export function getQrCodeApi(data: anyObj) {
    return createAxios(
        {
            url: '/admin/questionnaire.examination/getQrCode',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: false,
        }
    )
}

//获取二维码
export function updareQrCodeApi(data: anyObj) {
    return createAxios(
        {
            url: '/admin/questionnaire.examination/updateQrCode',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
