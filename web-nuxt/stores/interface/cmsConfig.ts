export interface CmsConfig {
    // 首页SEO标题
    index_seo_title: string
    // 首页SEO关键词
    index_seo_keywords: string
    // 首页SEO描述
    index_seo_description: string
    // 内容标记语言
    content_language: string
    // 评论标记语言
    comment_language: string
    // 是否开启内容赞赏功能
    appreciation: 'enable' | 'disable'
    // 右侧栏数据
    right_sidebar: any[]
    // 友情链接数据
    friendly_link: any[]
}
