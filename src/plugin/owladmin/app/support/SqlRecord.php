<?php

namespace plugin\owladmin\app\support;

use support\Db;
use Illuminate\Database\Events\QueryExecuted;

class SqlRecord
{
    public static array $sql = [];

    public static function listen(): void
    {
        Db::listen(function (QueryExecuted $query) {
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
