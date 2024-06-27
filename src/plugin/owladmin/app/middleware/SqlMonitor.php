<?php

namespace plugin\owladmin\app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use plugin\owladmin\app\support\SqlRecord;

class SqlMonitor implements MiddlewareInterface
{

    public function process(Request $request, callable $handler): Response
    {
        if (config('app.debug')) {
            SqlRecord::$sql = [];
            SqlRecord::listen();
        }
        return $handler($request);

    }
}
