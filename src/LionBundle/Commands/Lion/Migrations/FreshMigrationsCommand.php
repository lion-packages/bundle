<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Database\Connection;
use LogicException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop all tables and re-run all migrations
 *
 * @package Lion\Bundle\Commands\Lion\Migrations
 */
class FreshMigrationsCommand extends MenuCommand
{
    /**
     * Manages the processes of creating or executing migrations
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
     * Initializes the command after the input has been bound and before the input
     * is validated
     *
     * This is mainly useful when a lot of commands extends one main command where
     * some things need to be initialized based on the input arguments and options
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes
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
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes
     *
     * @return int
     *
     * @throws Exception If an error occurs while deleting the file
     * @throws ExceptionInterface When input binding fails. Bypass this by calling
     * ignoreValidationErrors()
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (isError($this->store->exist('database/Migrations/'))) {
            $output->writeln($this->errorOutput("\t>> MIGRATION: There are no defined migrations."));

            return parent::FAILURE;
        }

        $connections = Connection::getConnections();

        foreach ($connections as $connectionName => $connectionData) {
            $this->migrations
                ->processingWithStaticConnections(function () use ($connectionName, $connectionData): void {
                    $this->migrations->resetDatabase(
                        $connectionData['dbname'],
                        $connectionName,
                        $connectionData['type']
                    );
                });
        }

        /** @var array<string, array<string, MigrationUpInterface>> $migrations */
        $migrations = $this->migrations->getMigrations();

        $this->migrations->executeMigrations(
            $this,
            $output,
            /** @phpstan-ignore-next-line */
            $this->migrations->orderList($migrations[TableInterface::class])
        );

        /** @phpstan-ignore-next-line */
        $this->migrations->executeMigrations($this, $output, $migrations[ViewInterface::class]);

        /** @phpstan-ignore-next-line */
        $this->migrations->executeMigrations($this, $output, $migrations[StoredProcedureInterface::class]);

        $output->writeln($this->infoOutput("\n\t>> Migrations executed successfully."));

        $seed = $input->getOption('seed');

        if ($seed != 'none') {
            $output->writeln('');

            /** @phpstan-ignore-next-line */
            $this
                ->getApplication()
                ->find('db:seed')
                ->run(new ArrayInput([]), $output);
        }

        return parent::SUCCESS;
    }
}
