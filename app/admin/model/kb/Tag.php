<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 知识库标签模型
 */
class Tag extends BaseModel
{
    // 表名
    protected $name = 'kb_tag';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 关联内容
    public function contents()
    {
        return $this->belongsToMany(Content::class, 'kb_content_tag', 'content_id', 'tag_id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 搜索条件
     */
    public function searchKeywordAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('name', 'like', "%{$value}%");
        }
    }
}