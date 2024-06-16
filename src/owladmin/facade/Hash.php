<?php

namespace plugin\owladmin\facade;

use Illuminate\Hashing\ArgonHasher;
use Illuminate\Hashing\BcryptHasher;
use support\Container;

/**
 * @method static \Illuminate\Hashing\BcryptHasher createBcryptDriver()
 * @method static \Illuminate\Hashing\ArgonHasher createArgonDriver()
 * @method static \Illuminate\Hashing\Argon2IdHasher createArgon2idDriver()
 * @method static array info(string $hashedValue)
 * @method static string make(string $value, array $options = [])
 * @method static bool check(string $value, string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static string getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Illuminate\Hashing\HashManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Illuminate\Hashing\HashManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \Illuminate\Hashing\HashManager forgetDrivers()
 *
 * @see \Illuminate\Hashing\HashManager
 * @see \Illuminate\Hashing\AbstractHasher
 */
class Hash
{
    public static function instance()
    {
        return Container::make(BcryptHasher::class, [[
            'rounds' => 10,
        ]]);
    }

    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(...$arguments);
    }
}
