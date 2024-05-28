<?php

namespace plugin\jzadmin\support\CodeGenerator;

use Illuminate\Support\Facades\Artisan;
use plugin\jzadmin\service\AdminMenuService;

class RouteGenerator
{
    public static function handle($menuInfo)
    {
        if (!$menuInfo['enabled']) {
            return;
        }

        // 创建菜单
        $adminMenuService = AdminMenuService::make();

        $_url = '/' . ltrim($menuInfo['route'], '/');
        if (!$adminMenuService->getModel()->query()->where('url', $_url)->exists()) {
            $adminMenuService->store([
                'title'     => $menuInfo['title'],
                'icon'      => $menuInfo['icon'],
                'parent_id' => $menuInfo['parent_id'],
                'url'       => $_url,
                'order'     => 100,
            ]);
        }

        if ($adminMenuService->hasError()) {
            abort(500, $adminMenuService->getError());
        }

        // 刷新路由
        runComman('admin:gen-route');
    }
}
