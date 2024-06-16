<?php

namespace plugin\owladmin\app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AutoSetLocale implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $locale = request()->header('locale', config('plugin.owladmin.translation.locale')); // 获取客户端要求的语言包
        // 切换语言
        locale($locale);
        return $handler($request);
    }
}
