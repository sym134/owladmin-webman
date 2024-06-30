<?php

namespace plugin\owladmin\app\bootstrap;

use Webman\Bootstrap;
use Workerman\Worker;
use plugin\owladmin\app\support\SqlRecord;

class SqlMonitor implements Bootstrap
{

    public static function start(?Worker $worker): void
    {
        if (config('app.debug')) {
            SqlRecord::listen();
        }
    }
}
