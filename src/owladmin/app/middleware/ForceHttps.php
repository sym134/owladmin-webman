<?php

namespace plugin\owladmin\app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use plugin\owladmin\app\Admin;
use Webman\MiddlewareInterface;

class ForceHttps implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if ($request->protocolVersion() === '1.1' && Admin::config('admin.https')) {
            return Admin::response()->additional(['code' => 301])->fail('请使用https');
        }

        return $handler($request);
    }
}
