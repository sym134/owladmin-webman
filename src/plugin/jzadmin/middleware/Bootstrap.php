<?php

namespace plugin\jzadmin\middleware;

use Closure;
use plugin\jzadmin\Admin;
use Illuminate\Http\Request;

class Bootstrap
{
    public function handle(Request $request, Closure $next)
    {
        Admin::bootstrap();

        return $next($request);
    }
}
