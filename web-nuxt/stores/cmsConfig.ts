import { CmsConfig } from '~/stores/interface/cmsConfig'

export const useCmsConfig = defineStore('CmsConfig', {
    state: (): CmsConfig => {
        return {
            index_seo_title: '',
            index_seo_keywords: '',
            index_seo_description: '',
            content_language: '',
            comment_language: '',
            appreciation: 'disable',
            right_sidebar: [],
            friendly_link: [],
        }
    },
    actions: {
        dataFill(state: CmsConfig) {
            this.$state = state
        },
    },
})
