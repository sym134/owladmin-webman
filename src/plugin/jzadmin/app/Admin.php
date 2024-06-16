<?php

namespace plugin\jzadmin\app;

use support\Db;
use Composer\Autoload\ClassLoader;
use Shopwwi\WebmanAuth\Facade\Auth;
use plugin\jzadmin\app\extend\Manager;
use plugin\jzadmin\app\support\Context;
use plugin\jzadmin\app\support\Composer;
use plugin\jzadmin\app\trait\AssetsTrait;
use plugin\jzadmin\app\support\Cores\Menu;
use plugin\jzadmin\app\support\Cores\Module;
use Psr\Container\NotFoundExceptionInterface;
use plugin\jzadmin\app\extend\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use plugin\jzadmin\app\service\AdminSettingService;
use plugin\jzadmin\app\support\Cores\Permission;
use plugin\jzadmin\app\support\Cores\JsonResponse;
use plugin\jzadmin\app\model\{AdminMenu, AdminRole, AdminUser, AdminPermission};

class Admin
{
    use AssetsTrait;

    public static function make(): static
    {
        return new static();
    }

    // public static function boot(): void
    // {
    //     Relationships::boot();
    //     Api::boot();

        // Sanctum 指定模型表
        // if (class_exists(Sanctum::class)) {
        //     Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        // }

        //Octane 清空sql记录
        // if (class_exists('\Laravel\Octane\Events\RequestReceived')) {
        //     Event::listen(\Laravel\Octane\Events\RequestReceived::class, function ($event) {
        //         SqlRecord::$sql = [];
        //     });
        // }
    // }

    public static function response(): JsonResponse
    {
        return new JsonResponse();
    }

    /**
     * @return Menu;
     */
    public static function menu(): Menu
    {
        return appw('admin.menu');
    }

    /**
     * @return Permission
     */
    public static function permission(): Permission
    {
        return new Permission;
    }

    public static function guard()
    {
        return Auth::guard(self::config('admin.auth.guard') ?: 'admin');
    }

    /**
     * @return AdminUser|null
     */
    public static function user(): ?AdminUser
    {
        return static::guard()->user();
    }

    /**
     * @param string|null $name
     *
     * @return Manager|ServiceProvider|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function extension(?string $name = ''): Manager|ServiceProvider|null
    {
        if ($name) {
            return appw('admin.extend')->get($name);
        }

        return appw('admin.extend');
    }

    // TODO 原组件
    public static function classLoader(): ClassLoader
    {
        return Composer::loader();
    }

    /**
     * 上下文管理.
     *
     * @return Context
     */
    public static function context(): Context
    {
        return appw('admin.context');
    }

    /**
     * @return AdminSettingService
     */
    public static function setting(): AdminSettingService
    {
        // return appw('admin.setting');
        return settings();
    }

    /**
     * 往分组插入中间件.
     *
     * @param array $mix
     */
    public static function mixMiddlewareGroup(array $mix = []): void
    {
        // Route::mixMiddlewareGroup($mix);
    }

    /**
     * @return string
     */
    public static function adminMenuModel():string
    {
        return self::config('admin.models.admin_menu', AdminMenu::class);
    }

    /**
     * @return string
     */
    public static function adminPermissionModel(): string
    {
        return self::config('admin.models.admin_permission', AdminPermission::class);
    }

    /**
     * @return string
     */
    public static function adminRoleModel(): string
    {
        return self::config('admin.models.admin_role', AdminRole::class);
    }

    /**
     * @return string
     */
    public static function adminUserModel(): string
    {
        return self::config('admin.models.admin_user', AdminUser::class);
    }

    /**
     * @return Module
     */
    public static function module(): Module
    {
        return appw('admin.module');
    }

    /**
     * 当前模块
     *
     * @param bool $lower
     *
     * @return mixed|string|null
     */
    public static function currentModule(bool $lower = false): mixed
    {
        return Module::current($lower);
    }

    public static function config($key, $default = '')
    {
        $key = 'plugin.jzadmin.' . $key; // webman
        if ($module = self::currentModule(true)) {
            return config($module . '.' . $key, $default);
        }

        return config($key, $default);
    }

    // 替换后台视图api
    public static function view($apiPrefix = ''): array|string|null
    {
        if (!$apiPrefix) {
            $apiPrefix = self::config('admin.route.prefix');
        }

        if (is_file(public_path('admin-assets/index.html'))) {
            $view = file_get_contents(public_path('admin-assets/index.html'));
        } else {
            $view = file_get_contents(base_path('vendor/jizhi/jzadmin/src/admin-assets/index.html'));
        }

        $script = '<script>window.$adminApiPrefix = "/' . $apiPrefix . '"</script>';

        return preg_replace('/<script>window.*?<\/script>/is', $script, $view);
    }

    public static function hasTable($table): bool
    {
        $key = 'admin_has_table_' . $table;
        if (cache()->has($key)) {
            return true;
        }

        $has = Db::schema()->hasTable($table);

        if ($has) {
            cache()->forever($key, true);
        }

        return $has;
    }
}
