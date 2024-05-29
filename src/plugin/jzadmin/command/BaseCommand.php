<?php

namespace plugin\jzadmin\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

abstract class BaseCommand extends Command
{
    protected OutputInterface $output;
    protected InputInterface $input;
    private SymfonyStyle $io;

    protected function configure()
    {
        // 在这里配置通用的命令参数和选项
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->input = $input;
        $this->io = new SymfonyStyle($input, $output);
        $this->handle();
        return self::SUCCESS;
    }

    protected function line(string $string): void
    {
        $this->output->writeln($string);
    }

    protected function warn(string $string): void
    {
        $this->io->warning($string);
    }

    abstract public function handle();

    protected function option($name)
    {
        return $this->input->getOption($name);
    }

    protected function call(string $command)
    {
        // 获取当前应用程序实例
        $application = $this->getApplication();
        // 使用 find 方法获取其他命令的实例
        $otherCommand = $application->find($command); // 替换为实际的命令名称
        $otherCommandInput = new \Symfony\Component\Console\Input\ArrayInput([]);
        // 调用其他命令的 run 方法执行它
        $otherCommand->run($otherCommandInput, $this->output);
    }
}
