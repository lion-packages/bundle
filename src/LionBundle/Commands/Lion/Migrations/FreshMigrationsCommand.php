<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Command\Command;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use LogicException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop all tables and re-run all migrations
 *
 * @property Migrations $migrations [Manages the processes of creating or
 * executing migrations]
 *
 * @package Lion\Bundle\Commands\Lion\Migrations
 */
class FreshMigrationsCommand extends MenuCommand
{
    /**
     * [Manages the processes of creating or executing migrations]
     *
     * @var Migrations $migrations
     */
    private Migrations $migrations;

    #[Inject]
    public function setMigrations(Migrations $migrations): FreshMigrationsCommand
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
            ->setName('migrate:fresh')
            ->setDescription('Drop all tables and re-run all migrations')
            ->addOption('seed', 's', InputOption::VALUE_OPTIONAL, 'Do you want to run the seeds?', 'none');
    }

    /**
     * Initializes the command after the input has been bound and before the
     * input is validated
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and
     * options
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
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
     * @throws ExceptionInterface
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('./database/Migrations/'))) {
            $output->writeln($this->errorOutput("\t>> MIGRATION: there are no defined migration routes"));

            return Command::FAILURE;
        }

        $this->dropTables();

        /** @var array<string, array<string, MigrationUpInterface>> $migrations */
        $migrations = $this->migrations->getMigrations();

        if (empty($migrations)) {
            $output->writeln($this->warningOutput("\t>> MIGRATION: no migrations available"));

            return Command::INVALID;
        }

        $this->migrations->executeMigrations(
            $this,
            $output,
            $this->migrations->orderList($migrations[TableInterface::class])
        );

        $this->migrations->executeMigrations($this, $output, $migrations[ViewInterface::class]);

        $this->migrations->executeMigrations($this, $output, $migrations[StoreProcedureInterface::class]);

        $output->writeln($this->infoOutput("\n\t>> Migrations executed successfully"));

        $seed = $input->getOption('seed');

        if ($seed != 'none') {
            $output->writeln('');

            $this
                ->getApplication()
                ->find('db:seed')
                ->run(new ArrayInput([]), $output);
        }

        return Command::SUCCESS;
    }

    /**
     * Clears all tables of all available connections
     *
     * @return void
     *
     * @internal
     */
    private function dropTables(): void
    {
        $connections = Connection::getConnections();

        foreach ($connections as $connectionName => $connection) {
            $response = [];

            if (Driver::MYSQL === $connection['type']) {
                $response = Schema::connection($connectionName)
                    ->dropTables()
                    ->execute();
            }

            if (Driver::POSTGRESQL === $connection['type']) {
                $tables = $this->getTables($connectionName);

                if (!isset($tables->status)) {
                    $tablesArr = $this->arr->of($tables)->tree('tablename')->keys()->join(', ');

                    $response = PostgreSQL::connection($connectionName)
                        ->query(
                            $this->str->of('DROP TABLE IF EXISTS ')->concat($tablesArr)->concat('CASCADE;')->get()
                        )
                        ->execute();
                }
            }

            if (isError($response)) {
                $this->output->writeln(
                    $this->warningOutput("\t>> DATABASE: {$connection['dbname']} [{$connection['type']}]")
                );

                $this->output->writeln(
                    $this->errorOutput("\t>> DATABASE: {$response->message} [{$connection['type']}]")
                );
            }
        }
    }
}
