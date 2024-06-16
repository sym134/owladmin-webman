<?php

namespace plugin\jzadmin\console;

use plugin\jzadmin\app\Admin;
use Illuminate\Console\Command;
use plugin\jzadmin\app\support\Cores\Database;

class InstallCommand extends Command
{
    protected $signature = 'admin:install';

    protected $description = 'Install OwlAdmin';

    protected string $directory;

    public function handle(): void
    {
        $this->initDatabase();
        $this->initAdminDirectory();
    }

    public function initDatabase(): void
    {
        $this->call('migrate');

        if (Admin::adminUserModel()::query()->count() == 0) {
            Database::make()->fillInitialData();
        }
    }

    protected function setDirectory(): void
    {
        $this->directory = config('plugin.jzadmin.admin.directory');
    }

    protected function initAdminDirectory(): void
    {
        $this->setDirectory();

        if (is_dir($this->directory)) {
            $this->warn("{$this->directory} directory already exists !");

            return;
        }

        $this->makeDir('/');
        $this->line('<info>Admin directory was created:</info> ' . str_replace(base_path(), '', $this->directory));

        $this->makeDir('Controllers');


        $this->createAuthController();
        $this->createBootstrapFile();
        $this->createRoutesFile();
        $this->createHomeController();
        $this->createSettingController();
    }

    protected function makeDir($path = '')
    {
        $this->laravel['files']->makeDirectory("{$this->directory}/$path", 0755, true, true);
    }

    public function createAuthController(): void
    {
        $authController = $this->directory . '/Controllers/AuthController.php';
        $contents       = $this->getStub('AuthController');
        $this->laravel['files']->put(
            $authController,
            str_replace('{{Namespace}}', $this->getNamespace('Controllers'), $contents)
        );
        $this->line('<info>AuthController file was created:</info> ' . str_replace(base_path(), '', $authController));
    }

    protected function createBootstrapFile(): void
    {
        $file = $this->directory . '/bootstrap.php';

        $contents = $this->getStub('bootstrap');
        $this->laravel['files']->put($file, $contents);
        $this->line('<info>Bootstrap file was created:</info> ' . str_replace(base_path(), '', $file));
    }

    protected function createRoutesFile(): void
    {
        $file = $this->directory . '/routes.php';

        $contents = $this->getStub('routes');
        $this->laravel['files']->put($file,
            str_replace('{{Namespace}}', $this->getNamespace('Controllers'), $contents));
        $this->line('<info>Routes file was created:</info> ' . str_replace(base_path(), '', $file));
    }

    public function createHomeController(): void
    {
        $homeController = $this->directory . '/Controllers/HomeController.php';
        $contents       = $this->getStub('HomeController');
        $this->laravel['files']->put(
            $homeController,
            str_replace('{{Namespace}}', config('plugin.jzadmin.admin.route.namespace'), $contents)
        );
        $this->line('<info>HomeController file was created:</info> ' . str_replace(base_path(), '', $homeController));
    }

    public function createSettingController(): void
    {
        $settingController = $this->directory . '/Controllers/SettingController.php';
        $contents          = $this->getStub('SettingController');
        $this->laravel['files']->put(
            $settingController,
            str_replace('{{Namespace}}', config('plugin.jzadmin.admin.route.namespace'), $contents)
        );
        $this->line('<info>SettingController file was created:</info> ' . str_replace(base_path(),
                '',
                $settingController));
    }

    protected function getNamespace($name = null): string
    {
        $base = str_replace('\\controller', '\\', config('plugin.jzadmin.admin.route.namespace'));

        return trim($base, '\\') . ($name ? "\\{$name}" : '');
    }

    protected function getStub($name): string
    {
        return $this->laravel['files']->get(__DIR__ . "/stubs/{$name}.stub");
    }
}
