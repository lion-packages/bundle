<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

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
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use LogicException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop all tables and re-run all migrations
 *
 * @property OutputInterface $output [OutputInterface is the interface
 * implemented by all Output classes]
 * @property Container $container [Container class object]
 * @property Store $store [Store class object]
 *
 * @package Lion\Bundle\Commands\Lion\Migrations
 */
class FreshMigrationsCommand extends MenuCommand
{
    /**
     * [OutputInterface is the interface implemented by all Output classes]
     *
     * @var OutputInterface $output
     */
    private OutputInterface $output;

    /**
     * [Container class object]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * @required
     * */
    public function setContainer(Container $container): FreshMigrationsCommand
    {
        $this->container = $container;

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
        $this->output = $output;
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
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('./database/Migrations/'))) {
            $output->writeln($this->errorOutput("\t>> MIGRATION: there are no defined migration routes"));

            return Command::FAILURE;
        }

        $this->dropTables();

        /** @var array<string, array<string, MigrationUpInterface>> $migrations */
        $migrations = $this->getMigrations();

        if (empty($migrations)) {
            $output->writeln($this->warningOutput("\t>> MIGRATION: no migrations available"));

            return Command::INVALID;
        }

        $this->executeMigrations($this->orderList($migrations[TableInterface::class]));

        $this->executeMigrations($migrations[ViewInterface::class]);

        $this->executeMigrations($migrations[StoreProcedureInterface::class]);

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
     * Gets defined migrations categorized by type
     *
     * @return array<string, array<string, MigrationUpInterface>>
     */
    private function getMigrations(): array
    {
        /** @var array<string, array<string, MigrationUpInterface>> $allMigrations */
        $allMigrations = [
            TableInterface::class => [],
            ViewInterface::class => [],
            StoreProcedureInterface::class => [],
        ];

        foreach ($this->container->getFiles('./database/Migrations/') as $migration) {
            if (isSuccess($this->store->validate([$migration], ['php']))) {
                $namespace = $this->container->getNamespace($migration, 'Database\\Migrations\\', 'Migrations/');

                $tableMigration = include_once($migration);

                if ($tableMigration instanceof TableInterface) {
                    $allMigrations[TableInterface::class][$namespace] = $tableMigration;
                }

                if ($tableMigration instanceof ViewInterface) {
                    $allMigrations[ViewInterface::class][$namespace] = $tableMigration;
                }

                if ($tableMigration instanceof StoreProcedureInterface) {
                    $allMigrations[StoreProcedureInterface::class][$namespace] = $tableMigration;
                }
            }
        }

        return $allMigrations;
    }

    /**
     * Clears all tables of all available connections
     *
     * @return void
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

    /**
     * Sorts the list of elements by the value defined in the INDEX constant
     *
     * @param array<string, MigrationUpInterface> $files [Class List]
     *
     * @return array<string, MigrationUpInterface>
     */
    private function orderList(array $files): array
    {
        uasort($files, function ($classA, $classB) {
            $namespaceA = $classA::class;

            $namespaceB = $classB::class;

            if (!defined($namespaceA . "::INDEX")) {
                return -1;
            }

            if (!defined($namespaceB . "::INDEX")) {
                return -1;
            }

            return $classA::INDEX <=> $classB::INDEX;
        });

        return $files;
    }

    /**
     * Run the migrations
     *
     * @param array<int, MigrationUpInterface> $files [List of migration files]
     *
     * @return void
     */
    private function executeMigrations(array $files): void
    {
        foreach ($files as $namespace => $classObject) {
            if ($classObject instanceof MigrationUpInterface) {
                $response = $classObject->up();

                $this->output->writeln($this->warningOutput("\t>> MIGRATION: {$namespace}"));

                if (isError($response)) {
                    $this->output->writeln($this->errorOutput("\t>> MIGRATION: {$response->message}"));
                } else {
                    $this->output->writeln($this->successOutput("\t>> MIGRATION: {$response->message}"));
                }
            }
        }
    }
}
