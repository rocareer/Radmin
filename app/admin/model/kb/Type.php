<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 知识库类型模型
 */
class Type extends BaseModel
{
    // 表名
    protected $name = 'kb_type';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 字段类型
    protected array $type = [
        'status' => 'boolean',
        'sort' => 'integer',
    ];

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

    /**
     * 状态搜索
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('status', $value);
        }
    }
}