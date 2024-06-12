<?php

namespace plugin\jzadmin\app\command;

class KeyGenerate extends BaseCommand
{
    protected static $defaultName = 'key:generate';
    protected static $defaultDescription = '使用 PHP 的安全随机字节生成器为你的应用程序构建加密安全密钥';

    function handle()
    {
        // 读取原始配置文件
        $configPath = base_path() . '/config/app.php';
        $config = require $configPath;
        // 添加新的键值对
        $config['app_key'] = 'base64:' . base64_encode(generateRandomString());
        // 将更新后的配置写回到文件
        file_put_contents($configPath, '<?php return ' . var_export($config, true) . ';');
    }
}
