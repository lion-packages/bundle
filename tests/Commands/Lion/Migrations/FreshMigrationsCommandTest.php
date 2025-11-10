<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use DI\DependencyException;
use DI\NotFoundException;
use InvalidArgumentException;
use Lion\Bundle\Commands\Lion\DB\DBSeedCommand;
use Lion\Bundle\Commands\Lion\Migrations\FreshMigrationsCommand;
use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Kernel;
use Lion\Database\Connection;
use Lion\Database\Drivers\Schema\MySQL;
use Lion\Dependency\Injection\Container;
use Lion\Helpers\Str;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class FreshMigrationsCommandTest extends Test
{
    private const string OUTPUT_SEED_CREATE_MESSAGE = 'The seed was generated correctly.';
    private const string OUTPUT_MESSAGE = 'Migrations executed successfully.';
    private const string OUTPUT_MESSAGE_ERROR_PATH = 'There are no defined migrations.';
    private const string OUTPUT_MIGRATION_CREATE_MESSAGE = 'The migration was generated successfully.';
    private const string SEED_CLASS = 'ExampleSeed';
    private const string SEED_FILE = 'ExampleSeed.php';

    private CommandTester $commandTesterNew;
    private CommandTester $commandTesterSeed;
    private CommandTester $commandTesterFresh;
    private FreshMigrationsCommand $freshMigrationsCommand;
    private ClassFactory $classFactory;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        $kernel = new Kernel();

        $container = new Container();

        /** @var DBSeedCommand $dbSeedCommand */
        $dbSeedCommand = $container->resolve(DBSeedCommand::class);

        /** @var SeedCommand $seedCommand */
        $seedCommand = $container->resolve(SeedCommand::class);

        /** @var MigrationCommand $migrationCommand */
        $migrationCommand = $container->resolve(MigrationCommand::class);

        /** @var FreshMigrationsCommand $freshMigrationCommand */
        $freshMigrationCommand = $container->resolve(FreshMigrationsCommand::class);

        $this->freshMigrationsCommand = $freshMigrationCommand;

        /** @var ClassFactory $classFactory */
        $classFactory = $container->resolve(ClassFactory::class);

        $this->classFactory = $classFactory;

        $kernel->commandsOnObjects([
            $dbSeedCommand,
            $seedCommand,
            $migrationCommand,
            $this->freshMigrationsCommand,
        ]);

        $application = $kernel->getApplication();

        $this->commandTesterNew = new CommandTester($application->find('new:migration'));

        $this->commandTesterSeed = new CommandTester($application->find('new:seed'));

        $this->commandTesterFresh = new CommandTester($application->find('migrate:fresh'));

        $this->initReflection($this->freshMigrationsCommand);
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setMigrations(): void
    {
        $this->assertInstanceOf(
            FreshMigrationsCommand::class,
            $this->freshMigrationsCommand->setMigrations(new Migrations())
        );

        $this->assertInstanceOf(Migrations::class, $this->getPrivateProperty('migrations'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(
            FreshMigrationsCommand::class,
            $this->freshMigrationsCommand->setDatabaseEngine(new DatabaseEngine())
        );

        $this->assertInstanceOf(DatabaseEngine::class, $this->getPrivateProperty('databaseEngine'));
    }

    #[Testing]
    public function executeWithoutConnection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The '--connection' option is required.");
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->commandTesterFresh->execute([]);
    }

    #[Testing]
    public function executePathDoesNotExist(): void
    {
        $this->assertSame(Command::FAILURE, $this->commandTesterFresh->execute([
            '--connection' => getDefaultConnection(),
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE_ERROR_PATH, $this->commandTesterFresh->getDisplay());
    }

    #[Testing]
    public function execute(): void
    {
        $this->createDirectory(Migrations::MIGRATIONS_PATH);

        $connectionName = 'local';

        $this->assertSame(Command::SUCCESS, $this->commandTesterNew->execute([
            'migration' => 'test',
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MIGRATION_CREATE_MESSAGE, $this->commandTesterNew->getDisplay());

        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute([
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTesterFresh->getDisplay());

        $this->rmdirRecursively('./database/');

        /** @var string $connectionName */
        $connectionName = env('DB_DEFAULT');

        MySQL::connection($connectionName)
            ->dropTable('test')
            ->execute();
    }

    #[Testing]
    public function executeWithSeed(): void
    {
        $this->createDirectory(Migrations::MIGRATIONS_PATH);

        $connectionName = getDefaultConnection();

        $connections = Connection::getConnections();

        $connection = $connections[$connectionName];

        /** @var string $dbNamePascal */
        $dbNamePascal = new Str()
            ->of($connection[Connection::CONNECTION_DBNAME])
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->pascal()
            ->get();

        $dbType = new DatabaseEngine()->getDriver($connection[Connection::CONNECTION_TYPE]);

        $seedsPath = Migrations::SEEDS_PATH . "{$dbNamePascal}/{$dbType}/";

        $this->classFactory->classFactory($seedsPath, self::SEED_CLASS);

        $this->assertSame(Command::SUCCESS, $this->commandTesterSeed->execute([
            'seed' => self::SEED_CLASS,
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::OUTPUT_SEED_CREATE_MESSAGE, $this->commandTesterSeed->getDisplay());
        $this->assertFileExists($seedsPath . self::SEED_FILE);

        $objClass = new ($this->classFactory->getNamespace() . "\\" . self::SEED_CLASS)();

        $this->assertInstanceOf(SeedInterface::class, $objClass);

        $this->assertSame(Command::SUCCESS, $this->commandTesterNew->execute([
            'migration' => 'test',
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MIGRATION_CREATE_MESSAGE, $this->commandTesterNew->getDisplay());

        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute([
            '--seed' => null,
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTesterFresh->getDisplay());

        $this->rmdirRecursively('./database/');
    }
}
