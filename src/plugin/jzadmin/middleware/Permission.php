<?php

namespace plugin\jzadmin\middleware;

use support\Request;
use Webman\Http\Response;
use plugin\jzadmin\Admin;
use Webman\MiddlewareInterface;

class Permission implements MiddlewareInterface
{
    public function process(\Webman\Http\Request $request, callable $handler): Response
    {
        if (Admin::permission()->permissionIntercept($request, '')) {
            return Admin::response()->fail(admin_trans('admin.unauthorized'));
        }

        return $handler($request);
    }
}

