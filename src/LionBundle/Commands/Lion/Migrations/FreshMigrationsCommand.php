<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Command\Command;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop all tables and re-run all migrations
 *
 * @property Container $container [Container class object]
 */
class FreshMigrationsCommand extends Command
{
    /**
     * [Container class object]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * @required
     * */
    public function setContainer(Container $container): FreshMigrationsCommand
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): FreshMigrationsCommand
    {
        $this->store = $store;

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
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('./database/Migrations/'))) {
            $output->writeln($this->errorOutput("\t>> MIGRATION: there are no defined migration routes"));

            return Command::FAILURE;
        }

        $this->dropTables($output);

        /** @var array<string, array<string, MigrationUpInterface>> $migrations */
        $migrations = $this->getMigrations();

        if (empty($migrations)) {
            $output->writeln($this->warningOutput("\t>> MIGRATION: no migrations available"));

            return Command::INVALID;
        }

        $this->executeMigrations($output, $this->orderList($migrations[TableInterface::class]));

        $this->executeMigrations($output, $migrations[ViewInterface::class]);

        $this->executeMigrations($output, $migrations[StoreProcedureInterface::class]);

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
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return void
     */
    private function dropTables(OutputInterface $output): void
    {
        $connections = (object) Schema::getConnections();

        foreach ($connections->connections as $connection) {
            $response = Schema::dropTables()->execute();

            if (isError($response)) {
                $output->writeln($this->warningOutput("\t>> DATABASE: {$connection->dbname}"));

                $output->writeln($this->errorOutput("\t>> DATABASE: {$response->message}"));
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
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array<int, MigrationUpInterface> $files [description]
     *
     * @return [type] [description]
     */
    private function executeMigrations(Output $output, array $files): void
    {
        foreach ($files as $namespace => $classObject) {
            if ($classObject instanceof MigrationUpInterface) {
                /** @var MigrationUpInterface $classObject */
                $response = $classObject->up();

                if (isError($response)) {
                    $output->writeln($this->warningOutput("\t>> MIGRATION: {$namespace}"));

                    $output->writeln($this->errorOutput("\t>> MIGRATION: {$response->message}"));
                } else {
                    $output->writeln($this->warningOutput("\t>> MIGRATION: {$namespace}"));

                    $output->writeln($this->successOutput("\t>> MIGRATION: {$response->message}"));
                }
            }
        }
    }
}
