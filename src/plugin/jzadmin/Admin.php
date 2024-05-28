<?php

namespace plugin\jzadmin;

use Shopwwi\WebmanAuth\Facade\Auth;
use plugin\jzadmin\extend\Manager;
use plugin\jzadmin\trait\AssetsTrait;
use plugin\jzadmin\extend\ServiceProvider;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use plugin\jzadmin\service\AdminSettingService;
use plugin\jzadmin\model\{AdminMenu, AdminRole, AdminUser, AdminPermission};
use plugin\jzadmin\support\{Context, Composer, Cores\Menu, Cores\Route, Cores\Permission, Cores\JsonResponse};

class Admin
{
    use AssetsTrait;

    public static function make(): static
    {
        return new static();
    }

    public static function response()
    {
        return new JsonResponse();
    }

    /**
     * @return \plugin\jzadmin\support\Cores\Menu;
     */
    public static function menu()
    {
        return new Menu;
    }

    /**
     * @return Permission
     */
    public static function permission()
    {
        return new Permission;
    }

    public static function guard()
    {
        return Auth::guard(self::config('admin.auth.guard') ?: 'admin');
    }

    /**
     * @return AdminUser|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function user()
    {
        /** @var AdminUser|null|\Illuminate\Contracts\Auth\Authenticatable $user */
        $user = static::guard()->user();
        return $user;
    }

    public static function bootstrap()
    {
        $file = self::config('admin.bootstrap');

        if (is_file($file)) {
            require self::config('admin.bootstrap');
        }
    }

    /**
     * 加载框架路由
     *
     * @return void
     */
    public static function loadBaseRoute()
    {
        Route::baseLoad();
    }

    /**
     * @param string|null $name
     *
     * @return Manager|ServiceProvider|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function extension(?string $name = '')
    {
        if ($name) {
            return appw('admin.extend')->get($name);
        }

        return appw('admin.extend');
    }

    public static function classLoader()
    {
        return Composer::loader();
    }

    /**
     * 上下文管理.
     *
     * @return Context
     */
    public static function context()
    {
        // return new Context;
        return appw('admin.context');
    }

    /**
     * @return \Closure|AdminSettingService
     */
    public static function setting()
    {
        // return appw('admin.setting');
        return settings();
    }

    /**
     * 往分组插入中间件.
     *
     * @param array $mix
     */
    public static function mixMiddlewareGroup(array $mix = [])
    {
        $router = appw('router');
        $group = $router->getMiddlewareGroups()['admin'] ?? [];

        if ($mix) {
            $finalGroup = [];

            foreach ($group as $i => $mid) {
                $next = $i + 1;

                $finalGroup[] = $mid;

                if (!isset($group[$next]) || $group[$next] !== 'admin.permission') {
                    continue;
                }

                $finalGroup = array_merge($finalGroup, $mix);

                $mix = [];
            }

            if ($mix) {
                $finalGroup = array_merge($finalGroup, $mix);
            }

            $group = $finalGroup;
        }

        $router->middlewareGroup('admin', $group);
    }

    /**
     * @return AdminMenu
     */
    public static function adminMenuModel()
    {
        return self::config('admin.models.admin_menu', AdminMenu::class);
    }

    /**
     * @return AdminPermission
     */
    public static function adminPermissionModel()
    {
        return self::config('admin.models.admin_permission', AdminPermission::class);
    }

    /**
     * @return AdminRole
     */
    public static function adminRoleModel()
    {
        return self::config('admin.models.admin_role', AdminRole::class);
    }

    /**
     * @return AdminUser
     */
    public static function adminUserModel()
    {
        return self::config('admin.models.admin_user', AdminUser::class);
    }

    /**
     * 当前模块
     *
     * @param bool $lower
     *
     * @return mixed|string|null
     */
    public static function currentModule(bool $lower = false)
    {
        $prefix = data_get(explode('/', is_null(request()) ? '' : request()->path()), 0);
        if ($prefix) {
            $modules = config('admin.modules');
            if (count($modules)) {
                $_list = collect($modules)
                    ->combine($modules)
                    ->map(fn($item) => config(strtolower($item) . '.admin.route.prefix', ''))
                    ->flip()
                    ->toArray();

                if (key_exists($prefix, $_list)) {
                    return $lower ? strtolower($_list[$prefix]) : $_list[$prefix];
                }
            }
        }

        return null;
    }

    public static function config($key, $default = '')
    {
        $key = 'plugin.jizhi.jz-admin.' . $key;
        if ($module = self::currentModule(true)) {
            return config($module . '.' . $key, $default);
        }

        return config($key, $default);
    }
}
