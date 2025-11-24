<?php

namespace app\admin\model\cms;

use think\facade\Db;
use think\Model;
use think\model\relation\BelongsTo;

/**
 * Content
 */
class Content extends Model
{
    // 表名
    protected $name = 'cms_content';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 追加属性
    protected $append = [
        'cmsChannel',
        'cmsTags',
    ];

    // 字段类型转换
    protected $type = [
        'publish_time' => 'timestamp:Y-m-d H:i:s',
    ];

    protected string $contentLanguage = '';

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->contentLanguage = Db::name('cms_config')
            ->where('name', 'content_language')
            ->value('value');
    }

    protected static function onAfterInsert($model)
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
        }
    }

    public static function getTitleStyleAttr($value): array
    {
        if (!$value) return [];
        $value = json_decode($value, true);
        if ($value['bold']) $value['bold'] = true;
        return $value;
    }

    public function setTitleStyleAttr($value): string
    {
        if ($value) {
            if ($value['bold']) $value['bold'] = 1;
            return json_encode($value);
        }
        return '';
    }

    public function getChannelIdsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setChannelIdsAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function getFlagAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setFlagAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public static function getImagesAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            $images = explode(',', $value);
            return array_map(function ($image) {
                return full_url($image);
            }, array_filter($images));
        }
        return $value;
    }

    public function setImagesAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function getContentAttr($value): string
    {
        $value = !$value ? '' : htmlspecialchars_decode($value);
        if ($this->contentLanguage == 'html') {
            if (!str_contains($value, '<p>')) {
                $value = '<p>' . $value . '</p>';
            }
        }
        return $value;
    }

    public function getCmsTagsAttr($value, $row): array
    {
        return [
            'name' => \app\admin\model\cms\Tags::whereIn('id', $row['tags'])->column('name', 'id'),
        ];
    }

    public function getTagsAttr($value): array
    {
        if ($value === '' || $value === null) return [];
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setTagsAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function getPriceAttr($value): float
    {
        return (float)$value;
    }

    public static function modelDataOutput($value, $type): mixed
    {
        if (in_array($type, ['checkbox', 'images', 'files'])) {
            if ($value === '' || $value === null) return [];
            if (!is_array($value)) {
                return explode(',', $value);
            }
            return $value;
        } elseif ($type == 'array') {
            return empty($value) ? [] : json_decode($value, true);
        } elseif ($type == 'editor') {
            return !$value ? '' : htmlspecialchars_decode($value);
        } elseif ($type == 'city') {
            if ($value === '' || $value === null) return '';
            $cityNames = Db::name('area')->whereIn('id', $value)->column('name');
            return $cityNames ? implode(',', $cityNames) : '';
        } elseif ($type == 'datetime') {
            return $value ? date('Y-m-d H:i:s', $value) : '';
        } else {
            return $value;
        }
    }

    public static function getModelTableData($value, $type): mixed
    {
        if (!$type) return $value;
        if ($type == 'array') {
            return empty($value) ? [] : json_decode($value, true);
        } elseif ($type == 'switch') {
            return (bool)$value;
        } elseif ($type == 'datetime') {
            return $value ? date('Y-m-d H:i:s', $value) : '';
        } elseif ($type == 'editor') {
            return !$value ? '' : htmlspecialchars_decode($value);
        } elseif (in_array($type, ['city', 'checkbox', 'selects', 'remoteSelects'])) {
            if ($value == '') {
                return [];
            }
            if (!is_array($value)) {
                return explode(',', $value);
            }
            return $value;
        } elseif ($type == 'float') {
            return is_null($value) ? null : (float)$value;
        }
        return $value;
    }

    public static function setModelTableData($value, $type): string|int|bool|null
    {
        if ($type == 'array') {
            return $value ? json_encode($value) : '';
        } elseif ($type == 'switch') {
            return $value ? '1' : '0';
        } elseif ($type == 'time') {
            return $value ? date('H:i:s', strtotime($value)) : $value;
        } elseif ($type == 'datetime') {
            return $value ? strtotime($value) : $value;
        } elseif ($type == 'city' || $type == 'checkbox' || $type == 'selects') {
            if ($value && is_array($value)) {
                return implode(',', $value);
            }
            return $value ?: '';
        } elseif (is_array($value)) {
            return implode(',', $value);
        }
        return $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

    public function cmsChannel(): BelongsTo
    {
        return $this->belongsTo(\app\admin\model\cms\Channel::class, 'channel_id', 'id');
    }
}