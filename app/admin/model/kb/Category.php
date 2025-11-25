<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 知识库分类模型
 */
class Category extends BaseModel
{
    // 表名
    protected $name = 'kb_category';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

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
        if ($value !== '' && $value !== null) {
            $query->where('status', '=', $value);
        }
    }
}