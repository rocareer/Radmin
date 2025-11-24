import { CSSProperties } from 'vue'
import { isArray } from 'lodash-es'
import noPic from '~/assets/images/cms/no-pic.jpg'

export const infoBreadcrumbTo = (item: anyObj) => {
    return { name: 'cmsChannel', params: { value: item.type == 'search' ? item.name : item.id, type: item.type == 'channel' ? '' : item.type } }
}

export const calcTitleStyle = (titleStyle: { color: string; bold: boolean } = { color: '#ffffff', bold: false }): CSSProperties => {
    return {
        color: titleStyle.color ?? '#ffffff',
        fontWeight: titleStyle.bold ? 'bold' : 'normal',
    }
}

export const getFirstImage = (images: string | string[]) => {
    let url = ''
    if (typeof images == 'string') {
        url = images
    } else if (isArray(images) && images.length > 0) {
        url = images[0]
    }
    return url ? fullUrl(url) : noPic
}

export function toQQzone(url: string, title: string) {
    url = encodeURIComponent(url)
    title = encodeURIComponent(title)
    window.open(`https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=${url}&title=${title}&desc=${title}&summary=${title}&site=${url}`)
}

export function toQQ(url: string, title: string) {
    url = encodeURIComponent(url)
    title = encodeURIComponent(title)
    window.open(`https://connect.qq.com/widget/shareqq/index.html?url=${url}&title=${title}&source=${url}&desc=${title}&pics=`)
}

export function toWeibo(url: string, title: string) {
    url = encodeURIComponent(url)
    title = encodeURIComponent(title)
    window.open(`https://service.weibo.com/share/share.php?url=${url}&title=${title}&pic=&appkey=&sudaref=`)
}
