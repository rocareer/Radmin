<template>
  <div class="tencent-cloud-container">
    <el-row :gutter="20">
      <!-- 文件上传区域 -->
      <el-col :span="12">
        <el-card class="upload-card">
          <template #header>
            <div class="card-header">
              <span>文件上传</span>
            </div>
          </template>
          
          <el-upload
            class="upload-demo"
            drag
            :action="uploadUrl"
            :before-upload="beforeUpload"
            :on-success="onUploadSuccess"
            :on-error="onUploadError"
            :show-file-list="false"
            accept=".doc,.docx,.pdf,.txt,.md"
          >
            <el-icon class="el-icon--upload"><upload-filled /></el-icon>
            <div class="el-upload__text">
              将文件拖到此处，或<em>点击上传</em>
            </div>
            <template #tip>
              <div class="el-upload__tip">
                支持 doc/docx/pdf/txt/md 格式，文件大小不超过 50MB
              </div>
            </template>
          </el-upload>

          <div class="upload-progress" v-if="uploadProgress > 0">
            <el-progress :percentage="uploadProgress" :status="uploadStatus" />
          </div>
        </el-card>
      </el-col>

      <!-- 同步管理区域 -->
      <el-col :span="12">
        <el-card class="sync-card">
          <template #header>
            <div class="card-header">
              <span>内容同步</span>
              <el-button type="primary" size="small" @click="showSyncDialog = true">
                同步内容到腾讯云
              </el-button>
            </div>
          </template>
          
          <div class="sync-stats">
            <el-row :gutter="16">
              <el-col :span="8">
                <div class="stat-item">
                  <div class="stat-value">{{ syncStats.total }}</div>
                  <div class="stat-label">总同步数</div>
                </div>
              </el-col>
              <el-col :span="8">
                <div class="stat-item">
                  <div class="stat-value success">{{ syncStats.success }}</div>
                  <div class="stat-label">同步成功</div>
                </div>
              </el-col>
              <el-col :span="8">
                <div class="stat-item">
                  <div class="stat-value error">{{ syncStats.failed }}</div>
                  <div class="stat-label">同步失败</div>
                </div>
              </el-col>
            </el-row>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 上传任务列表 -->
    <el-card class="task-list-card" style="margin-top: 20px;">
      <template #header>
        <div class="card-header">
          <span>上传任务</span>
          <el-button type="text" @click="refreshUploadList">
            <Icon name="fa fa-refresh" />
            刷新
          </el-button>
        </div>
      </template>
      
      <el-table
        v-loading="uploadListLoading"
        :data="uploadList"
        border
        stripe
      >
        <el-table-column prop="task_name" label="任务名称" min-width="150" />
        <el-table-column prop="file_name" label="文件名" min-width="200" show-overflow-tooltip />
        <el-table-column prop="file_size_formatted" label="文件大小" width="120" align="center" />
        <el-table-column prop="upload_status_text" label="状态" width="100" align="center">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.upload_status)">
              {{ scope.row.upload_status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="upload_progress" label="进度" width="120" align="center">
          <template #default="scope">
            <el-progress :percentage="scope.row.upload_progress" :stroke-width="6" />
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180" align="center" />
        <el-table-column label="操作" width="150" align="center">
          <template #default="scope">
            <el-button 
              v-if="scope.row.upload_url" 
              type="primary" 
              text 
              @click="openUrl(scope.row.upload_url)"
            >
              查看文档
            </el-button>
            <el-button 
              v-if="scope.row.upload_status === 3" 
              type="danger" 
              text 
              @click="retryUpload(scope.row)"
            >
              重试
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 同步记录列表 -->
    <el-card class="sync-list-card" style="margin-top: 20px;">
      <template #header>
        <div class="card-header">
          <span>同步记录</span>
          <el-button type="text" @click="refreshSyncList">
            <Icon name="fa fa-refresh" />
            刷新
          </el-button>
        </div>
      </template>
      
      <el-table
        v-loading="syncListLoading"
        :data="syncList"
        border
        stripe
      >
        <el-table-column prop="local_content.title" label="本地内容" min-width="200" show-overflow-tooltip />
        <el-table-column prop="tencent_doc_title" label="腾讯云文档" min-width="200" show-overflow-tooltip />
        <el-table-column prop="sync_type_text" label="同步类型" width="120" align="center" />
        <el-table-column prop="sync_status_text" label="同步状态" width="120" align="center">
          <template #default="scope">
            <el-tag :type="getSyncStatusType(scope.row.sync_status)">
              {{ scope.row.sync_status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="last_sync_time" label="最后同步时间" width="180" align="center" />
        <el-table-column label="操作" width="150" align="center">
          <template #default="scope">
            <el-button 
              v-if="scope.row.tencent_doc_url" 
              type="primary" 
              text 
              @click="openUrl(scope.row.tencent_doc_url)"
            >
              查看文档
            </el-button>
            <el-button 
              v-if="scope.row.sync_status === 3" 
              type="danger" 
              text 
              @click="retrySync(scope.row)"
            >
              重试
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 同步对话框 -->
    <el-dialog v-model="showSyncDialog" title="同步内容到腾讯云" width="500px">
      <el-form :model="syncForm" label-width="100px">
        <el-form-item label="选择内容">
          <el-select
            v-model="syncForm.content_id"
            placeholder="请选择要同步的内容"
            style="width: 100%"
            filterable
            remote
            :remote-method="searchContents"
            :loading="contentSearchLoading"
          >
            <el-option
              v-for="item in contentOptions"
              :key="item.id"
              :label="item.title"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showSyncDialog = false">取消</el-button>
        <el-button type="primary" @click="submitSync" :loading="syncSubmitting">确定同步</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import { UploadFilled } from '@element-plus/icons-vue'
import Icon from '/@/components/icon/index.vue'

defineOptions({
  name: 'kb/tencentCloud',
})

const { t } = useI18n()

// 上传相关
const uploadUrl = '/admin/kb.TencentCloud/upload'
const uploadProgress = ref(0)
const uploadStatus = ref('')

// 上传任务列表
const uploadList = ref([])
const uploadListLoading = ref(false)

// 同步相关
const showSyncDialog = ref(false)
const syncSubmitting = ref(false)
const syncForm = reactive({
  content_id: ''
})
const contentOptions = ref([])
const contentSearchLoading = ref(false)

// 同步记录列表
const syncList = ref([])
const syncListLoading = ref(false)

// 同步统计
const syncStats = reactive({
  total: 0,
  success: 0,
  failed: 0
})

// 文件上传前验证
const beforeUpload = (file: File) => {
  const allowedTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'text/plain', 'text/markdown']
  const allowedExtensions = ['.doc', '.docx', '.pdf', '.txt', '.md']
  const extension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase()
  
  if (!allowedExtensions.includes(extension)) {
    ElMessage.error('只支持 doc/docx/pdf/txt/md 格式的文件')
    return false
  }
  
  if (file.size > 50 * 1024 * 1024) {
    ElMessage.error('文件大小不能超过 50MB')
    return false
  }
  
  uploadProgress.value = 0
  uploadStatus.value = ''
  return true
}

// 上传成功
const onUploadSuccess = (response: any) => {
  if (response.code === 1) {
    ElMessage.success('文件上传成功')
    uploadProgress.value = 100
    uploadStatus.value = 'success'
    refreshUploadList()
  } else {
    ElMessage.error(response.msg || '上传失败')
    uploadStatus.value = 'exception'
  }
}

// 上传失败
const onUploadError = () => {
  ElMessage.error('上传失败')
  uploadStatus.value = 'exception'
}

// 获取状态类型
const getStatusType = (status: number) => {
  const types = { 0: 'info', 1: 'warning', 2: 'success', 3: 'danger' }
  return types[status] || 'info'
}

// 获取同步状态类型
const getSyncStatusType = (status: number) => {
  const types = { 0: 'info', 1: 'warning', 2: 'success', 3: 'danger' }
  return types[status] || 'info'
}

// 刷新上传列表
const refreshUploadList = async () => {
  uploadListLoading.value = true
  try {
    // 这里应该调用API获取上传列表
    // const res = await api.getUploadList()
    // uploadList.value = res.data.list
    uploadList.value = []
  } catch (error) {
    ElMessage.error('获取上传列表失败')
  } finally {
    uploadListLoading.value = false
  }
}

// 刷新同步列表
const refreshSyncList = async () => {
  syncListLoading.value = true
  try {
    // 这里应该调用API获取同步列表
    // const res = await api.getSyncList()
    // syncList.value = res.data.list
    syncList.value = []
  } catch (error) {
    ElMessage.error('获取同步列表失败')
  } finally {
    syncListLoading.value = false
  }
}

// 搜索内容
const searchContents = async (query: string) => {
  if (query.length < 2) return
  
  contentSearchLoading.value = true
  try {
    // 这里应该调用API搜索内容
    // const res = await api.searchContents({ keyword: query })
    // contentOptions.value = res.data
    contentOptions.value = []
  } catch (error) {
    ElMessage.error('搜索内容失败')
  } finally {
    contentSearchLoading.value = false
  }
}

// 提交同步
const submitSync = async () => {
  if (!syncForm.content_id) {
    ElMessage.error('请选择要同步的内容')
    return
  }
  
  syncSubmitting.value = true
  try {
    // 这里应该调用API提交同步
    // const res = await api.syncToTencent({ content_id: syncForm.content_id })
    // if (res.code === 1) {
    //   ElMessage.success('同步任务已创建')
    //   showSyncDialog.value = false
    //   refreshSyncList()
    // } else {
    //   ElMessage.error(res.msg)
    // }
    ElMessage.success('同步任务已创建')
    showSyncDialog.value = false
  } catch (error) {
    ElMessage.error('同步失败')
  } finally {
    syncSubmitting.value = false
  }
}

// 打开链接
const openUrl = (url: string) => {
  window.open(url, '_blank')
}

// 重试上传
const retryUpload = (row: any) => {
  // 实现重试上传逻辑
  ElMessage.info('重试上传功能开发中...')
}

// 重试同步
const retrySync = (row: any) => {
  // 实现重试同步逻辑
  ElMessage.info('重试同步功能开发中...')
}

onMounted(() => {
  refreshUploadList()
  refreshSyncList()
})
</script>

<style scoped>
.tencent-cloud-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.upload-card,
.sync-card,
.task-list-card,
.sync-list-card {
  margin-bottom: 20px;
}

.upload-progress {
  margin-top: 20px;
}

.sync-stats {
  margin-top: 20px;
}

.stat-item {
  text-align: center;
  padding: 20px;
  background: #f5f7fa;
  border-radius: 8px;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 8px;
}

.stat-value.success {
  color: #67c23a;
}

.stat-value.error {
  color: #f56c6c;
}

.stat-label {
  font-size: 14px;
  color: #909399;
}

.upload-demo {
  width: 100%;
}
</style>