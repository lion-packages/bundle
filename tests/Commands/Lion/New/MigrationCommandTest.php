<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class MigrationCommandTest extends Test
{
    use ConnectionProviderTrait;

    private const string MIGRATION_NAME = 'test-migration';
    private const string CLASS_NAME = 'TestMigration';
    private const string URL_PATH_MYSQL_TABLE = './database/Migrations/LionDatabase/MySQL/Tables/';
    private const string URL_PATH_MYSQL_VIEW = './database/Migrations/LionDatabase/MySQL/Views/';
    private const string URL_PATH_MYSQL_STORE_PROCEDURES = './database/Migrations/LionDatabase/MySQL/StoreProcedures/';
    private const string URL_PATH_POSTGRESQL_TABLE = './database/Migrations/LionDatabase/PostgreSQL/Tables/';
    private const string URL_PATH_POSTGRESQL_VIEW = './database/Migrations/LionDatabase/PostgreSQL/Views/';
    private const string URL_PATH_POSTGRESQL_STORE_PROCEDURES = './database/Migrations/LionDatabase/PostgreSQL/StoreProcedures/';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'migration has been generated';

    private CommandTester $commandTester;
    private MigrationCommand $migrationCommand;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->migrationCommand = (new MigrationCommand())
            ->setArr(new Arr())
            ->setStore(new Store())
            ->setStr(new Str())
            ->setClassFactory(
                (new ClassFactory())
                    ->setStore(new Store())
            )
            ->setMigrationFactory(new MigrationFactory())
            ->setDatabaseEngine(new DatabaseEngine());

        $application = new Application();

        $application->add($this->migrationCommand);

        $this->commandTester = new CommandTester($application->find('new:migration'));

        $this->initReflection($this->migrationCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(MigrationCommand::class, $this->migrationCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    #[Testing]
    public function setMigrationFactory(): void
    {
        $this->assertInstanceOf(
            MigrationCommand::class,
            $this->migrationCommand->setMigrationFactory(new MigrationFactory())
        );

        $this->assertInstanceOf(MigrationFactory::class, $this->getPrivateProperty('migrationFactory'));
    }

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
        $this->assertSame(Command::INVALID, $this->commandTester->execute(['migration' => 'users/create-users']));
    }

    #[Testing]
    public function executeForMySQLTable(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '0'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);
    }

    #[Testing]
    public function executeForMySQLView(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '1'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_VIEW . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_MYSQL_VIEW . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);
    }

    #[Testing]
    public function executeForMySQLStoreProcedure(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '2'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_STORE_PROCEDURES . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_MYSQL_STORE_PROCEDURES . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoreProcedureInterface::class,
        ]);
    }

    #[Testing]
    public function executeForPostgreSQLTable(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['2', '0'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_POSTGRESQL_TABLE . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_POSTGRESQL_TABLE . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);
    }

    #[Testing]
    public function executeForPostgreSQLView(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['2', '1'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_POSTGRESQL_VIEW . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_POSTGRESQL_VIEW . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);
    }

    #[Testing]
    public function executeForPostgreSQLStoreProcedure(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['2', '2'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_POSTGRESQL_STORE_PROCEDURES . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_POSTGRESQL_STORE_PROCEDURES . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoreProcedureInterface::class,
        ]);
    }
}
