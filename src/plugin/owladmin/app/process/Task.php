<?php

namespace plugin\owladmin\app\process;

use Workerman\Crontab\Crontab;
use plugin\owladmin\app\service\system\AdminCrontabService;

class Task
{
    public function onWorkerStart(): void
    {
        $service = new AdminCrontabService();
        $taskList = $service->getModel()->where('task_status', 1)->get();

        foreach ($taskList as $item) {
            new Crontab($item->rule, function () use ($service, $item) {
                $service->run($item->id);
            });
        }
    }

    public function run($item): string
    {
        echo '任务调用：' . date('Y-m-d H:i:s') . "\n";
        var_dump($item->name . '参数:' . ($item->rule));
        return '任务调用：' . date('Y-m-d H:i:s') . "\n";
    }
}
