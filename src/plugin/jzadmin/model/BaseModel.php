<?php

namespace plugin\jzadmin\model;

use Illuminate\Database\Eloquent\Model;
use plugin\jzadmin\Admin;

class BaseModel extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->setConnection(Admin::config('admin.database.connection'));

        parent::__construct($attributes);
    }

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format($this->getDateFormat());
    }
}
