owladmin-webman
==================

基于 [OwlAdmin](https://github.com/slowlyo/owl-admin) 修改的 Webman 扩展包。


## webman 安装

```shell
composer create-project workerman/webman
cd webman
```

## 依赖注入
```shell
composer require psr/container ^1.1.1 php-di/php-di ^6 doctrine/annotations ^1.14
```

## 数据库配置文件位置为 config/database.php
```shell
return [
 // 默认数据库
 'default' => 'mysql',
 // 各种数据库配置
 'connections' => [

     'mysql' => [
         'driver'      => 'mysql',
         'host'        => '127.0.0.1',
         'port'        => 3306,
         'database'    => 'webman',
         'username'    => 'webman',
         'password'    => '',
         'unix_socket' => '',
         'charset'     => 'utf8',
         'collation'   => 'utf8_unicode_ci',
         'prefix'      => '',
         'strict'      => true,
         'engine'      => null,
     ],
 ],
];
```

## 安装

```shell
composer require jizhi/owladmin-webman
```

## 配置 .env

```env
# 语言
APP_LOCALE=zh_CN

# admin 登录验证码
ADMIN_LOGIN_CAPTCHA=true
# admin https
ADMIN_HTTPS=false
# admin开发工具
ADMIN_SHOW_DEVELOPMENT_TOOLS=true
# 显示自动生成权限按钮
ADMIN_SHOW_AUTO_GENERATE_PERMISSION_BUTTON=true
DB_CONNECTION=mysql
```

## 配置auth config/plugin/shopwwi/auth/app.php

```php
 return [
     'enable' => true,
     'app_key' => 'base64:N721v3Gt2I58HH7oiU7a70PQ+i8ekPWRqwI+JSnM1wo=',
     'guard' => [
    // ........
         // 添加 admin
         'admin' => [
             'key' => 'id',
             'field' => ['id','name','email','mobile'], //设置允许写入扩展中的字段
             'num' => 0, //-1为不限制终端数量 0为只支持一个终端在线 大于0为同一账号同终端支持数量 建议设置为1 则同一账号同终端在线1个
             'model'=> \plugin\owladmin\app\model\AdminUser::class
         ]
     ],
    // ........
```

## 数据库安装
```shell
php webman migrate:run
```

## 运行

```shell
php start.php start
```

## 访问

http://127.0.0.1:8780/admin

