<?php

namespace plugin\owladmin\app\model\system;

use plugin\owladmin\app\model\BaseModel as Model;

/**
 * 定时任务日志
 */
class AdminCrontabLog extends Model
{

    protected $table = 'admin_crontab_log';

    protected $casts = [
        'parameter' => 'json',
    ];

    public const UPDATED_AT = null;
}
