<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Connection;
use Lion\Database\Driver;
use LogicException;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a migration for database structure control
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class MigrationCommand extends MenuCommand
{
    /**
     * [Constant for a table]
     *
     * @const TABLE
     */
    private const string TABLE = 'Table';

    /**
     * [Constant for a view]
     *
     * @const VIEW
     */
    private const string VIEW = 'View';

    /**
     * [Constant for a store-procedure]
     *
     * @const STORE_PROCEDURE
     */
    private const string STORE_PROCEDURE = 'Store-Procedure';

    /**
     * [List of available types]
     *
     * @const OPTIONS
     */
    private const array MIGRATIONS_OPTIONS = [
        self::TABLE,
        self::VIEW,
        self::STORE_PROCEDURE,
    ];

    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Factory of the content of the generated migrations]
     *
     * @var MigrationFactory $migrationFactory
     */
    private MigrationFactory $migrationFactory;

    /**
     * [Manages basic database engine processes]
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): MigrationCommand
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setMigrationFactory(MigrationFactory $migrationFactory): MigrationCommand
    {
        $this->migrationFactory = $migrationFactory;

        return $this;
    }

    #[Inject]
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): MigrationCommand
    {
        $this->databaseEngine = $databaseEngine;

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
            ->setName('new:migration')
            ->setDescription('Command required to generate a new migration')
            ->addArgument('migration', InputArgument::REQUIRED, 'Migration name');
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
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $migration */
        $migration = $input->getArgument('migration');

        if (STR->of($migration)->test("/.*\//")) {
            $output->writeln($this->errorOutput("\t>>  migration cannot be inside subfolders"));

            return parent::INVALID;
        }

        $selectedConnection = $this->selectConnection($input, $output);

        $connectionName = Connection::getConnections()[$selectedConnection]['dbname'];

        /** @var string $databaseEngineType */
        $databaseEngineType = $this->databaseEngine->getDatabaseEngineType($selectedConnection);

        $driver = $this->databaseEngine->getDriver($databaseEngineType);

        $selectedType = $this->selectMigrationType($input, $output, self::MIGRATIONS_OPTIONS);

        /** @var string $migrationPascal */
        $migrationPascal = $this->str
            ->of($migration)
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->pascal()
            ->trim()
            ->get();

        /** @var string $dbPascal */
        $dbPascal = $this->str
            ->of($connectionName)
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->pascal()
            ->trim()
            ->get();

        $dataMigration = $this->getBody($migrationPascal, $selectedType, $dbPascal, $driver);

        /** @var string $path */
        $path = $dataMigration->path;

        /** @var string $body */
        $body = $dataMigration->body;

        /** @var string $add */
        $add = $this->str
            ->of($body)
            ->replace('--NAME--', $migration)
            ->get();

        $this->store->folder($path);

        $this->classFactory
            ->classFactory($path, $migrationPascal)
            ->create($this->classFactory->getClass(), ClassFactory::PHP_EXTENSION, $path)
            ->add($add)
            ->close();

        $output->writeln($this->warningOutput("\t>>  MIGRATION: {$this->classFactory->getClass()}"));

        $output->writeln(
            $this->successOutput(
                "\t>>  MIGRATION: the '{$this->classFactory->getNamespace()}\\{$this->classFactory->getClass()}' " .
                'migration has been generated'
            )
        );

        return parent::SUCCESS;
    }

    /**
     * Gets the data to generate the body of the selected migration type
     *
     * @param string $className [Class name]
     * @param string $selectedType [Type of migration]
     * @param string $dbPascal [Database in PascalCase format]
     * @param string $driver [Database Engine Type]
     *
     * @return stdClass
     */
    private function getBody(string $className, string $selectedType, string $dbPascal, string $driver): stdClass
    {
        $body = '';

        $path = '';

        if (self::TABLE === $selectedType) {
            $path = "database/Migrations/{$dbPascal}/{$driver}/Tables/";

            if ($this->databaseEngine->getDriver(Driver::MYSQL) === $driver) {
                $body = $this->migrationFactory->getMySQLTableBody(
                    $className,
                    "Database\\Migrations\\{$dbPascal}\\{$driver}\\Tables"
                );
            } elseif ($this->databaseEngine->getDriver(Driver::POSTGRESQL) === $driver) {
                $body = $this->migrationFactory->getPostgreSQLTableBody(
                    $className,
                    "Database\\Migrations\\{$dbPascal}\\{$driver}\\Tables"
                );
            }
        }

        if (self::VIEW === $selectedType) {
            $path = "database/Migrations/{$dbPascal}/{$driver}/Views/";

            if ($this->databaseEngine->getDriver(Driver::MYSQL) === $driver) {
                $body = $this->migrationFactory->getMySQLViewBody(
                    $className,
                    "Database\\Migrations\\{$dbPascal}\\{$driver}\\Views"
                );
            } elseif ($this->databaseEngine->getDriver(Driver::POSTGRESQL) === $driver) {
                $body = $this->migrationFactory->getPostgreSQLViewBody(
                    $className,
                    "Database\\Migrations\\{$dbPascal}\\{$driver}\\Views"
                );
            }
        }

        if (self::STORE_PROCEDURE === $selectedType) {
            $path = "database/Migrations/{$dbPascal}/{$driver}/StoreProcedures/";

            if ($this->databaseEngine->getDriver(Driver::MYSQL) === $driver) {
                $body = $this->migrationFactory->getMySQLStoreProcedureBody(
                    $className,
                    "Database\\Migrations\\{$dbPascal}\\{$driver}\\StoreProcedures"
                );
            } elseif ($this->databaseEngine->getDriver(Driver::POSTGRESQL) === $driver) {
                $body = $this->migrationFactory->getPostgreSQLStoreProcedureBody(
                    $className,
                    "Database\\Migrations\\{$dbPascal}\\{$driver}\\StoreProcedures"
                );
            }
        }

        return (object) [
            'body' => $body,
            'path' => $path,
        ];
    }
}
