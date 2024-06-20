<?php

namespace plugin\owladmin\app\utils;

use Closure;
use support\Cache as SymfonyCache;

class Cache
{

    private string $prefix = 'owladmin_';

    public function rememberForever(string $key, Closure $callback)
    {
        $value = $callback($key);
        if (symfonyCache::set($key, $value)) {
            return $value;

        };
        return null;
    }

    public function forget(string $key): bool
    {
        return symfonyCache::delete($this->getKey($key));
    }

    public function delete(string $key): bool
    {
        return SymfonyCache::delete($this->getKey($key));
    }

    public function put(string $key, $getCaptcha, int $int): bool
    {
        return SymfonyCache::set($this->getKey($key), $getCaptcha, $int);
    }

    public function has(string $key): bool
    {
        return SymfonyCache::has($this->getKey($key));
    }

    public function forever(string $key, bool $true): bool
    {
        return SymfonyCache::set($this->getKey($key), $true);
    }

    public function pull(string $key): ?string
    {
        $key = $this->getKey($key);
        if (!$this->has($key)) {
            return null;
        }
        $res = $this->get($key);
        $this->delete($key);
        return $res;
    }

    public function get($key)
    {
        return SymfonyCache::get($this->getKey($key));
    }

    private function getKey(string $key): string
    {
        if (isset(request()->tenant)) {
            return $this->prefix . 'tenant_' . request()->tenant . '_' . $key;
        }
        return $this->prefix . $key;
    }
}
