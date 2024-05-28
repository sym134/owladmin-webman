<?php

namespace plugin\jzadmin\model;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use plugin\jzadmin\Admin;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    public function __construct(array $attributes = [])
    {
        $this->setConnection(Admin::config('admin.database.connection'));

        parent::__construct($attributes);
    }
}
