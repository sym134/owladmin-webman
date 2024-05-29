<?php

namespace plugin\jzadmin\command;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateInstall extends AbstractCommand
{
    protected static $defaultName = 'migrate:install';

    protected function configure()
    {
        $this
            ->setDescription('Create the migration repository')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production')
            ->setHelp('Create the migration repository' . PHP_EOL);

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        if (! $this->confirmToProceed()) {
            return 0;
        }

        $repository = new DatabaseMigrationRepository($this->getDb(), $this->getMigrationTable());
        $repository->setSource($this->database);
        $repository->createRepository();

        $this->output->writeln('<info>Migration table created successfully.</info>');

        return 0;
    }
}
