<?php

namespace plugin\owladmin\app\service\system;

use plugin\owladmin\app\utils\Cache;

/**
 * 缓存
 * CacheService
 * plugin\owladmin\app\service\system
 *
 * Author:sym
 * Date:2024/6/29 上午7:49
 * Company:极智科技
 */
class CacheService
{

    public static function clear(array $data): void
    {
        foreach ($data as $key => $val) {
            if ($key === 'storage' && $val === 1) {
                settings()->clearCache('storage');
            }
        }
    }
}
