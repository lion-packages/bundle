<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Command\Command;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use LogicException;
use stdClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Empties all entities of the defined databases
 *
 * @package Lion\Bundle\Commands\Lion\Migrations
 */
class EmptyMigrationsCommand extends MenuCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('migrate:empty')
            ->setDescription('Empties all tables built with the migrations');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int [0 if everything went fine, or an exit code]
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connections = Connection::getConnections();

        foreach ($connections as $connectionName => $connection) {
            $tables = $this->getTables($connectionName);

            if (!is_array($tables) && isSuccess($tables)) {
                $output->writeln($this->warningOutput("\t>> MIGRATION: no tables available"));

                continue;
            }

            if (!is_array($tables) && isError($tables)) {
                $output->writeln($this->errorOutput("\t>> MIGRATION: {$tables->message}"));

                continue;
            }

            /** @var stdClass $table */
            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_{$connection['dbname']}"};

                $response = $this
                    ->truncateTable($connection['type'], $connectionName, $tableName)
                    ->execute();

                $output->writeln(
                    $this->warningOutput(
                        "\t>> MIGRATION: {$connection['dbname']}.{$tableName} [{$connection['type']}]"
                    )
                );

                if (isError($response)) {
                    $output->writeln($this->errorOutput("\t>> MIGRATION: {$response->message}"));
                } else {
                    $output->writeln($this->successOutput("\t>> MIGRATION: {$response->message}"));
                }
            }
        }

        $output->writeln($this->infoOutput("\n\t>> all tables have been truncated"));

        return Command::SUCCESS;
    }

    /**
     * Empty the available tables
     *
     * @param string $driver [Database engine]
     * @param string $connectionName [Connection name]
     * @param string $table [Name the table]
     *
     * @return RunDatabaseProcessesInterface|null
     */
    private function truncateTable(
        string $driver,
        string $connectionName,
        string $table
    ): ?RunDatabaseProcessesInterface {
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
