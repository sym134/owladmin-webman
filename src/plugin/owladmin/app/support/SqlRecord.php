<?php

namespace plugin\owladmin\app\support;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class SqlRecord
{
    public static array $sql = [];

    public static function listen(): void
    {
        Manager::connection()->listen(function ($query) {
            $bindings = $query->bindings;
            $sql = $query->sql;

            foreach ($bindings as $replace) {
                $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }

            $sql = sprintf('[%s ms] %s', $query->time, $sql);

            self::$sql[] = $sql;
        });
    }
}
