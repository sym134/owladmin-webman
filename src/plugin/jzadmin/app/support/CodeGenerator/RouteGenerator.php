<?php

namespace plugin\jzadmin\app\support\CodeGenerator;

use plugin\jzadmin\app\service\AdminMenuService;

class RouteGenerator
{
    public static function handle($menuInfo): string
    {
        if (!$menuInfo['enabled']) {
            return '';
        }

        // 创建菜单
        $adminMenuService = AdminMenuService::make();

        $_url = '/' . ltrim($menuInfo['route'], '/');
        if (!$adminMenuService->getModel()->query()->where('url', $_url)->exists()) {
               $adminMenuService->store([
                'title'        => $menuInfo['title'],
                'icon'         => $menuInfo['icon'],
                'parent_id'    => $menuInfo['parent_id'],
                'url'          => $_url,
                'custom_order' => 100,
            ]);
        }

        if ($adminMenuService->hasError()) {
            abort(500, $adminMenuService->getError());
        }
        return runCommand('admin:gen-route');
    }

    public static function refresh(): void
    {
        runCommand('admin:gen-route');
    }
}
