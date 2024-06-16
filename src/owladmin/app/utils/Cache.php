<?php

namespace plugin\owladmin\app\utils;

use Closure;
use support\Cache as SymfonyCache;

class Cache
{

    public function rememberForever(string $key, Closure $callback)
    {
        $value = $callback($key);
        if (symfonyCache::set($key, $value)) {
            return $value;

        };
        return null;
    }

    public function forget(string $cacheKey): bool
    {
        return symfonyCache::delete($cacheKey);
    }

    public function delete(string $string): bool
    {
        return SymfonyCache::delete($string);
    }

    public function put(string $sys_captcha, $getCaptcha, int $int): bool
    {
        return SymfonyCache::set($sys_captcha, $getCaptcha, $int);
    }

    public function has(string $key): bool
    {
        return SymfonyCache::has($key);
    }

    public function forever(string $key, bool $true): bool
    {
        return SymfonyCache::set($key, $true);
    }

    public function pull(string $key): ?string
    {
        if (!$this->has($key)) {
            return null;
        }
        $res = $this->get($key);
        $this->delete($key);
        return $res;
    }

    public function get($key)
    {
        return SymfonyCache::get($key);
    }
}
