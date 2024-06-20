<?php

namespace plugin\owladmin\app\command;

use plugin\owladmin\app\Admin;
use plugin\owladmin\app\support\Cores\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends BaseCommand
{
    protected static $defaultName = 'admin:install';
    protected static $defaultDescription = 'admin install';
    /**
     * @var array|mixed|null
     */
    private mixed $directory;


    /**
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function handle(InputInterface $input, OutputInterface $output): void
    {
        $this->initDatabase();
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

}
