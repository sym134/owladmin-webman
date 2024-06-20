<?php

use plugin\owladmin\app\model\AdminUser;
use plugin\owladmin\app\model\AdminRole;
use plugin\owladmin\app\model\AdminMenu;
use plugin\owladmin\app\model\AdminPermission;

return [
    // 应用名称
    'name'           => 'Owl Admin',
    'app_key'        => env('APP_KEY', ''),

    // 应用 logo
    'logo'           => '/admin-assets/logo.png',

    // 默认头像
    'default_avatar' => '/admin-assets/default-avatar.png',

    // 应用安装目录
    'directory'      => app_path('admin'), // webman

    // 应用路由
    'route'          => [
        'prefix'               => 'admin-api',
        'domain'               => null,
        'namespace'            => 'app\\admin\\controller', // webman
        'middleware'           => ['admin'],
        // 不包含额外路由, 配置后, 不会追加新增/详情/编辑页面路由
        'without_extra_routes' => [
            '/dashboard',
        ],
    ],

    'auth' => [
        // 是否开启验证码
        'login_captcha' => env('ADMIN_LOGIN_CAPTCHA', true),
        // 是否开启认证
        'enable'        => true,
        // 是否开启鉴权
        'permission'    => true,
        'guard'         => 'admin',
        'guards'        => [
            'admin' => [
                'driver'   => 'sanctum',
                'provider' => 'admin',
            ],
        ],
        'providers'     => [
            'admin' => [
                'driver' => 'eloquent',
                'model'  => AdminUser::class, // webman
            ],
        ],
        'except'        => [

        ],
    ],

    'upload' => [
        'disk'      => 'public',
        // 文件上传目录
        'directory' => [
            'image' => 'images',
            'file'  => 'files',
            'rich'  => 'rich',
        ],
    ],

    'https'                                => env('ADMIN_HTTPS', false),

    // 是否显示 [开发者工具]
    'show_development_tools'               => env('ADMIN_SHOW_DEVELOPMENT_TOOLS', true),

    // 是否显示 [权限] 功能中的自动生成按钮
    'show_auto_generate_permission_button' => env('ADMIN_SHOW_AUTO_GENERATE_PERMISSION_BUTTON', true),

    // 扩展
    'extension'                            => [
        'dir' => base_path('plugin'),
    ],

    'layout' => [
        // 浏览器标题, 功能名称使用 %title% 代替
        'title'              => '%title% | OwlAdmin',
        'header'             => [
            // 是否显示 [刷新] 按钮
            'refresh'       => true,
            // 是否显示 [暗色模式] 按钮
            'dark'          => true,
            // 是否显示 [全屏] 按钮
            'full_screen'   => true,
            // 是否显示 [多语言] 按钮
            'locale_toggle' => true,
            // 是否显示 [主题配置] 按钮
            'theme_config'  => true,
        ],
        // 多语言选项
        'locale_options'     => [
            'en'    => 'English',
            'zh_CN' => '简体中文',
        ],
        /*
         * keep_alive 页面缓存黑名单
         *
         * eg:
         * 列表: /user
         * 详情: /user/:id
         * 编辑: /user/:id/edit
         * 新增: /user/create
         */
        'keep_alive_exclude' => [],
        // 底部信息
        'footer'             => '<a href="https://github.com/slowlyo/owl-admin" target="_blank">Owl Admin</a>',
    ],

    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
    ],

    'models' => [
        'admin_user'       => AdminUser::class,
        'admin_role'       => AdminRole::class,
        'admin_menu'       => AdminMenu::class,
        'admin_permission' => AdminPermission::class,
    ],

    'modules' => [
    ],
];
