<?php

namespace plugin\jzadmin\app\command;

use plugin\jzadmin\Admin;
use plugin\jzadmin\support\Cores\Database;

class InstallCommand extends BaseCommand
{
    protected static $defaultName = 'admin:install';
    protected static $defaultDescription = 'admin install';
    /**
     * @var array|mixed|null
     */
    private $directory;


    /**
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->initDatabase();
        // $this->initAdminDirectory();
    }

    /**
     * 数据发布
     *
     * @return void
     *
     * Author:sym
     * Date:2024/1/21 20:58
     * Company:极智网络科技
     */
    public function initDatabase(): void
    {
        $this->call('migrate:run');

        if (Admin::adminUserModel()::query()->count() == 0) {
            Database::make()->fillInitialData();
        }
    }

    protected function initAdminDirectory()
    {
        $this->setDirectory();

        if (is_dir($this->directory)) {
            $this->warn("{$this->directory} directory already exists !");
            return;
        }

        $this->makeDir('/');
        $this->line('<info>Admin directory was created:</info> ' . str_replace(base_path(), '', $this->directory));

        $this->editRequest();
        $this->createAuthController();
        $this->createRoutesFile();
        $this->createHomeController();
        $this->createSettingController();
        $this->call('key:generate');
    }

    protected function makeDir($path = '')
    {
        $this->files = mkdir("{$this->directory}/$path", 0777, true);
    }

    protected function setDirectory(): void
    {
        $this->directory = config('plugin.jizhi.jz-admin.admin.directory');
    }

    private function createAuthController()
    {
        $path = $this->directory . '/controller';
        $file_name = '/AuthController.php';
        $contents = $this->getStub('AuthController');
        $this->filePut($path, $file_name, str_replace('{{Namespace}}', $this->getNamespace('controller'), $contents));

        $this->line('<info>AuthController file was created:</info> ' . str_replace(base_path(), '', $path . $file_name));
    }

    /**
     * 创建路由
     *
     * @return void
     *
     * Author:sym
     * Date:2024/1/21 15:37
     * Company:极智网络科技
     */
    protected function createRoutesFile(): void
    {
        $path = $this->directory . '/config';
        $file_name = '/Route.php';
        $contents = $this->getStub('routes');
        $contents = str_replace("[
    'domain'     => config('plugin.jzadmin.admin.route.domain'),
    'prefix'     => config('plugin.jzadmin.admin.route.prefix'),
    'middleware' => config('plugin.jzadmin.admin.route.middleware'),
]", "'/' . config('plugin.jzadmin.admin.route.prefix')", $contents);
        $contents = str_replace('Router $router', '', $contents);
        $contents = str_replace('$router->resource(\'dashboard', "Route::resource('/dashboard", $contents);
        $contents = str_replace('$router->resource(\'system/settings', "Route::resource('/system/settings", $contents);
        $contents = str_replace('use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;', 'use Webman\Route;', $contents);
        $this->filePut($path, $file_name, str_replace('{{Namespace}}', $this->getNamespace('controller'), $contents));
        $this->line('<info>Routes file was created:</info> ' . str_replace(base_path(), '', $path . $file_name));
    }

    /**
     * 创建控制器
     *
     * @return void
     *
     * Author:sym
     * Date:2024/1/21 15:42
     * Company:极智网络科技
     */
    public function createHomeController(): void
    {
        $path = $this->directory . '/controller';
        $file_name = '/HomeController.php';
        $contents = $this->getStub('HomeController');
        // 替换掉laravel
        $contents = str_replace('use Illuminate\Http\Request;', 'use support\Request;', $contents);
        $contents = str_replace('use Illuminate\Http\JsonResponse;', 'use support\Response;', $contents);
        $contents = str_replace('use Illuminate\Http\Resources\Json\JsonResource;', '', $contents);
        $contents = str_replace('JsonResponse|JsonResource', 'Response', $contents);

        $this->filePut($path, $file_name, str_replace('{{Namespace}}', config('plugin.jizhi.jz-admin.admin.route.namespace'), $contents));

        $this->line('<info>HomeController file was created:</info> ' . str_replace(base_path(), '', $path . $file_name));
    }

    /**
     * 创建设置
     *
     * @return void
     *
     * Author:sym
     * Date:2024/1/21 15:42
     * Company:极智网络科技
     */
    public function createSettingController(): void
    {
        $path = $this->directory . '/controller';
        $file_name = '/SettingController.php';
        $contents = $this->getStub('SettingController');
        // 替换掉laravel
        $contents = str_replace('use Illuminate\Http\Request;', 'use support\Request;', $contents);

        $this->filePut($path, $file_name, str_replace('{{Namespace}}', config('plugin.jizhi.jz-admin.admin.route.namespace'), $contents));
        $this->line('<info>SettingController file was created:</info> ' . str_replace(base_path(),
                '',
                $path . $file_name));
    }

    public function editRequest()
    {
        $path = base_path('support/Request.php');
        $content = file_get_contents($path);
        if (!strpos($content,'__get')){
            $content = substr_replace($content, '
    public function __get($name)
            {
        if ($this->has($name)){
            return $this->input($name);
        }
        return parent::__get($name);
    }

    public function query()
    {
        return $this->all();
    }
    
    public function is(string $params): bool
    {
        return \Illuminate\Support\Str::is($params, trim(request()->path(),\'/]\'));
    }
    
    public function has($key): bool
    {
        return !($this->input($key, false) === false);
    }', strlen($content) -2, 0);
            file_put_contents($path, $content);
        }
    }

    protected function filePut($path, $file_name, $content): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        file_put_contents($path . $file_name, $content);
    }


    protected function getStub($name): string
    {
        return file_get_contents(getcwd().'/vendor/jizhi/owl-admin/src' . "/Console/stubs/{$name}.stub");
    }

    protected function getNamespace($name = null): string
    {
        $base = str_replace('\\controller', '\\', config('plugin.jizhi.jz-admin.admin.route.namespace'));

        return trim($base, '\\') . ($name ? "\\{$name}" : '');
    }

}
