<?php

namespace app\admin\model\cms;

use think\facade\Db;
use think\Model;
use think\model\relation\BelongsTo;

/**
 * Comment
 */
class Comment extends Model
{
    // 表名
    protected $name = 'cms_comment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    protected $type = [
        'at_user' => 'array',
    ];

    protected string $commentLanguage = '';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->commentLanguage = Db::name('cms_config')
            ->where('name', 'comment_language')
            ->value('value');
    }

    protected static function onAfterInsert($model)
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            if (strlen($model[$pk]) >= 19) {
                $model->where($pk, $model[$pk])->update(['weigh' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
            }
        }
    }

    public function getContentAttr($value): string
    {
        $value = !$value ? '' : htmlspecialchars_decode($value);
        if ($this->commentLanguage == 'html') {
            if (!str_contains($value, '<p>')) {
                $value = '<p>' . $value . '</p>';
            }
        }
        return $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function cmsContent(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\cms\Content::class, 'content_id', 'id');
    }
}