<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Migrations;

use Closure;
use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Command;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Database\Interface\ExecuteInterface;
use Lion\Files\Store;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manages the processes of creating or executing migrations
 *
 * @package Lion\Bundle\Helpers\Commands\Migrations
 */
class Migrations
{
    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [Stores already loaded migrations]
     *
     * @var array<string, MigrationUpInterface>
     */
    private array $loadedMigrations = [];

    #[Inject]
    public function setStore(Store $store): Migrations
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Sorts the list of elements by the value defined in the INDEX constant
     *
     * @param array<string, MigrationUpInterface|SeedInterface> $list [Class List]
     *
     * @return array<string, MigrationUpInterface|SeedInterface>
     *
     * @internal
     *
     * @codeCoverageIgnore
     */
    public function orderList(array $list): array
    {
        uasort($list, function ($classA, $classB) {
            $namespaceA = $classA::class;

            $namespaceB = $classB::class;

            $indexA = defined("{$namespaceA}::INDEX") ? constant("{$namespaceA}::INDEX") : null;

            $indexB = defined("{$namespaceB}::INDEX") ? constant("{$namespaceB}::INDEX") : null;

            if ($indexA === null && $indexB === null) {
                return 0;
            }

            if ($indexA === null) {
                return 1;
            }

            if ($indexB === null) {
                return -1;
            }

            return $indexA <=> $indexB;
        });

        return $list;
    }

    /**
     * Gets defined migrations categorized by type
     *
     * @return array<string, array<string, MigrationUpInterface>>
     *
     * @internal
     */
    public function getMigrations(): array
    {
        /** @var array<string, array<string, MigrationUpInterface>> $allMigrations */
        $allMigrations = [
            TableInterface::class => [],
            ViewInterface::class => [],
            StoredProcedureInterface::class => [],
        ];

        foreach ($this->store->getFiles('./database/Migrations/') as $migration) {
            if (isSuccess($this->store->validate([$migration], ['php']))) {
                $namespace = $this->store->getNamespaceFromFile($migration, 'Database\\Migrations\\', 'Migrations/');

                if (!isset($this->loadedMigrations[$migration])) {
                    /** @var MigrationUpInterface $migrationInstance */
                    $migrationInstance = new $namespace();

                    $this->loadedMigrations[$migration] = $migrationInstance;
                }

                $tableMigration = $this->loadedMigrations[$migration];

                if ($tableMigration instanceof TableInterface) {
                    $allMigrations[TableInterface::class][$namespace] = $tableMigration;
                }

                if ($tableMigration instanceof ViewInterface) {
                    $allMigrations[ViewInterface::class][$namespace] = $tableMigration;
                }

                if ($tableMigration instanceof StoredProcedureInterface) {
                    $allMigrations[StoredProcedureInterface::class][$namespace] = $tableMigration;
                }
            }
        }

        return $allMigrations;
    }

    /**
     * Run the migrations
     *
     * @param Command $command [Extends the functions of the Command class to
     * format messages with different colors]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array<int, MigrationUpInterface> $files [List of migration files]
     *
     * @return void
     *
     * @internal
     *
     * @codeCoverageIgnore
     */
    public function executeMigrations(Command $command, OutputInterface $output, array $files): void
    {
        foreach ($files as $namespace => $classObject) {
            $response = $classObject->up();

            /** @phpstan-ignore-next-line */
            $message = "\t>> MIGRATION: {$response->message}";

            $output->writeln($command->warningOutput("\t>> MIGRATION: {$namespace}"));

            if (isError($response)) {
                $output->writeln($command->errorOutput($message));
            } else {
                $output->writeln($command->successOutput($message));
            }
        }
    }

    /**
     * Run the migrations
     *
     * @param array<int, class-string> $list [List of classes]
     *
     * @return void
     */
    public function executeMigrationsGroup(array $list): void
    {
        /** @var array<string, array<int, MigrationUpInterface>> $migrations */
        $migrations = [
            TableInterface::class => [],
            ViewInterface::class => [],
            StoredProcedureInterface::class => [],
        ];

        foreach ($list as $namespace) {
            /** @var MigrationUpInterface $classObject */
            $classObject = new $namespace();

            if ($classObject instanceof TableInterface) {
                $migrations[TableInterface::class][$namespace] = $classObject;
            }

            if ($classObject instanceof ViewInterface) {
                $migrations[ViewInterface::class][$namespace] = $classObject;
            }

            if ($classObject instanceof StoredProcedureInterface) {
                $migrations[StoredProcedureInterface::class][$namespace] = $classObject;
            }
        }

        /**
         * @param array<string, MigrationUpInterface> $list
         *
         * @return void
         */
        $run = function (array $list): void {
            foreach ($list as $migration) {
                /** @phpstan-ignore-next-line */
                $migration->up();
            }
        };

        /** @phpstan-ignore-next-line */
        $run($this->orderList($migrations[TableInterface::class]));

        $run($migrations[ViewInterface::class]);

        $run($migrations[StoredProcedureInterface::class]);
    }

    /**
     * Generates static connections to manipulate databases
     *
     * @param Closure $callback [Executes logic to reset databases to their
     * original form]
     *
     * @return void
     *
     * @internal
     */
    public function processingWithStaticConnections(Closure $callback): void
    {
        $originalConnections = Connection::getConnections();

        $backupConnections = $originalConnections;

        /**
         * @param array<string, array{
         *     type: string,
         *     host: string,
         *     port: int,
         *     dbname: string,
         *     user: string,
         *     password: string,
         *     options?: array<int, int>
         * }> $connections
         * @param bool $isReset
         *
         * @return void
         */
        $addConnections = function (array $connections, bool $isReset): void {
            foreach ($connections as $connectionName => $connectionData) {
                Connection::removeConnection($connectionName);

                if ($isReset) {
                    /** @phpstan-ignore-next-line */
                    unset($connectionData['dbname']);
                }

                /** @phpstan-ignore-next-line */
                Connection::addConnection($connectionName, $connectionData);
            }
        };

        $addConnections($originalConnections, true);

        $callback();

        $addConnections($backupConnections, false);
    }

    /**
     * Remove and rebuild all databases
     *
     * @param string $dbName [Database name]
     * @param string $connectionName [Connection name]
     * @param string $type [Driver Type]
     * @param Closure|null $evaluate [Performs the necessary operations during
     * connections to the database server]
     *
     * @return void
     *
     * @throws Exception
     */
    public function resetDatabase(string $dbName, string $connectionName, string $type, ?Closure $evaluate = null): void
    {
        if (Driver::SQLITE === $type) {
            $this->store->remove($dbName);

            null != $evaluate && $evaluate();

            file_put_contents($dbName, '');

            return;
        }

        if (Driver::MYSQL === $type) {
            MySQL::connection($connectionName)
                ->query(
                    <<<SQL
                    DROP DATABASE IF EXISTS `{$dbName}`;
                    SQL
                )
                ->execute();

            null != $evaluate && $evaluate();

            MySQL::connection($connectionName)
                ->query(
                    <<<SQL
                    CREATE DATABASE `{$dbName}`;
                    SQL
                )
                ->execute();

            return;
        }

        if (Driver::POSTGRESQL === $type) {
            PostgreSQL::connection($connectionName)
                ->query(
                    <<<SQL
                    SELECT
                        pg_terminate_backend(pg_stat_activity.pid)
                    FROM pg_stat_activity
                    WHERE datname = '{$dbName}'
                    AND pid <> pg_backend_pid();
                    SQL
                )
                ->execute();

            usleep(500000);

            PostgreSQL::connection($connectionName)
                ->query(
                    <<<SQL
                    DROP DATABASE IF EXISTS "{$dbName}";
                    SQL
                )
                ->execute();

            null != $evaluate && $evaluate();

            PostgreSQL::connection($connectionName)
                ->query(
                    <<<SQL
                    CREATE DATABASE "{$dbName}";
                    SQL
                )
                ->execute();
        }
    }

    /**
     * Empty the available tables
     *
     * @param string $driver [Database engine]
     * @param string $connectionName [Connection name]
     * @param string $table [Name the table]
     *
     * @return ExecuteInterface|null
     *
     * @internal
     *
     * @codeCoverageIgnore
     */
    public function truncateTable(
        string $driver,
        string $connectionName,
        string $table
    ): ?ExecuteInterface {
        if (Driver::MYSQL === $driver) {
            return Schema::connection($connectionName)
                ->truncateTable($table);
        }

        if (Driver::POSTGRESQL === $driver) {
            return PostgreSQL::connection($connectionName)
                ->query(
                    <<<SQL
                    TRUNCATE TABLE {$table} CASCADE;
                    SQL
                );
        }

        return null;
    }
}
