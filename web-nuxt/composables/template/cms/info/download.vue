<template>
    <div>
        <el-row class="main-row" :gutter="15">
            <el-col :lg="18">
                <el-card class="info-box-card base-card" shadow="never">
                    <template #header>
                        <!-- 面包屑 -->
                        <el-breadcrumb separator="/">
                            <el-breadcrumb-item :to="{ name: 'cmsIndex' }">{{ $t('Home') }}</el-breadcrumb-item>
                            <el-breadcrumb-item
                                :to="{ name: 'cmsChannel', params: { value: item.id } }"
                                v-for="(item, idx) in data.breadCrumbs"
                                :key="idx"
                            >
                                {{ item.name }}
                            </el-breadcrumb-item>
                        </el-breadcrumb>
                    </template>
                    <div class="content-box">
                        <!-- 标题 -->
                        <h1 class="title">{{ state.content.title }}</h1>
                        <div class="other">
                            <div class="other-item"><i class="fa fa-eye"></i> {{ state.content.views }}阅读</div>
                            <div class="other-item"><i class="fa fa-comments"></i> {{ state.content.comments }}评论</div>
                            <div class="other-item"><i class="fa fa-thumbs-o-up"></i> {{ state.content.likes }}点赞</div>
                        </div>

                        <!-- 轮播图 -->
                        <el-carousel v-if="carousel && state.content.images" height="400px" class="images">
                            <el-carousel-item v-for="(img, imIdx) in state.content.images" :key="imIdx">
                                <el-image class="image" :src="img">
                                    <template #placeholder>
                                        <div class="img-loading-placeholder">
                                            <Loading />
                                        </div>
                                    </template>
                                </el-image>
                            </el-carousel-item>
                        </el-carousel>

                        <!-- 内容详情 -->
                        <div
                            v-if="parseFloat(state.content.price) <= 0"
                            v-html="state.content.content"
                            :class="cmsConfig.content_language == 'html' ? 'ba-wang-editor' : 'ba-markdown'"
                            class="content-html"
                        ></div>
                        <div v-else>
                            <CmsBuyContent class="content-html" :data="state.content" />
                        </div>

                        <div class="footer">
                            <div class="statement">
                                <el-alert type="warning" :closable="false">
                                    <template #title>
                                        <client-only>
                                            <div class="link">转载请注明出处：{{ getCurrentUrl() }}</div>
                                        </client-only>
                                        <div>©软件著作权归作者所有。本站所有软件均来源于网络，仅供学习使用，请支持正版！</div>
                                    </template>
                                </el-alert>
                            </div>
                            <!-- 点赞和赞赏 -->
                            <div class="buttons">
                                <el-button v-blur @click="onLike" size="large" :type="state.content.likeed ? '' : 'primary'">
                                    <i class="fa fa-thumbs-up"></i>
                                    <span>{{ state.content.likeed ? '您已赞过' : '点赞' }} ({{ state.content.likes }})</span>
                                </el-button>
                                <el-button v-if="cmsConfig.appreciation == 'enable'" @click="state.appreciation = true" size="large">
                                    <i class="fa fa-cny"></i> 赞赏
                                </el-button>
                            </div>

                            <!-- 分享 -->
                            <div class="share">
                                <div @click="onCollect" class="share-item border-yellow" :class="state.content.collected ? '' : 'uncollected'">
                                    <el-tooltip content="收藏当前内容" effect="light" placement="top">
                                        <img src="~/assets/images/cms/info/collect.png" alt="收藏当前内容" />
                                    </el-tooltip>
                                </div>
                                <div @click="toQQ(getCurrentUrl(), state.content.title)" class="share-item border-red">
                                    <el-tooltip content="分享给QQ好友" effect="light" placement="top">
                                        <img src="~/assets/images/cms/info/QQ.png" alt="分享给QQ好友" />
                                    </el-tooltip>
                                </div>
                                <div @click="toQQzone(getCurrentUrl(), state.content.title)" class="share-item border-yellow">
                                    <el-tooltip content="分享到QQ空间" effect="light" placement="top">
                                        <img src="~/assets/images/cms/info/QQzone.png" alt="分享到QQ空间" />
                                    </el-tooltip>
                                </div>
                                <div @click="toWeibo(getCurrentUrl(), state.content.title)" class="share-item border-red">
                                    <el-tooltip content="分享到微博" effect="light" placement="top">
                                        <img src="~/assets/images/cms/info/Weibo.png" alt="分享到微博" />
                                    </el-tooltip>
                                </div>
                                <client-only>
                                    <div class="share-item border-green">
                                        <el-tooltip effect="light" placement="top">
                                            <template #content>
                                                <h3 :style="{ textAlign: 'center', paddingTop: '10px' }">使用微信扫码分享</h3>
                                                <qrcode-vue :value="getCurrentUrl()" :margin="4" :size="160" level="H" />
                                            </template>
                                            <img src="~/assets/images/cms/info/Wechat.png" alt="分享到微信" />
                                        </el-tooltip>
                                    </div>
                                </client-only>
                            </div>

                            <!-- 文章底部信息 -->
                            <div v-if="!isEmpty(state.content.cmsTags.name)" class="content-info">
                                <div class="info-item">发布日期：{{ timeFormat(state.content.create_time, 'yyyy-mm-dd') }}</div>
                                <div class="info-item">
                                    <span>相关标签：</span>
                                    <NuxtLink
                                        v-for="(tag, tagIdx) in state.content.cmsTags.name"
                                        :key="tagIdx"
                                        :to="{ name: 'cmsChannel', params: { value: tagIdx, type: 'tag' } }"
                                    >
                                        <el-tag>{{ tag }}</el-tag>
                                    </NuxtLink>
                                </div>
                            </div>

                            <!-- 上一篇、下一篇 -->
                            <div v-if="data.nextArticle || data.prevArticle" class="before-after-articles">
                                <div v-if="data.prevArticle" class="article-item before-article">
                                    <el-tag type="info">上一个 ></el-tag>
                                    <div class="content">
                                        <NuxtLink :to="{ name: 'cmsInfo', params: { id: data.prevArticle.id } }">
                                            {{ data.prevArticle.title }}
                                        </NuxtLink>
                                    </div>
                                </div>
                                <div v-if="data.nextArticle" class="article-item after-article">
                                    <el-tag type="info">下一个 ></el-tag>
                                    <div class="content">
                                        <NuxtLink :to="{ name: 'cmsInfo', params: { id: data.nextArticle.id } }">
                                            {{ data.nextArticle.title }}
                                        </NuxtLink>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-card>

                <el-card class="base-card download-card" header="下载" shadow="never">
                    <div v-if="parseFloat(state.content.price) <= 0">
                        <div v-if="state.content.commented">
                            <a v-for="(item, idx) in state.content.downloads" :key="idx" :href="item.value" target="_blank" rel="noopener noreferrer">
                                <el-button type="primary" class="download-button">
                                    {{ item.key }}
                                </el-button>
                            </a>
                        </div>
                        <div v-else>您需要评论后，才能查阅下载链接。</div>
                    </div>
                    <div v-else>
                        <CmsBuyContent :data="state.content" />
                    </div>
                </el-card>

                <!-- 评论 -->
                <CmsComments
                    v-if="data.comments"
                    :id="state.content.id.toString()"
                    :comments="data.comments"
                    :commentCount="state.content.comments"
                    :disable="state.content.allow_comment_groups == 'disable' ? true : false"
                    :callback="onCommentCallback"
                />
            </el-col>
            <el-col :lg="6">
                <el-card v-if="!isEmpty(state.content.author)" class="base-card" shadow="never">
                    <div class="author">
                        <img class="avatar" :src="fullUrl(state.content.author.avatar)" :alt="state.content.author.nickname" />
                        <div class="nickname">{{ state.content.author.nickname }}</div>
                        <div class="motto">{{ state.content.author.motto ? state.content.author.motto : '这家伙很懒，什么也没写~' }}</div>
                        <div v-if="!isEmpty(state.content.author.statistics)" class="author-other">
                            <div class="author-other-item">
                                <div class="author-other-item-title">文章</div>
                                <NuxtLink :to="{ name: 'cmsChannel', params: { value: state.content.user_id, type: 'user' } }">
                                    <div class="author-other-item-value">
                                        {{ state.content.author.statistics.articleCount }}
                                    </div>
                                </NuxtLink>
                            </div>
                            <div class="author-other-item">
                                <div class="author-other-item-title">动态</div>
                                <NuxtLink v-if="data.interactionInstalled" :to="{ name: 'user/card', params: { id: state.content.user_id } }">
                                    <div class="author-other-item-value">
                                        {{ state.content.author.statistics.dynamicCount }}
                                    </div>
                                </NuxtLink>
                                <div v-else @click="onNoInteraction" class="author-other-item-value">
                                    {{ state.content.author.statistics.dynamicCount }}
                                </div>
                            </div>
                            <div class="author-other-item">
                                <div class="author-other-item-title">加入时间</div>
                                <NuxtLink v-if="data.interactionInstalled" :to="{ name: 'user/card', params: { id: state.content.user_id } }">
                                    <div class="author-other-item-value">
                                        {{ state.content.author.statistics.joinTime }}
                                    </div>
                                </NuxtLink>
                                <div v-else @click="onNoInteraction" class="author-other-item-value">
                                    {{ state.content.author.statistics.joinTime }}
                                </div>
                            </div>
                        </div>
                    </div>
                </el-card>
                <CmsRightSideBar />
            </el-col>
        </el-row>
        <CmsAppreciation
            v-model="state.appreciation"
            :object="assembleAppreciationData()"
            :pay-params="{
                object: state.content.id,
            }"
        />
    </div>
</template>

<script setup lang="ts">
import { like, collect } from '~/api/cms/content'
import { ElNotification } from 'element-plus'
import { isEmpty } from 'lodash-es'
import QrcodeVue from 'qrcode.vue'

interface Props {
    data: ArticleInfoData
    carousel?: boolean
}
const props = withDefaults(defineProps<Props>(), {
    data: () => {
        return {}
    },
    carousel: false,
})

const state: {
    comment: string
    content: anyObj
    appreciation: boolean
} = reactive({
    comment: '',
    content: props.data.content!,
    appreciation: false,
})

const router = useRouter()
const cmsConfig = useCmsConfig()

const getCurrentUrl = () => {
    return import.meta.client ? window.location.href : ''
}

const onLike = () => {
    like(state.content.id).then((res) => {
        if (res.code == 1) {
            state.content.likes++
            state.content.likeed = true
        }
    })
}

const onCollect = () => {
    collect(state.content.id).then((res) => {
        state.content.collected = res.data.collected
    })
}

const onNoInteraction = () => {
    ElNotification({
        type: 'error',
        message: '请先于模块市场安装《会员交互扩展》',
    })
}

const assembleAppreciationData = () => {
    const title = '赞赏文章《' + state.content.title + '》'
    if (!isEmpty(state.content.author)) {
        return {
            userInfo: {
                avatar: fullUrl(state.content.author.avatar),
                nickname: state.content.author.nickname,
            },
            title,
        }
    } else {
        return {
            userInfo: {
                avatar: getFirstImage(state.content.images),
                nickname: state.content.title,
            },
            title,
        }
    }
}

const onCommentCallback = () => {
    if (!state.content.commented) {
        setTimeout(() => {
            router.go(0)
        }, 2500)
    }
}
</script>

<style scoped lang="scss">
.base-card {
    border: none;
    border-radius: 0;
    margin-bottom: 10px;
    :deep(.el-card__header) {
        padding: 15px;
        border: none;
    }
    :deep(.el-card__body) {
        padding: 0 15px;
    }
}
.main-row {
    margin-top: 15px;
}
.info-box-card {
    :deep(.el-card__header) {
        border-bottom: 1px solid var(--el-border-color-extra-light);
    }
    .content-box {
        .title {
            margin: 15px 0;
            font-size: 1.65em;
            line-height: 1.45;
            margin-bottom: 5px;
            color: var(--el-text-color-primary);
            font-weight: 500;
        }
        .other {
            display: flex;
            color: var(--el-color-info);
            .other-item {
                padding-right: 15px;
            }
        }
        .images {
            margin-top: 15px;
            .image {
                object-fit: cover;
                object-position: center;
                width: 100%;
                min-height: 400px;
            }
        }
        .content-html {
            margin-top: 15px;
            :deep(img) {
                max-width: 100%;
            }
        }
        .footer {
            margin-top: 20px;
            .statement {
                margin-bottom: 10px;
            }
            .buttons {
                display: flex;
                width: 100%;
                justify-content: center;
                i {
                    margin-right: 5px;
                }
            }
            .share {
                display: flex;
                justify-content: center;
                margin: 20px 0;
                .share-item {
                    border-radius: 50%;
                    padding: 6px;
                    width: 40px;
                    height: 40px;
                    cursor: pointer;
                    margin-right: 10px;
                    img {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
            .content-info {
                .info-item {
                    margin-bottom: 8px;
                    .el-tag {
                        margin-right: 6px;
                        cursor: pointer;
                    }
                    .el-tag:hover {
                        background-color: var(--el-color-primary);
                        color: var(--el-color-white);
                    }
                }
            }
            .before-after-articles {
                border-top: 1px solid var(--el-border-color-extra-light);
                .article-item {
                    display: flex;
                    align-items: center;
                    margin: 10px 0;
                    .content {
                        margin-left: 10px;
                    }
                    a {
                        color: var(--el-color-primary);
                        text-decoration: none;
                        &:hover {
                            text-decoration: underline;
                        }
                    }
                }
            }
        }
    }
}

.hot-tags-card {
    margin-top: 10px;
    .el-tag {
        margin: 0 10px 10px 0;
        cursor: pointer;
    }
    .el-tag:hover {
        background-color: var(--el-color-primary);
        color: var(--el-color-white);
    }
}
.border-yellow {
    border: 1px solid var(--el-color-warning-light-5);
}
.border-red {
    border: 1px solid var(--el-color-danger-light-5);
}
.border-green {
    border: 1px solid var(--el-color-success-light-5);
}
.uncollected {
    filter: grayscale(100%);
    border: 1px solid var(--el-color-info-light-5);
}
.author {
    display: block;
    .avatar {
        display: block;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 20px auto;
    }
    .nickname {
        text-align: center;
        font-size: var(--el-font-size-large);
        font-weight: bold;
        margin-bottom: 6px;
    }
    .motto {
        text-align: center;
        margin-bottom: 6px;
    }
    .author-other {
        padding: 5px 0;
        a {
            text-decoration: none;
            color: var(--el-text-color-primary);
        }
        .author-other-item {
            float: left;
            width: 33.3%;
            margin-bottom: 10px;
            &:hover {
                .author-other-item-value {
                    text-decoration: underline;
                    color: var(--el-color-primary);
                }
            }
            .author-other-item-title {
                font-size: var(--el-font-size-small);
                text-align: center;
                color: var(--el-color-info);
                line-height: 26px;
            }
            .author-other-item-value {
                text-align: center;
                line-height: 26px;
                font-weight: bold;
                cursor: pointer;
                user-select: none;
            }
        }
    }
}
.download-card {
    :deep(.el-card__body) {
        padding: 15px;
        padding-top: 0;
    }
    .download-button {
        margin-right: 10px;
    }
}
</style>
