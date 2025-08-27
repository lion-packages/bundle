<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Database\Connection;
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
     * [Manages the processes of creating or executing migrations]
     *
     * @var Migrations $migrations
     */
    private Migrations $migrations;

    #[Inject]
    public function setMigrations(Migrations $migrations): EmptyMigrationsCommand
    {
        $this->migrations = $migrations;

        return $this;
    }

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
     * @return int
     *
     * @throws LogicException [When this abstract method is not implemented]
     *
     * @codeCoverageIgnore
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connections = Connection::getConnections();

        foreach ($connections as $connectionName => $connection) {
            /** @var array<int, stdClass>|stdClass $tables */
            $tables = $this->getTables($connectionName);

            if ($tables instanceof stdClass && isSuccess($tables)) {
                $output->writeln($this->warningOutput("\t>> MIGRATION: No tables available"));

                continue;
            }

            if ($tables instanceof stdClass && isError($tables)) {
                /** @phpstan-ignore-next-line */
                $output->writeln($this->errorOutput("\t>> MIGRATION: {$tables->message}"));

                continue;
            }

            if (is_array($tables)) {
                foreach ($tables as $table) {
                    /** @var string $tableName */
                    $tableName = $table->{"Tables_in_{$connection['dbname']}"};

                    $truncate = $this->migrations->truncateTable($connection['type'], $connectionName, $tableName);

                    if (null != $truncate) {
                        /** @var stdClass $response */
                        $response = $truncate->execute();

                        /** @var string $message */
                        $message = $response->message;

                        $output->writeln(
                            $this->warningOutput(
                                "\t>> MIGRATION: {$connection['dbname']}.{$tableName} [{$connection['type']}]"
                            )
                        );

                        if (isError($response)) {
                            $output->writeln($this->errorOutput("\t>> MIGRATION: {$message}"));
                        } else {
                            $output->writeln($this->successOutput("\t>> MIGRATION: {$message}"));
                        }
                    }
                }
            }
        }

        $output->writeln($this->infoOutput("\n\t>> All tables have been truncated"));

        return parent::SUCCESS;
    }
}
