<?php

use plugin\jzadmin\app\middleware\Permission;
use plugin\jzadmin\app\middleware\ForceHttps;
use plugin\jzadmin\app\middleware\Authenticate;
use plugin\jzadmin\app\middleware\AutoSetLocale;

return [
    '' => [
        ForceHttps::class,
        AutoSetLocale::class,
        Authenticate::class,
        Permission::class,
    ],
];
