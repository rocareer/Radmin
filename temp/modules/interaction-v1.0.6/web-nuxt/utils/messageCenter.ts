import { render } from 'vue'
import { getMessageCount } from '~/api/interaction'

let renderFailCount = 0
let renderTimer: NodeJS.Timeout
let fetchCountTimer: NodeJS.Timeout

export function fetchMessage() {
    if (import.meta.server) return
    clearTimeout(fetchCountTimer)
    getMessageCount().then((res) => {
        if (res.data.count) {
            renderNewMessageMark(res.data.count)
        } else {
            document.querySelector('.header-new-message-mark')?.remove()
            document.querySelector('.header-new-message-mark-count')?.remove()
        }
        const pollingInterval = parseInt(res.data.pollingInterval)
        if (pollingInterval) {
            fetchCountTimer = setTimeout(() => {
                fetchMessage()
            }, pollingInterval * 1000)
        }
    })
}

export function renderNewMessageMark(count: number) {
    clearTimeout(renderTimer)
    const headerUserBox = document.querySelector('.header-user-box')
    const messageCenter = document.querySelector('.messageCenter')
    if (headerUserBox && messageCenter) {
        renderFailCount = 0
        render(
            h(
                'span',
                {
                    style: {
                        position: 'absolute',
                        right: '-12px',
                        color: 'var(--el-color-error)',
                        'font-size': '25px',
                    },
                    class: 'header-new-message-mark',
                },
                '•'
            ),
            headerUserBox
        )
        render(
            h(
                'div',
                {
                    style: {
                        'font-size': 'var(--el-font-size-extra-small)',
                        background: 'var(--el-color-error)',
                        width: '18px',
                        height: '18px',
                        'border-radius': '50%',
                        display: 'inline-flex',
                        'justify-content': 'center',
                        'align-items': 'center',
                        color: 'var(--el-color-white)',
                        position: 'relative',
                        top: '-0.5em',
                        'white-space': 'nowrap',
                    },
                    class: 'header-new-message-mark-count',
                },
                h('span', count > 9 ? '9+' : count)
            ),
            messageCenter
        )
    } else {
        renderFailCount++
        if (renderFailCount > 3) {
            clearTimeout(fetchCountTimer)
            return
        }
        renderTimer = setTimeout(() => {
            renderNewMessageMark(count)
        }, 1000)
    }
}
