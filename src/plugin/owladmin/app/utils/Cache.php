<?php

namespace plugin\owladmin\app\utils;

use Closure;
use support\Cache as SymfonyCache;

class Cache
{

    private static string $prefix = 'owladmin_';

    public static function rememberForever(string $key, Closure $callback)
    {
        $value = $callback($key);
        if (symfonyCache::set($key, $value)) {
            return $value;
        }
        return null;
    }

    public static function forget(string $key): bool
    {
        return symfonyCache::delete(self::getKey($key));
    }

    public static function delete(string $key): bool
    {
        return SymfonyCache::delete(self::getKey($key));
    }

    public static function put(string $key, $getCaptcha, int $int): bool
    {
        return SymfonyCache::set(self::getKey($key), $getCaptcha, $int);
    }

    public static function has(string $key): bool
    {
        return SymfonyCache::has(self::getKey($key));
    }

    public static function forever(string $key, bool $true): bool
    {
        return SymfonyCache::set(self::getKey($key), $true);
    }

    public static function pull(string $key): ?string
    {
        if (!self::has($key)) {
            return null;
        }
        $res = self::get($key);
        self::delete($key);
        return $res;
    }

    public static function get($key)
    {
        return SymfonyCache::get(self::getKey($key));
    }

    private static function getKey(string $key): string
    {
        if (isset(request()->tenant)) {
            return self::$prefix . 'tenant_' . request()->tenant . '_' . $key;
        }
        return self::$prefix . $key;
    }
}
