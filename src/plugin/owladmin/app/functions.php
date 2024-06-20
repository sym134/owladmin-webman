<?php
/**
 * Here is your custom functions.
 */

use support\Container;

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

if (!function_exists('runCommand')) {
    // 执行命令
    function runCommand(string $commandName, array $arguments = []): array
    {
        $array = explode(' ', 'php webman ' . $commandName);
        $array = array_merge($array, $arguments);
        // 创建进程对象
        $process = new Symfony\Component\Process\Process($array);
        // 执行命令
        $process->run();
        var_dump($process->getCommandLine());
        return [$process->isSuccessful(), $process->getOutput()];
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
