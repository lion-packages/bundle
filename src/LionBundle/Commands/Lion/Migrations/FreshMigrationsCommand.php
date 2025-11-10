<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Migrations;

use DI\Attribute\Inject;
use Exception;
use InvalidArgumentException;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Interface\Migrations\SchemaInterface;
use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Database\Connection;
use Lion\Request\Http;
use LogicException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop all tables and re-run all migrations.
 */
class FreshMigrationsCommand extends MenuCommand
{
    /**
     * Manages the processes of creating or executing migrations.
     *
     * @var Migrations $migrations
     */
    private Migrations $migrations;

    /**
     * Manages basic database engine processes.
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    #[Inject]
    public function setMigrations(Migrations $migrations): FreshMigrationsCommand
    {
        $this->migrations = $migrations;

        return $this;
    }

    #[Inject]
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): FreshMigrationsCommand
    {
        $this->databaseEngine = $databaseEngine;

        return $this;
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('migrate:fresh')
            ->setDescription('Drop all tables and re-run all migrations')
            ->addOption('seed', 's', InputOption::VALUE_OPTIONAL, 'Do you want to run the seeds?', 'none')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The connection to run.');
    }

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command where
     * some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes.
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes.
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method.
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes.
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes.
     *
     * @return int
     *
     * @throws Exception If an error occurs while deleting the file.
     * @throws ExceptionInterface When input binding fails. Bypass this by calling
     * ignoreValidationErrors().
     * @throws LogicException When this abstract method is not implemented.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $connectionName */
        $connectionName = $input->getOption('connection');

        if (!$connectionName) {
            throw new InvalidArgumentException("The '--connection' option is required.", Http::INTERNAL_SERVER_ERROR);
        }

        $connections = Connection::getConnections();

        $connection = $connections[$connectionName];

        /** @var string $dbNamePascal */
        $dbNamePascal = $this->str
            ->of($connection[Connection::CONNECTION_DBNAME])
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->pascal()
            ->get();

        $dbType = $this->databaseEngine->getDriver($connection[Connection::CONNECTION_TYPE]);

        if (isError($this->store->exist(Migrations::MIGRATIONS_PATH . "{$dbNamePascal}/{$dbType}/"))) {
            $output->writeln($this->errorOutput("\t>> MIGRATION: There are no defined migrations."));

            return parent::FAILURE;
        }

        $this->migrations->resetDatabase(
            $connection[Connection::CONNECTION_DBNAME],
            $connectionName,
            $connection[Connection::CONNECTION_TYPE]
        );

        /** @var array<string, array<string, MigrationUpInterface>> $migrations */
        $migrations = $this->migrations->getMigrations("{$dbNamePascal}/{$dbType}/");

        /** @phpstan-ignore-next-line */
        $this->migrations->executeMigrations($this, $output, $migrations[SchemaInterface::class]);

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

        $output->writeln($this->infoOutput("\n\t>> MIGRATIONS: Migrations executed successfully."));

        $seed = $input->getOption('seed');

        if ($seed != 'none') {
            $output->writeln('');

            /** @phpstan-ignore-next-line */
            $this
                ->getApplication()
                ->find('db:seed')
                ->run(new ArrayInput([
                    '--connection' => $connectionName,
                ]), $output);
        }

        return parent::SUCCESS;
    }
}
