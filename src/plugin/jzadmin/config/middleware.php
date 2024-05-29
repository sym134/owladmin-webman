<?php

use plugin\jzadmin\middleware\Permission;
use plugin\jzadmin\middleware\Authenticate;

return [
    'admin' => [
        Authenticate::class,
        Permission::class,
    ],
];
