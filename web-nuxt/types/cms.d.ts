interface ArticleListData {
    list?: any[]
    total?: number
    info?: anyObj
    title?: string
    template?: string
    filterConfig?: any[]
    [key: string]: any
}

interface ArticleInfoData {
    template?: string
    content?: anyObj
    breadCrumbs?: anyObj[]
    hotContent?: anyObj[]
    prevArticle?: anyObj
    nextArticle?: anyObj
    comments?: anyObj
    interactionInstalled?: boolean
}
