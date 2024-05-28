<?php

namespace plugin\jzadmin\middleware;

use Closure;
use plugin\jzadmin\Admin;
use Illuminate\Http\Request;

class Permission
{
    public function handle(Request $request, Closure $next, ...$args)
    {
        if (Admin::permission()->permissionIntercept($request, $args)) {
            return Admin::response()->fail(__('admin.unauthorized'));
        }

        return $next($request);
    }
}
