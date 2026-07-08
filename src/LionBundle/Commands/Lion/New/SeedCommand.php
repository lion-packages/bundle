<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Connection;
use LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a seed.
 */
class SeedCommand extends MenuCommand
{
    /**
     * Manages basic database engine processes.
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    /**
     * Fabricates the data provided to manipulate information (folder, class,
     * namespace).
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    #[Inject]
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): SeedCommand
    {
        $this->databaseEngine = $databaseEngine;

        return $this;
    }

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): SeedCommand
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
            ->setName('new:seed')
            ->setDescription('Command required for creating new seeds.')
            ->addArgument('seed', InputArgument::OPTIONAL, 'Seed name.', 'ExampleSeed');
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
     * @throws Exception If the file could not be opened.
     * @throws LogicException When this abstract method is not implemented.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connectionName = $this->selectConnection();

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

        /** @var string $seed */
        $seed = $input->getArgument('seed');

        $this->classFactory->classFactory($seedsPath, $seed);

        $folder = $this->classFactory->getFolder();

        $class = $this->classFactory->getClass();

        $namespace = $this->classFactory->getNamespace();

        $this->store->folder($folder);

        $this->classFactory
            ->create($class, ClassFactory::PHP_EXTENSION, $folder)
            ->add(
                <<<PHP
                <?php

                declare(strict_types=1);

                namespace {$namespace};

                use Lion\Bundle\Interface\SeedInterface;
                use stdClass;

                /**
                 * Insert data into the '' entity.
                 */
                final class {$class} implements SeedInterface
                {
                    /**
                     * Index number for seed execution priority.
                     *
                     * @const INDEX
                     */
                    public const ?int INDEX = null;

                    /**
                     * {@inheritDoc}
                     */
                    public function run(): int|stdClass
                    {
                        return success('OK');
                    }
                }

                PHP
            )
            ->close();

        $output->writeln($this->warningOutput("\t>>  SEEDS: {$namespace}\\{$class}"));

        $output->writeln($this->successOutput("\t>>  SEEDS: The seed was generated correctly."));

        return parent::SUCCESS;
    }
}
