<?php

namespace plugin\jzadmin\app\model;

class AdminSetting extends BaseModel
{
    protected $table = 'admin_settings';

    protected $primaryKey = 'key';

    protected $guarded = [];

    protected $casts = [
        'values' => 'json',
    ];
}
