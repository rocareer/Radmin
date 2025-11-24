import { markRaw } from 'vue'
import { __ } from '/@/utils/common'
import Title from './renderTitle.vue'
import Tags from './renderTags.vue'
import { isEmpty } from 'lodash-es'

export function onClickTitle(row: TableRow) {
    if (row['url']) window.open(row['url'])
}

/**
 * 不可以开启公共搜索的字段
 */
export const notSupportComSearch = ['channel_ids', 'title_style', 'images', 'content', 'description']

/**
 * 不可以开启排序的字段
 */
export const notSupportSortable = ['channel_ids', 'title_style', 'images', 'content', 'description', 'tags', 'url', 'currency']

/**
 * 不可以开启显示的字段
 */
export const notSupportShow = ['channel_ids', 'title_style', 'content', 'description', 'currency']

/**
 * 不可以开启发布的字段
 */
export const notSupportPublish = ['create_time', 'update_time', 'id']

/**
 * 不可以开启投稿的字段
 */
export const notSupportContribute = [
    'create_time',
    'update_time',
    'target',
    'url',
    'views',
    'comments',
    'likes',
    'dislikes',
    'allow_comment_groups',
    'allow_visit_groups',
    'publish_time',
    'memo',
    'status',
    'weigh',
    'admin_id',
    'user_id',
    'id',
]

/**
 * 表格列基本数据
 * 模型表格的列数据是根据配置动态组装的
 */
export const columnBaseData = new Map<string, TableColumn>([
    ['id', { label: __('cms.content.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE' }],
    [
        'user_id',
        {
            label: __('cms.content.user_id'),
            prop: 'user.nickname',
            align: 'center',
            operatorPlaceholder: __('Fuzzy query'),
            render: 'tags',
            operator: 'LIKE',
        },
    ],
    [
        'admin_id',
        {
            label: __('cms.content.admin_id'),
            prop: 'admin.nickname',
            align: 'center',
            operatorPlaceholder: __('Fuzzy query'),
            render: 'tags',
            operator: 'LIKE',
        },
    ],
    [
        'channel_id',
        {
            label: __('cms.content.channel_id'),
            prop: 'cmsChannel.name',
            align: 'center',
            operatorPlaceholder: __('Fuzzy query'),
            render: 'tag',
            operator: 'LIKE',
        },
    ],
    [
        'title',
        {
            label: __('cms.content.title'),
            prop: 'title',
            align: 'center',
            operatorPlaceholder: __('Fuzzy query'),
            operator: 'LIKE',
            width: 200,
            render: 'customRender',
            customRender: markRaw(Title),
        },
    ],
    [
        'flag',
        {
            label: __('cms.content.flag'),
            prop: 'flag',
            align: 'center',
            render: 'tags',
            operator: 'FIND_IN_SET',
            replaceValue: {
                top: __('cms.content.flag top'),
                hot: __('cms.content.flag hot'),
                recommend: __('cms.content.flag recommend'),
                new: __('cms.content.flag new'),
            },
        },
    ],
    [
        'images',
        {
            label: __('cms.content.images'),
            prop: 'images',
            align: 'center',
            render: 'images',
            formatter(row, column, cellValue) {
                return !isEmpty(cellValue) ? [cellValue[0]] : []
            },
            width: 86,
            operator: false,
            sortable: false,
        },
    ],
    [
        'seotitle',
        {
            label: __('cms.content.seotitle'),
            prop: 'seotitle',
            align: 'center',
            operatorPlaceholder: __('Fuzzy query'),
            operator: 'LIKE',
        },
    ],
    [
        'keywords',
        {
            label: __('cms.content.keywords'),
            prop: 'keywords',
            align: 'center',
            operatorPlaceholder: __('Fuzzy query'),
            operator: 'LIKE',
        },
    ],
    [
        'tags',
        {
            label: __('cms.content.tags'),
            prop: 'cmsTags.name',
            align: 'center',
            operator: false,
            render: 'customRender',
            customRender: markRaw(Tags),
        },
    ],
    [
        'tags-com-search',
        {
            show: false,
            enableColumnDisplayControl: false,
            label: __('cms.content.tags'),
            prop: 'tags',
            render: 'tags',
            operator: 'FIND_IN_SET',
            comSearchRender: 'remoteSelect',
            remote: {
                pk: 'tags.id',
                field: 'name',
                remoteUrl: '/admin/cms.Tags/index',
            },
        },
    ],
    [
        'url',
        {
            label: __('cms.content.url'),
            prop: 'url',
            align: 'center',
            render: 'url',
            operatorPlaceholder: __('Fuzzy query'),
            operator: 'LIKE',
        },
    ],
    [
        'target',
        {
            label: __('cms.content.target'),
            prop: 'target',
            align: 'center',
            render: 'tag',
            operator: '=',
            replaceValue: {
                _blank: __('cms.content.target _blank'),
                _self: __('cms.content.target _self'),
                _top: __('cms.content.target _top'),
                _parent: __('cms.content.target _parent'),
            },
        },
    ],
    ['views', { label: __('cms.content.views'), prop: 'views', align: 'center', operator: 'RANGE' }],
    ['comments', { label: __('cms.content.comments'), width: 100, prop: 'comments', align: 'center', operator: 'RANGE' }],
    ['likes', { label: __('cms.content.likes'), width: 100, prop: 'likes', align: 'center', operator: 'RANGE' }],
    ['dislikes', { label: __('cms.content.dislikes'), width: 100, prop: 'dislikes', align: 'center', operator: 'RANGE' }],
    ['weigh', { label: __('cms.content.weigh'), width: 80, prop: 'weigh', align: 'center', operator: 'RANGE' }],
    [
        'price',
        {
            label: __('cms.content.price'),
            render: 'customTemplate',
            customTemplate: (row) => {
                return parseFloat(row['price']) > 0 ? (row['currency'] == 'RMB' ? '人民币' : '积分') + ' <b>' + row['price'] + '</b>' : '-'
            },
            prop: 'price',
            align: 'center',
            operator: 'RANGE',
        },
    ],
    [
        'currency',
        {
            label: __('cms.content.currency'),
            prop: 'currency',
            align: 'center',
            render: 'tag',
            operator: '=',
            replaceValue: { RMB: __('cms.content.currency RMB'), integral: __('cms.content.currency integral') },
        },
    ],
    [
        'allow_visit_groups',
        {
            label: __('cms.content.allow_visit_groups'),
            prop: 'allow_visit_groups',
            align: 'center',
            render: 'tag',
            operator: '=',
            replaceValue: { all: __('cms.content.allow_visit_groups all'), user: __('cms.content.allow_visit_groups user') },
        },
    ],
    [
        'allow_comment_groups',
        {
            label: __('cms.content.allow_comment_groups'),
            prop: 'allow_comment_groups',
            align: 'center',
            render: 'tag',
            operator: '=',
            replaceValue: {
                disable: __('cms.content.allow_comment_groups disable'),
                all: __('cms.content.allow_comment_groups all'),
                user: __('cms.content.allow_comment_groups user'),
            },
        },
    ],
    [
        'status',
        {
            label: __('cms.content.status'),
            prop: 'status',
            align: 'center',
            render: 'tag',
            operator: '=',
            width: 80,
            replaceValue: {
                normal: __('cms.content.status normal'),
                unaudited: __('cms.content.status unaudited'),
                refused: __('cms.content.status refused'),
                offline: __('cms.content.status offline'),
            },
        },
    ],
    [
        'memo',
        {
            label: __('cms.content.memo'),
            prop: 'memo',
            align: 'center',
            operatorPlaceholder: __('Fuzzy query'),
            operator: 'LIKE',
        },
    ],
    [
        'publish_time',
        {
            label: __('cms.content.publish_time'),
            prop: 'publish_time',
            align: 'center',
            render: 'datetime',
            operator: 'RANGE',
            sortable: 'custom',
            width: 160,
            timeFormat: 'yyyy-mm-dd hh:MM:ss',
        },
    ],
    [
        'update_time',
        {
            label: __('cms.content.update_time'),
            prop: 'update_time',
            align: 'center',
            render: 'datetime',
            operator: 'RANGE',
            sortable: 'custom',
            width: 160,
            timeFormat: 'yyyy-mm-dd hh:MM:ss',
        },
    ],
    [
        'create_time',
        {
            label: __('cms.content.create_time'),
            prop: 'create_time',
            align: 'center',
            render: 'datetime',
            operator: 'RANGE',
            sortable: 'custom',
            width: 160,
            timeFormat: 'yyyy-mm-dd hh:MM:ss',
        },
    ],
])
