<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 腾讯云知识库配置模型
 */
class TencentConfig extends BaseModel
{
    // 表名
    protected $name = 'kb_tencent_config';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 字段类型
    protected array $type = [
        'status' => 'boolean',
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
     * 状态搜索
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('status', $value);
        }
    }

    /**
     * 名称搜索
     */
    public function searchNameAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('name', 'like', "%{$value}%");
        }
    }
}