<?php

namespace app\admin\model\kb;

use app\common\model\BaseModel;

/**
 * 知识库附件模型
 */
class Attachment extends BaseModel
{
    // 表名
    protected $name = 'kb_attachment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';

    // 关联内容
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }

    /**
     * 获取文件大小文本
     */
    public function getSizeTextAttr($value, $data)
    {
        $size = $data['size'];
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
     * 获取文件类型图标
     */
    public function getFileIconAttr($value, $data)
    {
        $mime = $data['mime_type'];
        if (strpos($mime, 'image/') === 0) {
            return 'fa fa-file-image-o';
        } elseif (strpos($mime, 'video/') === 0) {
            return 'fa fa-file-video-o';
        } elseif (strpos($mime, 'audio/') === 0) {
            return 'fa fa-file-audio-o';
        } elseif (strpos($mime, 'pdf') !== false) {
            return 'fa fa-file-pdf-o';
        } elseif (strpos($mime, 'word') !== false || strpos($mime, 'document') !== false) {
            return 'fa fa-file-word-o';
        } elseif (strpos($mime, 'excel') !== false || strpos($mime, 'spreadsheet') !== false) {
            return 'fa fa-file-excel-o';
        } elseif (strpos($mime, 'powerpoint') !== false || strpos($mime, 'presentation') !== false) {
            return 'fa fa-file-powerpoint-o';
        } elseif (strpos($mime, 'zip') !== false || strpos($mime, 'rar') !== false || strpos($mime, 'tar') !== false) {
            return 'fa fa-file-archive-o';
        } else {
            return 'fa fa-file-o';
        }
    }
}