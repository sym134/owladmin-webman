<?php

use plugin\jzadmin\facade\Crypt;

if (!function_exists('admin_url')) {
    function admin_url($path = null, $needPrefix = false)
    {
        $prefix = $needPrefix ? '/' . \plugin\jzadmin\app\Admin::config('admin.route.prefix') : ''; // webman

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
            $disk = \plugin\jzadmin\app\Admin::config('admin.upload.disk');

            if (config("filesystems.disks.{$disk}")) {
                $src = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
            } else {
                $src = '';
            }
        }
        $scheme = 'http:';
        if (\plugin\jzadmin\app\Admin::config('admin.https', false)) {
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
     * @return \plugin\jzadmin\app\renderer\Amis|\plugin\jzadmin\app\renderer\Component
     */
    function amis($type = null): \plugin\jzadmin\app\renderer\Amis|\plugin\jzadmin\app\renderer\Component
    {
        if (filled($type)) {
            return \plugin\jzadmin\app\renderer\Component::make()->setType($type); // webman
        }

        return \plugin\jzadmin\app\renderer\Amis::make();
    }
}

if (!function_exists('amisMake')) {
    /**
     * @return \plugin\jzadmin\app\renderer\Amis
     * @deprecated
     */
    function amisMake(): \plugin\jzadmin\app\renderer\Amis
    {
        return \plugin\jzadmin\app\renderer\Amis::make();
    }
}

if (!function_exists('admin_encode')) {
    function admin_encode($str): string
    {
        return Crypt::encryptString($str);  // webman
    }
}

if (!function_exists('admin_decode')) {
    function admin_decode($decodeStr): string
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
    function file_upload_handle(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        $storage = \Shopwwi\WebmanFilesystem\Facade\Storage::adapter(\plugin\jzadmin\app\Admin::config('admin.upload.disk')); // webman

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
    function file_upload_handle_multi(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        $storage = \Illuminate\Support\Facades\Storage::disk(\plugin\jzadmin\app\Admin::config('admin.upload.disk'));

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
    function is_json($string): bool
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('settings')) {
    function settings(): \plugin\jzadmin\app\service\AdminSettingService
    {
        return \plugin\jzadmin\app\service\AdminSettingService::make();
    }
}

if (!function_exists('admin_extension_path')) {
    /**
     * @param string|null $path
     *
     * @return string
     */
    function admin_extension_path(?string $path = ''): string
    {
        $dir = rtrim(config('plugin.jzadmin.admin.extension.dir'), '/') ?: base_path('extensions');

        $path = ltrim($path, '/');

        return $path ? $dir . '/' . $path : $dir;
    }
}

if (!function_exists('admin_user')) {
    function admin_user(): \plugin\jzadmin\app\model\AdminUser|\Illuminate\Contracts\Auth\Authenticatable|null
    {
        return \plugin\jzadmin\app\Admin::user();
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
    function admin_abort(string $message = '', array $data = [], int $doNotDisplayToast = 0): mixed
    {
        throw new \plugin\jzadmin\app\exception\AdminException($message, $data, $doNotDisplayToast);
    }

    function amis_abort($message = '', $data = []): void
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
    function admin_abort_if($flag, $message = '', $data = [], $doNotDisplayToast = 0): void
    {
        if ($flag) {
            admin_abort($message, $data, $doNotDisplayToast);
        }
    }

    function amis_abort_if($flag, $message = '', $data = []): void
    {
        admin_abort_if($flag, $message, $data, 1);
    }
}

if (!function_exists('owl_admin_path')) {
    function owl_admin_path($path = ''): string
    {
        $path = ltrim($path, '/');

        return __DIR__ . '/jzadmin/' . $path;
    }
}

if (!function_exists('admin_pages')) {
    function admin_pages($sign)
    {
        return \plugin\jzadmin\app\service\AdminPageService::make()->get($sign);
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
    function map2options($map): array
    {
        return collect($map)->map(fn($v, $k) => ['label' => $v, 'value' => $k])->values()->toArray();
    }
}

// webman 版本
if (!function_exists('admin_trans')) {
    function admin_trans(string|null $key = null, array $replace = [], string|null $locale = null): ?string
    {
        if (is_null($key)) {
            return $key;
        }
        $arr = explode('.', $key);
        return trans(str_replace($arr[0] . '.', '', $key), $replace, $arr[0], $locale);
    }
}
