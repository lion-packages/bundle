<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\DB;

use DI\Attribute\Inject;
use InvalidArgumentException;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Command;
use Lion\Database\Connection;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Request\Http;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Execute the database connection seeds.
 */
class DBSeedCommand extends Command
{
    /**
     * Manipulate system files.
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * Modify and construct strings with different formats.
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * Manages basic database engine processes.
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    /**
     * Manages the processes of creating or executing migrations.
     *
     * @var Migrations $migrations
     */
    private Migrations $migrations;

    /**
     * Fabricates the data provided to manipulate information (folder, class,
     * namespace)
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    #[Inject]
    public function setStore(Store $store): DBSeedCommand
    {
        $this->store = $store;

        return $this;
    }

    #[Inject]
    public function setStr(Str $str): DBSeedCommand
    {
        $this->str = $str;

        return $this;
    }

    #[Inject]
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): DBSeedCommand
    {
        $this->databaseEngine = $databaseEngine;

        return $this;
    }

    #[Inject]
    public function setMigrations(Migrations $migrations): DBSeedCommand
    {
        $this->migrations = $migrations;

        return $this;
    }

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): DBSeedCommand
    {
        $this->classFactory = $classFactory;

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
            ->setName('db:seed')
            ->setDescription('Run the available seeds.')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The connection to run.');
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

        $seedsPath = Migrations::SEEDS_PATH . "{$dbNamePascal}/{$dbType}/";

        if (isError($this->store->exist($seedsPath))) {
            $output->writeln($this->errorOutput("\t>> SEEDS: There are no defined seeds."));

            return parent::FAILURE;
        }

        /** @var array<int, SeedInterface> $files */
        $files = [];

        foreach ($this->store->getFiles($seedsPath) as $seed) {
            if (isSuccess($this->store->validate([$seed], [ClassFactory::PHP_EXTENSION]))) {
                $className = $this->store->getName($seed);

                $this->classFactory->classFactory($seedsPath, $className);

                $classObjectName = $this->classFactory->getNamespace() . "\\{$className}";

                /** @var SeedInterface $seedInterface */
                $seedInterface = new $classObjectName();

                $files[$className] = $seedInterface;
            }
        }

        /** @phpstan-ignore-next-line */
        foreach ($this->migrations->orderList($files) as $seedInterface) {
            $output->writeln($this->warningOutput("\t>>  SEED: " . $seedInterface::class));

            if ($seedInterface instanceof SeedInterface) {
                $response = $seedInterface->run();

                if (is_int($response)) {
                    continue;
                }

                /** @var string $message */
                $message = $response->message;

                if (isError($response)) {
                    $output->writeln($this->errorOutput("\t>>  SEEDS: {$message}"));
                } else {
                    $output->writeln($this->successOutput("\t>>  SEEDS: {$message}"));
                }
            }
        }

        $output->writeln($this->infoOutput("\n\t>>  SEEDS: Seeds executed."));

        return parent::SUCCESS;
    }
}
