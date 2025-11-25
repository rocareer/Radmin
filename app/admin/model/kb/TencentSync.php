<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 腾讯云知识库同步记录模型
 */
class TencentSync extends BaseModel
{
    // 表名
    protected $name = 'kb_tencent_sync';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 字段类型
    protected array $type = [
        'sync_type' => 'integer',
        'sync_status' => 'integer',
        'last_sync_time' => 'timestamp',
    ];

    // 关联本地内容
    public function localContent()
    {
        return $this->belongsTo(Content::class, 'local_content_id', 'id');
    }

    /**
     * 获取同步类型文本
     */
    public function getSyncTypeTextAttr($value, $data)
    {
        $types = [1 => '上传到腾讯云', 2 => '从腾讯云下载', 3 => '双向同步'];
        return $types[$data['sync_type']] ?? '未知';
    }

    /**
     * 获取同步状态文本
     */
    public function getSyncStatusTextAttr($value, $data)
    {
        $status = [0 => '待同步', 1 => '同步中', 2 => '同步成功', 3 => '同步失败'];
        return $status[$data['sync_status']] ?? '未知';
    }

    /**
     * 同步类型搜索
     */
    public function searchSyncTypeAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('sync_type', $value);
        }
    }

    /**
     * 同步状态搜索
     */
    public function searchSyncStatusAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('sync_status', $value);
        }
    }

    /**
     * 腾讯云文档ID搜索
     */
    public function searchTencentDocIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('tencent_doc_id', 'like', "%{$value}%");
        }
    }

    /**
     * 腾讯云文档标题搜索
     */
    public function searchTencentDocTitleAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('tencent_doc_title', 'like', "%{$value}%");
        }
    }
}