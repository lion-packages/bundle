<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use InvalidArgumentException;
use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Interface\Migrations\SchemaInterface;
use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Dependency\Injection\Container;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class MigrationCommandTest extends Test
{
    private const string MIGRATION_NAME = 'test-migration';
    private const string CLASS_NAME = 'TestMigration';
    private const string NAMESPACE_MYSQL_SCHEMA = 'Database\\Migrations\\LionDatabase\\MySQL\\Schemas\\';
    private const string NAMESPACE_MYSQL_TABLE = 'Database\\Migrations\\LionDatabase\\MySQL\\Tables\\';
    private const string NAMESPACE_MYSQL_VIEW = 'Database\\Migrations\\LionDatabase\\MySQL\\Views\\';
    private const string NAMESPACE_MYSQL_STORE_PROCEDURES =
        'Database\\Migrations\\LionDatabase\\MySQL\\StoredProcedures\\';
    private const string NAMESPACE_POSTGRESQL_TABLE = 'Database\\Migrations\\LionDatabase\\PostgreSQL\\Tables\\';
    private const string NAMESPACE_POSTGRESQL_VIEW = 'Database\\Migrations\\LionDatabase\\PostgreSQL\\Views\\';
    private const string NAMESPACE_POSTGRESQL_STORE_PROCEDURES =
        'Database\\Migrations\\LionDatabase\\PostgreSQL\\StoredProcedures\\';
    private const string URL_PATH_MYSQL_SCHEMA = './database/Migrations/LionDatabase/MySQL/Schemas/';
    private const string URL_PATH_MYSQL_TABLE = './database/Migrations/LionDatabase/MySQL/Tables/';
    private const string URL_PATH_MYSQL_VIEW = './database/Migrations/LionDatabase/MySQL/Views/';
    private const string URL_PATH_MYSQL_STORE_PROCEDURES = './database/Migrations/LionDatabase/MySQL/StoredProcedures/';
    private const string URL_PATH_POSTGRESQL_TABLE = './database/Migrations/LionDatabase/PostgreSQL/Tables/';
    private const string URL_PATH_POSTGRESQL_VIEW = './database/Migrations/LionDatabase/PostgreSQL/Views/';
    private const string URL_PATH_POSTGRESQL_STORE_PROCEDURES =
        './database/Migrations/LionDatabase/PostgreSQL/StoredProcedures/';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The migration was generated successfully.';

    private CommandTester $commandTester;
    private MigrationCommand $migrationCommand;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        /** @var MigrationCommand $migrationCommand */
        $migrationCommand = new Container()->resolve(MigrationCommand::class);

        $this->migrationCommand = $migrationCommand;

        $application = new Application();

        $application->add($this->migrationCommand);

        $this->commandTester = new CommandTester($application->find('new:migration'));

        $this->initReflection($this->migrationCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(MigrationCommand::class, $this->migrationCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setMigrationFactory(): void
    {
        $this->assertInstanceOf(
            MigrationCommand::class,
            $this->migrationCommand->setMigrationFactory(new MigrationFactory())
        );

        $this->assertInstanceOf(MigrationFactory::class, $this->getPrivateProperty('migrationFactory'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(
            MigrationCommand::class,
            $this->migrationCommand->setDatabaseEngine(new DatabaseEngine())
        );

        $this->assertInstanceOf(DatabaseEngine::class, $this->getPrivateProperty('databaseEngine'));
    }

    #[Testing]
    public function executeIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Migration cannot be inside subfolders.');
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->commandTester->execute([
            'migration' => 'users/create-users',
        ]);
    }

    #[Testing]
    public function executeWithoutConnection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The '--connection' option is required.");
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->commandTester->execute([
            'migration' => self::MIGRATION_NAME,
        ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForMySQLSchema(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::SCHEMA,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'local',
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_SCHEMA . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::NAMESPACE_MYSQL_SCHEMA . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            SchemaInterface::class,
        ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForPostgreSQLSchema(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('Currently, the driver does not support this type of migration.');

        $this->commandTester
            ->setInputs([
                MigrationFactory::SCHEMA,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'lion_database_postgres',
            ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForSQLiteSchema(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('Currently, the driver does not support this type of migration.');

        $this->commandTester
            ->setInputs([
                MigrationFactory::SCHEMA,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'lion_database_sqlite',
            ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForMySQLTable(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::TABLE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'local',
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::NAMESPACE_MYSQL_TABLE . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForMySQLView(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::VIEW,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'local',
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_VIEW . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::NAMESPACE_MYSQL_VIEW . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForMySQLStoreProcedure(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::STORED_PROCEDURE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'local',
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_STORE_PROCEDURES . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::NAMESPACE_MYSQL_STORE_PROCEDURES . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoredProcedureInterface::class,
        ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForPostgreSQLTable(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::TABLE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'lion_database_postgres',
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_POSTGRESQL_TABLE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::NAMESPACE_POSTGRESQL_TABLE . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForPostgreSQLView(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::VIEW,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'lion_database_postgres',
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_POSTGRESQL_VIEW . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::NAMESPACE_POSTGRESQL_VIEW . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);
    }

    #[Testing]
    #[RunInSeparateProcess]
    public function executeForPostgreSQLStoreProcedure(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::STORED_PROCEDURE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => 'lion_database_postgres',
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_POSTGRESQL_STORE_PROCEDURES . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::NAMESPACE_POSTGRESQL_STORE_PROCEDURES . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoredProcedureInterface::class,
        ]);
    }
}
