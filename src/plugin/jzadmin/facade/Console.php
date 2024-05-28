<?php

namespace plugin\jzadmin\facade;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Console
{
    protected $application;

    public function __construct()
    {
        // 创建控制台应用程序实例
        $application = new Application();
        $application->setName('Test Console App');
        $application->setVersion('1.0.0');
        //$application->setAutoExit(false);
        $this->application = $application;
    }

    public function add(string $command): static
    {
        // 向应用程序实例注册命令
        $this->application->add(new $command);
        return $this;
    }

    public function run(string $command)
    {
        $input = new StringInput($command);
        $output = new BufferedOutput();
        $this->application->run($input, $output);
        return $output->fetch();
    }

    public static function instance(): Console
    {
        return new static();
    }
}
