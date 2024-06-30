<?php

namespace plugin\owladmin\app\model\system;

use plugin\owladmin\app\model\BaseModel;

class Attachment extends BaseModel
{
    const STORAGE_MODE = ['local' => '本地', 'qiniu' => '七牛', 'aliyun' => '阿里云', 'qcloud' => '腾讯云'];
    const FILE_TYPE = ['image' => '图片', 'text' => '文档', 'audio' => '音频', 'file' => '文件'];

    protected $table = 'attachments';
}
