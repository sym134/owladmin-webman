<?php

namespace plugin\owladmin\app\middleware;

use Webman\Event\Event;
use Webman\Http\Request;
use Webman\Http\Response;
use plugin\owladmin\app\Admin;
use Webman\MiddlewareInterface;

class Authenticate implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        [$state, $user] = Admin::permission()->authIntercept($request);
        if ($state) {
            return Admin::response()->additional(['code' => 401])->fail(admin_trans('admin.please_login'));
        }
        $request->user = $user;
        // 记录日志
        Event::emit('user.operateLog', true);
        return $handler($request);
    }
}
