<?php

use plugin\jzadmin\facade\Crypt;

// webman
use plugin\jzadmin\extend\Manager;
use plugin\jzadmin\support\Context;
use support\Cache;


if (!function_exists('admin_url')) {
    function admin_url($path = null, $needPrefix = false)
    {
        $prefix = $needPrefix ? '/' . \plugin\jzadmin\Admin::config('admin.route.prefix') : ''; // webman

        return $prefix . '/' . trim($path, '/');
    }
}

if (!function_exists('table_columns')) {
    /**
     * 获取表字段
     *
     * @param $tableName
     *
     * @return array
     */
    function table_columns($tableName)
    {
        return \support\Db::schema()->getColumnListing($tableName); // webman
    }
}

if (!function_exists('array2tree')) {
    /**
     * 生成树状数据
     *
     * @param array $list
     * @param int   $parentId
     *
     * @return array
     */
    function array2tree(array $list, int $parentId = 0)
    {
        $data = [];
        foreach ($list as $key => $item) {
            if ($item['parent_id'] == $parentId) {
                $children = array2tree($list, (int)$item['id']);
                !empty($children) && $item['children'] = $children;
                $data[] = $item;
                unset($list[$key]);
            }
        }
        return $data;
    }
}

if (!function_exists('admin_resource_full_path')) {
    function admin_resource_full_path($path, $server = null)
    {
        if (!$path) {
            return '';
        }
        if (url()->isValidUrl($path) || mb_strpos($path, 'data:image') === 0) {
            $src = $path;
        } else if ($server) {
            $src = rtrim($server, '/') . '/' . ltrim($path, '/');
        } else {
            $disk = \plugin\jzadmin\Admin::config('admin.upload.disk');

            if (config("filesystems.disks.{$disk}")) {
                $src = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
            } else {
                $src = '';
            }
        }
        $scheme = 'http:';
        if (\plugin\jzadmin\Admin::config('admin.https', false)) {
            $scheme = 'https:';
        }
        return preg_replace('/^http[s]{0,1}:/', $scheme, $src, 1);
    }
}

if (!function_exists('admin_path')) {
    function admin_path($path = '')
    {
        return ucfirst(config('plugin.jzadmin.admin.directory')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}


if (!function_exists('amis')) {
    /**
     * @param $type
     *
     * @return \plugin\jzadmin\renderer\Amis|\plugin\jzadmin\renderer\Component
     */
    function amis($type = null)
    {
        if (filled($type)) {
            return \plugin\jzadmin\renderer\Component::make()->setType($type); // webman
        }

        return \plugin\jzadmin\renderer\Amis::make();
    }
}

if (!function_exists('amisMake')) {
    /**
     * @return \plugin\jzadmin\renderer\Amis
     * @deprecated
     */
    function amisMake()
    {
        return \plugin\jzadmin\renderer\Amis::make();
    }
}

if (!function_exists('admin_encode')) {
    function admin_encode($str)
    {
        return Crypt::encryptString($str);  // webman
    }
}

if (!function_exists('admin_decode')) {
    function admin_decode($decodeStr)
    {
        try {
            $str = Crypt::decryptString($decodeStr);  // webman
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $str = '';
        }

        return $str;
    }
}


if (!function_exists('file_upload_handle')) {
    /**
     * 处理文件上传回显问题
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    function file_upload_handle()
    {
        $storage = \Shopwwi\WebmanFilesystem\Facade\Storage::adapter(\plugin\jzadmin\Admin::config('admin.upload.disk')); // webman

        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn($value) => $value ? $storage->url($value) : '', // webman
            set: fn($value) => str_replace($storage->url(''), '', $value)
        );
    }
}

if (!function_exists('file_upload_handle_multi')) {
    /**
     * 处理文件上传回显问题 (多个)
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    function file_upload_handle_multi()
    {
        $storage = \Illuminate\Support\Facades\Storage::disk(\plugin\jzadmin\Admin::config('admin.upload.disk'));

        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) use ($storage) {
                return array_map(fn($item) => $item ? admin_resource_full_path($item) : '', explode(',', $value));
            },
            set: function ($value) use ($storage) {
                if (is_string($value)) {
                    return str_replace($storage->url(''), '', $value);
                }

                $list = array_map(fn($item) => str_replace($storage->url(''), '', $item), \Illuminate\Support\Arr::wrap($value));

                return implode(',', $list);
            }
        );
    }
}

// 是否是json字符串
if (!function_exists('is_json')) {
    /**
     * 是否是json字符串
     *
     * @param $string
     *
     * @return bool
     */
    function is_json($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('settings')) {
    function settings()
    {
        return \plugin\jzadmin\service\AdminSettingService::make();
    }
}

if (!function_exists('admin_extension_path')) {
    /**
     * @param string|null $path
     *
     * @return string
     */
    function admin_extension_path(?string $path = null)
    {
        $dir = rtrim(config('plugin.jzadmin.admin.extension.dir'), '/') ?: base_path('extensions');

        $path = ltrim($path, '/');

        return $path ? $dir . '/' . $path : $dir;
    }
}

if (!function_exists('admin_user')) {
    function admin_user()
    {
        return \plugin\jzadmin\Admin::user();
    }
}

if (!function_exists('admin_abort')) {
    /**
     * 抛出异常
     *
     * @param string $message           异常信息
     * @param array  $data              异常数据
     * @param int    $doNotDisplayToast 是否显示提示 (解决在 amis 中抛出异常时，会显示两次提示的问题)
     *
     * @return mixed
     * @throws null
     */
    function admin_abort($message = '', $data = [], $doNotDisplayToast = 0)
    {
        throw new \plugin\jzadmin\exception\AdminException($message, $data, $doNotDisplayToast);
    }

    function amis_abort($message = '', $data = [])
    {
        admin_abort($message, $data, 1);
    }

    /**
     * 如果条件成立，抛出异常
     *
     * @param boolean $flag              条件
     * @param string  $message           异常信息
     * @param array   $data              异常数据
     * @param int     $doNotDisplayToast 是否显示提示 (解决在 amis 中抛出异常时，会显示两次提示的问题)
     *
     * @return void
     */
    function admin_abort_if($flag, $message = '', $data = [], $doNotDisplayToast = 0)
    {
        if ($flag) {
            admin_abort($message, $data, $doNotDisplayToast);
        }
    }

    function amis_abort_if($flag, $message = '', $data = [])
    {
        admin_abort_if($flag, $message, $data, 1);
    }
}

if (!function_exists('owl_admin_path')) {
    function owl_admin_path($path = '')
    {
        $path = ltrim($path, '/');

        return __DIR__ . '/../' . $path;
    }
}

if (!function_exists('admin_pages')) {
    function admin_pages($sign)
    {
        return \plugin\jzadmin\service\AdminPageService::make()->get($sign);
    }
}

if (!function_exists('map2options')) {
    /**
     * 键作为value, 值作为label, 返回options格式
     *
     * @param $map
     *
     * @return array
     */
    function map2options($map)
    {
        return collect($map)->map(fn($v, $k) => ['label' => $v, 'value' => $k])->values()->toArray();
    }
}

// webman 版本
if (!function_exists('admin_trans')) {
    function admin_trans(string|null $key = null, array $replace = [], string|null $locale = null)
    {
        if (is_null($key)) {
            return $key;
        }
        $arr = explode('.', $key);
        return trans(str_replace($arr[0] . '.', '', $key), $replace, $arr[0], $locale);
    }
}

// webman 增加
if (!function_exists('url')) {
    function url($val): string
    {
        return route($val);
    }
}
if (!function_exists('abort')) {
    function abort($code, $message)
    {
        throw new Exception($message, $code);
    }
}

if (!function_exists('jzenv')) {
    /**
     * 获取系统配置信息
     *
     * @param string $name
     * @param bool   $group
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function jzenv(string $name, bool $group = false)
    {
        $redis = 'config_' . $name;
        $config = Cache::get($redis);

        try {
            $configList = Cache::get('config_list') ?? [];
            if (is_array($config) ? empty($config) : is_empty($config)) {
                $config = \Jizhi\JzAdmin\model\Config::getAll($name, $group);
                if (!empty($config)) {
                    // 是否开启分组查询
                    if (empty($group)) {
                        $config = $config[$name];
                    }
                    $configList[$name] = $redis;
                    Cache::set($redis, $config);
                    Cache::set('config_list', $configList);
                }
            }

        } catch (\Exception $e) {
        }
        return $config;
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
    function is_empty($value): bool
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
    function cdn_prefix()
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
    function laravel_batch_update(string $table, array $list_data, int $chunk_size = 200)
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
    function generateRandomString($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('runComman')) {
    // 执行命令
    function runComman(string $commandName, array $arguments = [])
    {
        // 创建进程对象
        $process = new Symfony\Component\Process\Process(explode(' ', 'php webman ' . $commandName));
        // 执行命令
        $process->run();
        // 检查命令是否执行成功
        if (!$process->isSuccessful()) {
            throw new Symfony\Component\Process\Exception\ProcessFailedException($process);
        }
        // 获取命令输出
        return $process->getOutput();
    }
}

if (!function_exists('app')) {
    function app($name)
    {
        $arr = [
            'files'         => \support\Container::make(\Illuminate\Filesystem\Filesystem::class,[]),
            'admin.context' => \support\Container::make(Context::class,[]),
            'admin.setting' => settings(),
            'admin.menu'    => \support\Container::make(\plugin\jzadmin\support\Cores\Menu::class,[]),
            'admin.extend'  => \support\Container::make(Manager::class,[]),
        ];
        return $arr[$name];
    }
}

if (!function_exists('database_path')) {
    function database_path($name)
    {
        return 'database/' . $name;
    }
}

if (!function_exists('cache')) {
    function cache(): \plugin\jzadmin\utils\Cache
    {
        return new \plugin\jzadmin\utils\Cache();
    }
}
