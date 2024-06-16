<?php

namespace plugin\owladmin\app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    protected OutputInterface $output;
    protected InputInterface $input;
    protected SymfonyStyle $io;


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->input = $input;
        $this->io = new SymfonyStyle($input, $output);
        return $this->handle($input, $output);
    }

    protected function line(string $string): void
    {
        $this->output->writeln($string);
    }

    protected function warn(string $string): void
    {
        $this->io->warning($string);
    }

    abstract public function handle(InputInterface $input, OutputInterface $output);

    protected function option($name)
    {
        return $this->input->getOption($name);
    }

    protected function call(string $command): void
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
