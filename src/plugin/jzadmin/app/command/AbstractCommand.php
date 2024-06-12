<?php

namespace plugin\jzadmin\app\command;

use Closure;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class AbstractCommand extends Command
{
    protected string $configFile;
    protected array $config;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected string $environment;
    protected ?string $database;

    protected function configure()
    {
        $this->addOption('config', '-c', InputOption::VALUE_REQUIRED, 'The configuration file to load', 'elmigrator.php');
        $this->addOption('env', '-e', InputOption::VALUE_OPTIONAL, 'Choose an environment');
        $this->addOption('database', '-d', InputOption::VALUE_OPTIONAL, 'The database connection to use');
    }

    protected function bootstrap(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
        $this->loadConfig($input);
    }

    protected function loadConfig(InputInterface $input): void
    {
        $this->configFile = (string)$input->getOption('config');
        $this->config = config('plugin.jzadmin.admin.migrate');
        $this->environment =  $this->config['default_environment'];
        $this->database =  $this->config['database'] ?? null;

        if ($this->configFile === null) {
            $this->output->writeln('<danger>could not find nothing configuration a file. Set throught --config option or environment variable ELMIGRATOR_CONFIG</danger>');
        }
    }

    protected function getMigrationPath(): string
    {
        return (string)$this->config['paths']['migrations'];
    }

    protected function getSeedPath(): string
    {
        return (string)$this->config['paths']['seeds'];
    }

    protected function getDb(): DatabaseManager
    {
        return $this->config['db'];
    }

    protected function environment(): string
    {
        return $this->environment;
    }

    protected function getMigrationTable(): string
    {
        return (string)$this->config['migration_table'];
    }

    protected function table(array $headers, array $contents)
    {
        $table = new Table($this->output);
        $table->setHeaders($headers)
            ->setRows($contents)
            ->render();
    }

    public function confirm(string $message): bool
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($message, false);

        if (!$helper->ask($this->input, $this->output, $question)) {
            return false;
        }
        return true;
    }

    /**
     * Confirm before proceeding with the action.
     *
     * This method only asks for confirmation in production.
     *
     * @param string $warning
     * @param  Closure|bool|null  $callback
     * @return bool
     */
    public function confirmToProceed(string $warning = 'Application In Production!', $callback = null): bool
    {
        $callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;
        $shouldConfirm = $callback instanceof Closure ? call_user_func($callback) : $callback;
        if ($shouldConfirm) {
            if ($this->input->hasOption('force') && $this->input->getOption('force')) {
                return true;
            }
            $this->output->writeln("<fg=yellow>$warning</>");
            $confirmed = $this->confirm('Do you really wish to run this command?');
            if (! $confirmed) {
                $this->output->writeln('<comment>Command Cancelled!</comment>');
                return false;
            }
        }
        return true;
    }

    /**
     * Get the default confirmation callback.
     *
     * @return Closure
     */
    protected function getDefaultConfirmCallback(): Closure
    {
        return function () {
            return $this->environment() === 'production';
        };
    }

    /**
     * Call another console command.
     *
     * @param string $command
     * @param  array   $arguments
     * @return int
     */
    public function call(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;
        $arguments['--config'] = $this->configFile;
        return $this->getApplication()->find($command)->run(
            new ArrayInput($arguments),
            $this->output
        );
    }
}
