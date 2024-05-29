<?php

namespace plugin\jzadmin\facade;

use Illuminate\Encryption\Encrypter;
use Shopwwi\WebmanAuth\Facade\Str;
use support\Container;

/**
 * @method static bool supported(string $key, string $cipher)
 * @method static string generateKey(string $cipher)
 * @method static string encrypt(mixed $value, bool $serialize = true)
 * @method static string encryptString(string $value)
 * @method static mixed decrypt(string $payload, bool $unserialize = true)
 * @method static string decryptString(string $payload)
 * @method static string getKey()
 *
 * @see \Illuminate\Encryption\Encrypter
 */
class Crypt
{
    public static function instance(): Encrypter
    {
        $key = config('app.app_key');
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return Container::make(Encrypter::class,[$key,'aes-256-cbc']);
    }

    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(...$arguments);
    }
}
