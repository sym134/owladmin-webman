<?php

namespace plugin\jzadmin\command;

use Illuminate\Console\OutputStyle;
use plugin\jzadmin\migrations\Migrator;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends AbstractCommand
{
    protected static $defaultName = 'migrate:run';

    protected BaseMigrator $migrator;
    protected DatabaseMigrationRepository $repository;

    protected function configure()
    {
        $this
            ->setDescription('Run migrations')
            ->addOption('dry-run', 'x', InputOption::VALUE_NONE, 'Dump query to standard output instead of executing it')
            ->addOption('step', 's', InputOption::VALUE_REQUIRED, 'Force the migrations to be run so they can be rolled back individually', 1)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'file path')
            ->setHelp('Runs all available migrations' . PHP_EOL);

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        if (!$this->confirmToProceed()) {
            return 0;
        }
        $migrationPath = $this->getMigrationPath();
        if ($input->getOption('path')) {
            $migrationPath = $migrationPath . DIRECTORY_SEPARATOR . $input->getOption('path');
        }
        $this->repository = new DatabaseMigrationRepository($this->getDb(), $this->getMigrationTable());
        $this->migrator = new Migrator($this->repository, $this->getDb(), new Filesystem());
        $this->migrator->setOutput($output);

        $this->migrator->usingConnection($this->database, function () use ($output, $input, $migrationPath) {
            $this->prepareDatabase();

            // Next, we will check to see if a path option has been defined. If it has
            // we will use the path relative to the root of this installation folder
            // so that migrations may be run for any path within the applications.
            $this->migrator->setOutput(new OutputStyle($input, $output))
                ->run([$migrationPath], [
                    'pretend' => $this->input->getOption('dry-run'),
                    'step'    => (int)$this->input->getOption('step'),
                ]);
        });

        return 0;
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if (!$this->migrator->repositoryExists()) {
            $this->call('migrate:install', array_filter([
                '--database' => $this->database,
            ]));
        }
    }
}
