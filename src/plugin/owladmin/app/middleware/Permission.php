<?php

namespace plugin\owladmin\app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use plugin\owladmin\app\Admin;
use Webman\MiddlewareInterface;

class Permission implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (Admin::permission()->permissionIntercept($request, '')) {
            return Admin::response()->fail(admin_trans('admin.unauthorized'));
        }

        return $handler($request);
    }
}

