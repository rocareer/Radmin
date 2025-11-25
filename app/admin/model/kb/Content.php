<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 知识库内容模型
 */
class Content extends BaseModel
{
    // 表名
    protected $name = 'kb_content';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 字段类型
    protected array $type = [
        'publish_time' => 'timestamp',
        'is_top' => 'boolean',
        'views' => 'integer',
        'likes' => 'integer',
    ];

    // 关联知识库
    public function kb()
    {
        return $this->belongsTo(Kb::class, 'kb_id', 'id');
    }

    // 关联类型
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    // 关联分类
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    // 关联标签
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'kb_content_tag', 'tag_id', 'content_id');
    }

    // 关联附件
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'content_id', 'id');
    }

    // 关联管理员
    public function admin()
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [0 => '草稿', 1 => '发布', 2 => '审核中'];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 获取是否置顶文本
     */
    public function getIsTopTextAttr($value, $data)
    {
        return $data['is_top'] ? '是' : '否';
    }

    /**
     * 搜索条件
     */
    public function searchKeywordAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('title|content|keywords', 'like', "%{$value}%");
        }
    }

    /**
     * 知识库搜索
     */
    public function searchKbIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('kb_id', $value);
        }
    }

    /**
     * 类型搜索
     */
    public function searchTypeIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('type_id', $value);
        }
    }

    /**
     * 分类搜索
     */
    public function searchCategoryIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('category_id', $value);
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

    /**
     * 作者搜索
     */
    public function searchAuthorAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('author', 'like', "%{$value}%");
        }
    }
}