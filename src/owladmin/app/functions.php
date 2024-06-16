<?php
/**
 * Here is your custom functions.
 */
use support\Cache;
use support\Container;
use plugin\owladmin\app\extend\Manager;
use plugin\owladmin\app\support\Context;

// webman 增加
if (!function_exists('plugin_path')) {
    function plugin_path(string $path = ''): string
    {
        return path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'plugin', $path);
    }
}
if (!function_exists('url')) {
    function url($val): string
    {
        return route($val);
    }
}
if (!function_exists('abort')) {
    /**
     * @throws Exception
     */
    function abort($code, $message)
    {
        throw new Exception($message, $code);
    }
}
if (!function_exists('is_empty')) {
    /**
     * 判断是否为空值
     *
     * @param array|string $value 要判断的值
     *
     * @return bool
     */
    function is_empty(array|string $value): bool
    {
        if (!isset($value)) {
            return true;
        }

        if (trim($value) === '') {
            return true;
        }

        return false;
    }
}

if (!function_exists('cdn_prefix')) {

    /**
     * 获取远程图片前缀
     *
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function cdn_prefix(): string
    {
        $config = jzenv('upload', true);
        $prefix = $config['upload_http_prefix'];
        if ($config['cloud_status'] === '1') {
            $prefix = $config[$config['cloud_type']]['url'];
        }
        return $prefix;
    }
}

if (!function_exists('laravel_batch_update')) {
    /**
     * laravel数据库单表批量更新，适用于laravel
     *
     * @param string $table
     * @param array  $list_data
     * @param int    $chunk_size
     *
     * @return int
     * @throws Exception
     * @author mosquito <zwj1206_hi@163.com> 2020-10-21
     */
    function laravel_batch_update(string $table, array $list_data, int $chunk_size = 200): int
    {
        if (count($list_data) < 1) {
            throw new \Exception('更新数量不能小于1');
        }
        if ($chunk_size < 1) {
            throw new \Exception('分切数量不能小于1');
        }
        $chunk_list = array_chunk($list_data, $chunk_size);
        $count = 0;
        foreach ($chunk_list as $list_item) {
            $first_row = current($list_item);
            $update_col = array_keys($first_row);
            // 默认以id为条件更新，如果没有ID则以第一个字段为条件
            $reference_col = isset($first_row['id']) ? 'id' : current($update_col);
            unset($update_col[0]);
            // 拼接sql语句
            $update_sql = 'UPDATE ' . $table . ' SET ';
            $sets = [];
            $bindings = [];
            foreach ($update_col as $u_col) {
                $set_sql = '`' . $u_col . '` = CASE ';
                foreach ($list_item as $item) {
                    $set_sql .= 'WHEN `' . $reference_col . '` = ? THEN ';
                    $bindings[] = $item[$reference_col];
                    if ($item[$u_col] instanceof \Illuminate\Database\Query\Expression) {
                        $set_sql .= $item[$u_col]->getValue() . ' ';
                    } else {
                        $set_sql .= '? ';
                        $bindings[] = $item[$u_col];
                    }
                }
                $set_sql .= 'ELSE `' . $u_col . '` END ';
                $sets[] = $set_sql;
            }
            $update_sql .= implode(', ', $sets);
            $where_in = collect($list_item)->pluck($reference_col)->values()->all();
            $bindings = array_merge($bindings, $where_in);
            $where_in = rtrim(str_repeat('?,', count($where_in)), ',');
            $update_sql = rtrim($update_sql, ', ') . ' WHERE `' . $reference_col . '` IN (' . $where_in . ')';
            //
            $count += \support\Db::update($update_sql, $bindings);
        }
        return $count;
    }
}
if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 32): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('runCommand')) {
    // 执行命令
    function runCommand(string $commandName, array $arguments = []): string
    {
        $array = explode(' ', 'php webman ' . $commandName);
        $array=array_merge($array, $arguments);
        // 创建进程对象
        $process = new Symfony\Component\Process\Process($array);
        // 执行命令
        $process->run();
        // 检查命令是否执行成功
        if (!$process->isSuccessful()) {
            return $process->getOutput();
        }
        // 获取命令输出
        return $process->getOutput();
    }
}

if (!function_exists('appw')) {
    function appw($name)
    {
        return Container::instance('owladmin')->get($name);
    }
}

if (!function_exists('database_path')) {
    function database_path($name): string
    {
        return 'database/' . $name;
    }
}

if (!function_exists('cache')) {
    function cache(): \plugin\owladmin\app\utils\Cache
    {
        return new \plugin\owladmin\app\utils\Cache();
    }
}

if (!function_exists('safe_explode')) {
    /**
     * 可传入数组的 explode
     *
     * @param $delimiter
     * @param $string
     *
     * @return array|false|string[]
     */
    function safe_explode($delimiter, $string): array|bool
    {
        if (is_array($string)) {
            return $string;
        }

        return explode($delimiter, $string);
    }
}
