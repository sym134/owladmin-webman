<?php

namespace plugin\owladmin\app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use plugin\owladmin\app\Admin;
use Webman\MiddlewareInterface;

class Authenticate implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (Admin::permission()->authIntercept($request)) {
            return Admin::response()->additional(['code' => 401])->fail(admin_trans('admin.please_login'));
        }

        return $handler($request);
    }
}