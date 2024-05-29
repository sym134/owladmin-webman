<?php

namespace plugin\jzadmin\migrations;

class Migrator extends \Illuminate\Database\Migrations\Migrator
{
    /**
     * Resolve a migration instance from a file.
     *
     * @param  string  $file
     * @return object
     */
    public function resolve($file)
    {
        $migration = parent::resolve($file);
        $migration->db = $this->resolveConnection(
            $migration->getConnection()
        );
        return $migration;
    }

    /**
     * Resolve a migration instance from a migration path.
     *
     * @param  string  $path
     * @return object
     */
    protected function resolvePath(string $path)
    {
        $migration = parent::resolvePath($path);
        $migration->db = $this->resolveConnection(
            $migration->getConnection()
        );
        return $migration;
    }

    public function rollback($paths = [], array $options = [])
    {
        $migration = $options['migration'] ?? null;
        if ($migration !== null) {
            $migrations = [
                [
                    'migration' => $migration,
                ],
            ];
        } else {
            // We want to pull in the last batch of migrations that ran on the previous
            // migration operation. We'll then reverse those migrations and run each
            // of them "down" to reverse the last migration "operation" which ran.
            $migrations = $this->getMigrationsForRollback($options);
        }

        if (count($migrations) === 0) {
            $this->note('<info>Nothing to rollback.</info>');

            return [];
        }

        return $this->rollbackMigrations($migrations, $paths, $options);
    }
}
