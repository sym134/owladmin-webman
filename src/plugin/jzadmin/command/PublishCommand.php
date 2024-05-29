<?php

namespace plugin\jzadmin\command;

/**
 * 发布资源
 * PublishCommand
 * plugin\jzadmin\command
 *
 * Author:sym
 * Date:2024/1/21 16:44
 * Company:极智网络科技
 */
class PublishCommand extends BaseCommand
{
    protected static $defaultName = 'admin:publish';
    protected static $defaultDescription = 'admin publish';

    protected function configure()
    {
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->lang();
        $this->database();
        // $this->adminAssets();
    }

    /**
     * 语言包
     *
     * @return void
     *
     * Author:sym
     * Date:2024/1/21 16:44
     * Company:极智网络科技
     */
    private function lang(): void
    {
        $translations = getcwd() . '/resource/translations';
        if (!is_dir($translations)) {
            mkdir($translations, 0755, true);
        }
        $source = getcwd() . '/vendor/jizhi/owl-admin' . '/lang';
        foreach (scandir($source) as $file) {
            if ($file !== '.' && $file !== '..') {
                $sourcePath = $source . '/' . $file;
                $destinationPath = $translations . '/' . $file;

                if (is_dir($sourcePath)) {
                    if (!is_dir($destinationPath)) { // 目录不存在创建
                        mkdir($destinationPath, 0755, true);
                    }
                    $this->copyDirectory($sourcePath, $destinationPath);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            }
        }
    }

    private function database(): void
    {
        $target = getcwd() . '/database';
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        $source = getcwd() . '/vendor/jizhi/owl-admin/database';
        $this->copyDirectory($source, $target);
        file_put_contents($target . '/migrations/2022_08_22_203040_install_slow_admin.php', str_replace('use Illuminate\Database\Migrations\Migration;', 'use plugin\jzadmin\migrations\Migration;', file_get_contents($target . '/migrations/2022_08_22_203040_install_slow_admin.php')));
    }

    /**
     * 静态资源
     *
     * @return void
     *
     * Author:sym
     * Date:2024/1/21 16:44
     * Company:极智网络科技
     */
    private function adminAssets(): void
    {
        $public = getcwd() . '/public/admin-assets';
        if (is_dir($public)) {
            $this->clearDirectory($public);
        }
        mkdir($public, 0755, true);
        $this->copyDirectory(dirname(dirname(__DIR__)) . '/admin-views', getcwd() . '/public/admin-assets');
    }

    private function copyDirectory(string $source, string $destination): void
    {
        foreach (scandir($source) as $file) {
            if ($file !== '.' && $file !== '..') {
                $sourcePath = $source . '/' . $file;
                $destinationPath = $destination . '/' . $file;

                if (is_dir($sourcePath)) {
                    if (!is_dir($destinationPath)){
                        mkdir($destinationPath, 0755, true);
                    }
                    $this->copyDirectory($sourcePath, $destinationPath);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            }
        }
    }

    private function clearDirectory($dir): void
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $path = $dir . '/' . $file;

                if (is_dir($path)) {
                    // 递归删除子目录
                    $this->clearDirectory($path);
                    rmdir($path);
                    echo "目录 $path 删除成功.\n";
                } else {
                    unlink($path);
                    echo "文件 $path 删除成功.\n";
                }
            }
        }
        rmdir($dir);
    }
}
