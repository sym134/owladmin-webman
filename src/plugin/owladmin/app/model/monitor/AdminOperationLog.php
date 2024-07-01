<?php

namespace plugin\owladmin\app\model\monitor;

use plugin\owladmin\app\model\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminOperationLog extends BaseModel
{
    use SoftDeletes;

    protected $table = 'admin_operation_log';
    public const UPDATED_AT = null;
}
