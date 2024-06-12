<?php

namespace plugin\jzadmin\model;

use support\Model;
use plugin\jzadmin\Admin;
use plugin\jzadmin\trait\DatetimeFormatterTrait;

class BaseModel extends Model
{
    use DatetimeFormatterTrait;

    public function __construct(array $attributes = [])
    {
        $this->setConnection(Admin::config('admin.database.connection'));

        parent::__construct($attributes);
    }
}
