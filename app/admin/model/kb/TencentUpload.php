<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 腾讯云知识库上传任务模型
 */
class TencentUpload extends BaseModel
{
    // 表名
    protected $name = 'kb_tencent_upload';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 字段类型
    protected array $type = [
        'file_size' => 'integer',
        'upload_status' => 'integer',
        'upload_progress' => 'integer',
    ];

    // 关联管理员
    public function admin()
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

    /**
     * 获取上传状态文本
     */
    public function getUploadStatusTextAttr($value, $data)
    {
        $status = [0 => '待上传', 1 => '上传中', 2 => '上传成功', 3 => '上传失败'];
        return $status[$data['upload_status']] ?? '未知';
    }

    /**
     * 获取文件大小格式化
     */
    public function getFileSizeFormattedAttr($value, $data)
    {
        $size = $data['file_size'] ?? 0;
        if ($size >= 1073741824) {
            return round($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return round($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return $size . ' B';
        }
    }

    /**
     * 上传状态搜索
     */
    public function searchUploadStatusAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('upload_status', $value);
        }
    }

    /**
     * 任务名称搜索
     */
    public function searchTaskNameAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('task_name', 'like', "%{$value}%");
        }
    }

    /**
     * 文件名搜索
     */
    public function searchFileNameAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('file_name', 'like', "%{$value}%");
        }
    }

    /**
     * 文件类型搜索
     */
    public function searchFileTypeAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('file_type', 'like', "%{$value}%");
        }
    }
}