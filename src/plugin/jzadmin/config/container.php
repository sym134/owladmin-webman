<?php
// return new Webman\Container;

use support\Container;
use Illuminate\Filesystem\Filesystem;
use plugin\jzadmin\app\extend\Manager;
use plugin\jzadmin\app\support\Cores\Menu;
use plugin\jzadmin\app\support\Cores\Asset;
use plugin\jzadmin\app\support\Cores\Module;
use plugin\jzadmin\app\support\Apis\DataListApi;
use plugin\jzadmin\app\support\Apis\DataCreateApi;
use plugin\jzadmin\app\support\Apis\DataDetailApi;
use plugin\jzadmin\app\support\Apis\DataDeleteApi;
use plugin\jzadmin\app\support\Apis\DataUpdateApi;
use plugin\jzadmin\app\service\AdminSettingService;

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions([
    'apis'          => [
        DataListApi::class,
        DataCreateApi::class,
        DataDetailApi::class,
        DataDeleteApi::class,
        DataUpdateApi::class,
    ],
    'files'         => new Filesystem,
    'admin.menu'    => new Menu,
    'admin.asset'   => new Asset,
    'admin.setting' => AdminSettingService::make(),
    'admin.module'  => new Module,
    'admin.extend'  => fn() => Container::instance('jzadmin')->make(Manager::class), // 拓展
]);
$builder->useAutowiring(true);
$builder->useAnnotations(true);
return $builder->build();
