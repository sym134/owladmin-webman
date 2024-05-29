<?php
/**
 * Here is your custom functions.
 */

// use support\Cache;
// use plugin\jzadmin\support\Context;
//
// if (!function_exists('url')) {
//     function url($val): string
//     {
//         return route($val);
//     }
// }
// if (!function_exists('abort')) {
//     function abort($code, $message)
//     {
//         throw new Exception($message, $code);
//     }
// }
//
// if (!function_exists('jzenv')) {
//     /**
//      * 获取系统配置信息
//      *
//      * @param string $name
//      * @param bool   $group
//      *
//      * @return mixed
//      * @throws \Psr\SimpleCache\InvalidArgumentException
//      * @throws \think\db\exception\DataNotFoundException
//      * @throws \think\db\exception\DbException
//      * @throws \think\db\exception\ModelNotFoundException
//      */
//     function jzenv(string $name, bool $group = false)
//     {
//         $redis = 'config_' . $name;
//         $config = Cache::get($redis);
//
//         try {
//             $configList = Cache::get('config_list') ?? [];
//             if (is_array($config) ? empty($config) : is_empty($config)) {
//                 $config = \Jizhi\JzAdmin\model\Config::getAll($name, $group);
//                 if (!empty($config)) {
//                     // 是否开启分组查询
//                     if (empty($group)) {
//                         $config = $config[$name];
//                     }
//                     $configList[$name] = $redis;
//                     Cache::set($redis, $config);
//                     Cache::set('config_list', $configList);
//                 }
//             }
//
//         } catch (\Exception $e) {
//         }
//         return $config;
//     }
// }
// if (!function_exists('is_empty')) {
//     /**
//      * 判断是否为空值
//      *
//      * @param array|string $value 要判断的值
//      *
//      * @return bool
//      */
//     function is_empty($value): bool
//     {
//         if (!isset($value)) {
//             return true;
//         }
//
//         if (trim($value) === '') {
//             return true;
//         }
//
//         return false;
//     }
// }
//
// if (!function_exists('cdn_prefix')) {
//
//     /**
//      * 获取远程图片前缀
//      *
//      * @return string
//      * @throws \Psr\SimpleCache\InvalidArgumentException
//      * @throws \think\db\exception\DataNotFoundException
//      * @throws \think\db\exception\DbException
//      * @throws \think\db\exception\ModelNotFoundException
//      */
//     function cdn_prefix()
//     {
//         $config = jzenv('upload', true);
//         $prefix = $config['upload_http_prefix'];
//         if ($config['cloud_status'] === '1') {
//             $prefix = $config[$config['cloud_type']]['url'];
//         }
//         return $prefix;
//     }
// }
//
// if (!function_exists('laravel_batch_update')) {
//     /**
//      * laravel数据库单表批量更新，适用于laravel
//      *
//      * @param string $table
//      * @param array  $list_data
//      * @param int    $chunk_size
//      *
//      * @return int
//      * @throws Exception
//      * @author mosquito <zwj1206_hi@163.com> 2020-10-21
//      */
//     function laravel_batch_update(string $table, array $list_data, int $chunk_size = 200)
//     {
//         if (count($list_data) < 1) {
//             throw new \Exception('更新数量不能小于1');
//         }
//         if ($chunk_size < 1) {
//             throw new \Exception('分切数量不能小于1');
//         }
//         $chunk_list = array_chunk($list_data, $chunk_size);
//         $count = 0;
//         foreach ($chunk_list as $list_item) {
//             $first_row = current($list_item);
//             $update_col = array_keys($first_row);
//             // 默认以id为条件更新，如果没有ID则以第一个字段为条件
//             $reference_col = isset($first_row['id']) ? 'id' : current($update_col);
//             unset($update_col[0]);
//             // 拼接sql语句
//             $update_sql = 'UPDATE ' . $table . ' SET ';
//             $sets = [];
//             $bindings = [];
//             foreach ($update_col as $u_col) {
//                 $set_sql = '`' . $u_col . '` = CASE ';
//                 foreach ($list_item as $item) {
//                     $set_sql .= 'WHEN `' . $reference_col . '` = ? THEN ';
//                     $bindings[] = $item[$reference_col];
//                     if ($item[$u_col] instanceof \Illuminate\Database\Query\Expression) {
//                         $set_sql .= $item[$u_col]->getValue() . ' ';
//                     } else {
//                         $set_sql .= '? ';
//                         $bindings[] = $item[$u_col];
//                     }
//                 }
//                 $set_sql .= 'ELSE `' . $u_col . '` END ';
//                 $sets[] = $set_sql;
//             }
//             $update_sql .= implode(', ', $sets);
//             $where_in = collect($list_item)->pluck($reference_col)->values()->all();
//             $bindings = array_merge($bindings, $where_in);
//             $where_in = rtrim(str_repeat('?,', count($where_in)), ',');
//             $update_sql = rtrim($update_sql, ', ') . ' WHERE `' . $reference_col . '` IN (' . $where_in . ')';
//             //
//             $count += \support\Db::update($update_sql, $bindings);
//         }
//         return $count;
//     }
// }
// if (!function_exists('generateRandomString')) {
//     function generateRandomString($length = 32)
//     {
//         $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//         $randomString = '';
//
//         for ($i = 0; $i < $length; $i++) {
//             $randomString .= $characters[rand(0, strlen($characters) - 1)];
//         }
//
//         return $randomString;
//     }
// }
//
// if (!function_exists('runComman')) {
//     // 执行命令
//     function runComman(string $commandName, array $arguments = [])
//     {
//         // 创建进程对象
//         $process = new Symfony\Component\Process\Process(explode(' ', 'php webman ' . $commandName));
//         // 执行命令
//         $process->run();
//         // 检查命令是否执行成功
//         if (!$process->isSuccessful()) {
//             throw new Symfony\Component\Process\Exception\ProcessFailedException($process);
//         }
//         // 获取命令输出
//         return $process->getOutput();
//     }
// }
//
// if (!function_exists('app')) {
//     function app($name)
//     {
//         $arr = [
//             'files'         => \support\Container::make(\Illuminate\Filesystem\Filesystem::class),
//             'admin.context' => \support\Container::make(Context::class),
//             'admin.setting' => settings(),
//             'admin.menu'    => \support\Container::make(\plugin\jzadmin\support\Cores\Menu::class),
//         ];
//         return $arr[$name];
//     }
// }
//
// if (!function_exists('database_path')) {
//     function database_path($name)
//     {
//         return 'database/' . $name;
//     }
// }
//
// if (!function_exists('cache')) {
//     function cache(): \plugin\jzadmin\utils\Cache
//     {
//         return new \plugin\jzadmin\utils\Cache();
//     }
// }

// webman 版本
// if (!function_exists('admin_trans')) {
//     function admin_trans(string|null $key = null, array $replace = [], string|null $locale = null)
//     {
//         if (is_null($key)) {
//             return $key;
//         }
//         $arr = explode('.', $key);
//         return trans(str_replace($arr[0] . '.', '', $key), $replace, $arr[0], $locale);
//     }
// }
