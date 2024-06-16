<?php

namespace plugin\jzadmin\app\model;

use support\Model;
use plugin\jzadmin\app\Admin;
use plugin\jzadmin\app\trait\DatetimeFormatterTrait;

class BaseModel extends Model
{
    use DatetimeFormatterTrait;

    public function __construct(array $attributes = [])
    {
        $this->setConnection(Admin::config('admin.database.connection'));

        parent::__construct($attributes);
    }
}
