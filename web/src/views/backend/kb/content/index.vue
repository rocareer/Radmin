<template>
    <div class="kb-content-container">
        <div class="table-container">
            <div class="table-header">
                <div class="header-left">
                    <h2 class="title">知识库内容管理</h2>
                    <p class="description">管理知识库中的文章、文档等内容</p>
                </div>
                <div class="header-right">
                    <el-button type="primary" @click="handleAdd" :icon="Plus">
                        添加内容
                    </el-button>
                    <el-button @click="refresh" :icon="Refresh">刷新</el-button>
                </div>
            </div>

            <!-- 搜索区域 -->
            <div class="search-container">
                <el-form :model="searchForm" inline>
                    <el-form-item label="关键词">
                        <el-input
                            v-model="searchForm.keyword"
                            placeholder="请输入标题/内容/关键词"
                            clearable
                            @keyup.enter="handleSearch"
                        />
                    </el-form-item>
                    <el-form-item label="类型">
                        <el-select
                            v-model="searchForm.type_id"
                            placeholder="请选择类型"
                            clearable
                        >
                            <el-option
                                v-for="item in typeOptions"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="分类">
                        <el-select
                            v-model="searchForm.category_id"
                            placeholder="请选择分类"
                            clearable
                        >
                            <el-option
                                v-for="item in categoryOptions"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="状态">
                        <el-select
                            v-model="searchForm.status"
                            placeholder="请选择状态"
                            clearable
                        >
                            <el-option label="草稿" :value="0" />
                            <el-option label="发布" :value="1" />
                            <el-option label="审核中" :value="2" />
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="handleSearch" :icon="Search">
                            搜索
                        </el-button>
                        <el-button @click="resetSearch" :icon="Refresh">重置</el-button>
                    </el-form-item>
                </el-form>
            </div>

            <!-- 表格区域 -->
            <div class="table-wrapper">
                <el-table
                    :data="tableData"
                    v-loading="loading"
                    stripe
                    border
                    @sort-change="handleSortChange"
                >
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="id" label="ID" width="80" sortable="custom" />
                    <el-table-column prop="title" label="标题" min-width="200" show-overflow-tooltip>
                        <template #default="{ row }">
                            <div class="title-cell">
                                <span v-if="row.is_top" class="top-tag">置顶</span>
                                <span v-if="row.is_recommend" class="recommend-tag">推荐</span>
                                <span class="title-text">{{ row.title }}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="type.name" label="类型" width="120" />
                    <el-table-column prop="category.name" label="分类" width="120" />
                    <el-table-column prop="views" label="浏览量" width="100" sortable="custom" />
                    <el-table-column prop="likes" label="点赞数" width="100" sortable="custom" />
                    <el-table-column prop="status" label="状态" width="100">
                        <template #default="{ row }">
                            <el-tag
                                :type="getStatusType(row.status)"
                                size="small"
                            >
                                {{ getStatusText(row.status) }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="publish_time" label="发布时间" width="160" sortable="custom">
                        <template #default="{ row }">
                            {{ formatTime(row.publish_time) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" label="创建时间" width="160" sortable="custom">
                        <template #default="{ row }">
                            {{ formatTime(row.create_time) }}
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="200" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                type="primary"
                                link
                                size="small"
                                @click="handleView(row)"
                                :icon="View"
                            >
                                查看
                            </el-button>
                            <el-button
                                type="warning"
                                link
                                size="small"
                                @click="handleEdit(row)"
                                :icon="Edit"
                            >
                                编辑
                            </el-button>
                            <el-button
                                type="danger"
                                link
                                size="small"
                                @click="handleDelete(row)"
                                :icon="Delete"
                            >
                                删除
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <!-- 分页区域 -->
            <div class="pagination-container">
                <el-pagination
                    v-model:current-page="pagination.current"
                    v-model:page-size="pagination.size"
                    :page-sizes="[10, 20, 50, 100]"
                    :total="pagination.total"
                    layout="total, sizes, prev, pager, next, jumper"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                />
            </div>
        </div>

        <!-- 添加/编辑弹窗 -->
        <content-form
            v-model="formVisible"
            :form-data="currentForm"
            :type-options="typeOptions"
            :category-options="categoryOptions"
            @success="handleFormSuccess"
        />

        <!-- 查看弹窗 -->
        <content-detail
            v-model="detailVisible"
            :content-id="currentId"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Refresh, Search, View, Edit, Delete } from '@element-plus/icons-vue'
import ContentForm from './popupForm.vue'
import ContentDetail from './detail.vue'

// 数据类型定义
interface ContentItem {
    id: number
    type_id: number
    category_id: number
    title: string
    content: string
    keywords: string
    summary: string
    author: string
    source: string
    views: number
    likes: number
    status: number
    is_top: number
    is_recommend: number
    publish_time: number
    create_time: number
    update_time: number
    type?: {
        id: number
        name: string
    }
    category?: {
        id: number
        name: string
    }
}

interface SearchForm {
    keyword: string
    type_id: number | null
    category_id: number | null
    status: number | null
}

interface Pagination {
    current: number
    size: number
    total: number
}

// 响应式数据
const loading = ref(false)
const tableData = ref<ContentItem[]>([])
const typeOptions = ref<any[]>([])
const categoryOptions = ref<any[]>([])
const formVisible = ref(false)
const detailVisible = ref(false)
const currentForm = ref<Partial<ContentItem>>({})
const currentId = ref<number>(0)

const searchForm = reactive<SearchForm>({
    keyword: '',
    type_id: null,
    category_id: null,
    status: null
})

const pagination = reactive<Pagination>({
    current: 1,
    size: 20,
    total: 0
})

// 获取数据
const fetchData = async () => {
    loading.value = true
    try {
        const params = {
            page: pagination.current,
            limit: pagination.size,
            ...searchForm
        }
        
        // 调用API获取数据
        const { data } = await $fetch('/api/kb/content', { params })
        if (data?.code === 1) {
            tableData.value = data.data.list || []
            pagination.total = data.data.total || 0
        } else {
            ElMessage.error(data?.msg || '获取数据失败')
        }
    } catch (error) {
        console.error('获取数据失败:', error)
        ElMessage.error('网络请求失败')
    } finally {
        loading.value = false
    }
}

// 获取类型和分类选项
const fetchOptions = async () => {
    try {
        // 获取类型选项
        const typeRes = await $fetch('/api/kb/type/select')
        if (typeRes?.data?.code === 1) {
            typeOptions.value = typeRes.data.data || []
        }
        
        // 获取分类选项
        const categoryRes = await $fetch('/api/kb/category/select')
        if (categoryRes?.data?.code === 1) {
            categoryOptions.value = categoryRes.data.data || []
        }
    } catch (error) {
        console.error('获取选项失败:', error)
    }
}

// 搜索处理
const handleSearch = () => {
    pagination.current = 1
    fetchData()
}

const resetSearch = () => {
    Object.assign(searchForm, {
        keyword: '',
        type_id: null,
        category_id: null,
        status: null
    })
    handleSearch()
}

// 分页处理
const handleSizeChange = (size: number) => {
    pagination.size = size
    pagination.current = 1
    fetchData()
}

const handleCurrentChange = (current: number) => {
    pagination.current = current
    fetchData()
}

// 排序处理
const handleSortChange = ({ prop, order }: any) => {
    // 处理排序逻辑
    console.log('排序:', prop, order)
}

// 操作处理
const handleAdd = () => {
    currentForm.value = {}
    formVisible.value = true
}

const handleEdit = (row: ContentItem) => {
    currentForm.value = { ...row }
    formVisible.value = true
}

const handleView = (row: ContentItem) => {
    currentId.value = row.id
    detailVisible.value = true
}

const handleDelete = async (row: ContentItem) => {
    try {
        await ElMessageBox.confirm(
            `确定要删除内容"${row.title}"吗？`,
            '提示',
            {
                type: 'warning',
                confirmButtonText: '确定',
                cancelButtonText: '取消'
            }
        )
        
        // 调用删除API
        const { data } = await $fetch(`/api/kb/content/${row.id}`, {
            method: 'DELETE'
        })
        
        if (data?.code === 1) {
            ElMessage.success('删除成功')
            fetchData()
        } else {
            ElMessage.error(data?.msg || '删除失败')
        }
    } catch (error) {
        // 用户取消删除
    }
}

// 表单成功回调
const handleFormSuccess = () => {
    formVisible.value = false
    fetchData()
}

// 工具函数
const getStatusType = (status: number) => {
    const types = { 0: 'info', 1: 'success', 2: 'warning' }
    return types[status as keyof typeof types] || 'info'
}

const getStatusText = (status: number) => {
    const texts = { 0: '草稿', 1: '发布', 2: '审核中' }
    return texts[status as keyof typeof texts] || '未知'
}

const formatTime = (timestamp: number) => {
    if (!timestamp) return '-'
    return new Date(timestamp * 1000).toLocaleString('zh-CN')
}

const refresh = () => {
    fetchData()
}

// 生命周期
onMounted(() => {
    fetchData()
    fetchOptions()
})
</script>

<style scoped lang="scss">
.kb-content-container {
    padding: 20px;
    
    .table-container {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            
            .header-left {
                .title {
                    margin: 0 0 8px 0;
                    font-size: 20px;
                    font-weight: 600;
                    color: #303133;
                }
                
                .description {
                    margin: 0;
                    font-size: 14px;
                    color: #909399;
                }
            }
        }
        
        .search-container {
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            
            :deep(.el-form-item) {
                margin-bottom: 0;
            }
        }
        
        .table-wrapper {
            margin-bottom: 20px;
            
            .title-cell {
                display: flex;
                align-items: center;
                gap: 8px;
                
                .top-tag {
                    background: #f56c6c;
                    color: #fff;
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-size: 12px;
                }
                
                .recommend-tag {
                    background: #e6a23c;
                    color: #fff;
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-size: 12px;
                }
                
                .title-text {
                    flex: 1;
                }
            }
        }
        
        .pagination-container {
            display: flex;
            justify-content: flex-end;
        }
    }
}

@media (max-width: 768px) {
    .kb-content-container {
        padding: 10px;
        
        .table-container {
            padding: 15px;
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                
                .header-right {
                    width: 100%;
                    display: flex;
                    gap: 10px;
                    
                    .el-button {
                        flex: 1;
                    }
                }
            }
            
            .search-container {
                :deep(.el-form) {
                    .el-form-item {
                        width: 100%;
                        margin-right: 0;
                        margin-bottom: 15px;
                    }
                }
            }
        }
    }
}
</style>