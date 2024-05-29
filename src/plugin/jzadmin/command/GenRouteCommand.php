<?php

namespace plugin\jzadmin\command;

use plugin\jzadmin\service\AdminCodeGeneratorService;
use Symfony\Component\Console\Input\InputOption;

class GenRouteCommand extends BaseCommand
{

    protected static $defaultName = 'admin:gen-route';
    protected static $defaultDescription = 'admin gen-route';

    protected function configure()
    {
        parent::configure();
        $this->addOption('excluded', '-excluded', InputOption::VALUE_REQUIRED, '--excluded选项的值');
    }

    public function handle()
    {

        $content = <<<EOF
// 自动
Route::group('/' . config('plugin.jzadmin.admin.route.prefix'), function () {
_content_
});
EOF;


        $excluded = $this->option('excluded');
        if ($excluded) {
            $excluded = explode(',', $excluded);
        }

        $routes = '';
        AdminCodeGeneratorService::make()
            ->getModel()
            ->query()
            ->when($excluded, fn($query, $excluded) => $query->whereNotIn('id', $excluded))
            ->get()
            ->map(function ($item) use (&$routes) {
                if (!$item->menu_info['enabled']) return;

                $_route = ltrim($item->menu_info['route'], '/');
                $_controller = '\\' . str_replace('/', '\\', $item->controller_name);

                $routes .= <<<EOF
    // {$item->title}
    Route::resource('/{$_route}', {$_controller}::class);

EOF;

            });

        $content = str_replace('_content_', $routes, $content);
        // webman
        $route_content=file_get_contents('config/Route.php');
        $position = strpos($route_content, '// 自动');
        // 如果找到了标记，则替换标记后面的内容
        if ($position !== false) {
            // 从标记位置到字符串结束的所有内容替换为新内容
            $content = substr_replace($route_content, $content, $position, strlen($route_content) - $position);
        } else {
            // 如果没有找到标记，则输出原始文本
            echo "标记未找到";
        }

        // todo 如果是插件就按插件目录生成，如果是应用就按应用目录生成
        file_put_contents(base_path('config/Route.php'), $content); // webman 路由器生成地址

        $this->line('Route file generated successfully.');
    }
}
