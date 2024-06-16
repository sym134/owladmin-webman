<?php

use plugin\owladmin\app\middleware\Permission;
use plugin\owladmin\app\middleware\ForceHttps;
use plugin\owladmin\app\middleware\Authenticate;
use plugin\owladmin\app\middleware\AutoSetLocale;

return [
    '' => [
        ForceHttps::class,
        AutoSetLocale::class,
        Authenticate::class,
        Permission::class,
    ],
];
