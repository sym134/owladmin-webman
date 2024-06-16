<?php

namespace plugin\owladmin\app\model;

use support\Model;
use plugin\owladmin\app\Admin;
use plugin\owladmin\app\trait\DatetimeFormatterTrait;

class BaseModel extends Model
{
    use DatetimeFormatterTrait;

    public function __construct(array $attributes = [])
    {
        $this->setConnection(Admin::config('admin.database.connection'));

        parent::__construct($attributes);
    }
}
