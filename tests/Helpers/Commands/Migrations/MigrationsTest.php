<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
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
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class MigrationsTest extends Test
{
    use ConnectionProviderTrait;

    private const string MIGRATION_NAME = 'test-migration';
    private const string CLASS_NAME = 'TestMigration';
    private const string CLASS_NAMESPACE_TABLE = 'Database\\Migrations\\LionDatabase\\MySQL\\Tables\\';
    private const string CLASS_NAMESPACE_VIEW = 'Database\\Migrations\\LionDatabase\\MySQL\\Views\\';
    private const string CLASS_NAMESPACE_STORE_PROCEDURE = 'Database\\Migrations\\LionDatabase\\MySQL\\StoreProcedures\\';
    private const string URL_PATH_MYSQL_TABLE = './database/Migrations/LionDatabase/MySQL/Tables/';
    private const string URL_PATH_MYSQL_VIEW = './database/Migrations/LionDatabase/MySQL/Views/';
    private const string URL_PATH_MYSQL_STORE_PROCEDURE = './database/Migrations/LionDatabase/MySQL/StoreProcedures/';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'migration has been generated';

    private CommandTester $commandTester;
    private Migrations $migrations;
    private Store $store;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->store = new Store();

        $this->migrations = (new Migrations())
            ->setStore($this->store);

        $migrationCommand = (new MigrationCommand())
            ->setArr(new Arr())
            ->setStore($this->store)
            ->setStr(new Str())
            ->setClassFactory(
                (new ClassFactory())
                    ->setStore($this->store)
            )
            ->setMigrationFactory(new MigrationFactory())
            ->setDatabaseEngine(new DatabaseEngine());

        $application = new Application();

        $application->add($migrationCommand);

        $this->commandTester = new CommandTester($application->find('new:migration'));

        $this->initReflection($this->migrations);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('database/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(Migrations::class, $this->migrations->setStore($this->store));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function orderList(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '0'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        $objClass = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $namespace = $this->store->getNamespaceFromFile(
            (self::URL_PATH_MYSQL_TABLE . self::FILE_NAME),
            'Database\\Migrations\\',
            'Migrations/'
        );

        $migrations = [
            $namespace => $objClass
        ];

        $list = $this->migrations->orderList($migrations);

        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
        $this->assertArrayHasKey($namespace, $list);
        $this->assertSame($migrations, $list);
        $this->assertIsObject($list[$namespace]);
        $this->assertInstanceOf(TableInterface::class, $list[$namespace]);
    }

    #[Testing]
    public function getMigrations(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '0'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        $objClass = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $list = $this->migrations->getMigrations();

        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
        $this->assertArrayHasKey(TableInterface::class, $list);
        $this->assertArrayHasKey(ViewInterface::class, $list);
        $this->assertArrayHasKey(StoreProcedureInterface::class, $list);
        $this->assertNotEmpty($list[TableInterface::class]);
        $this->assertisobject($list[TableInterface::class][self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME]);

        $this->assertInstanceOf(
            TableInterface::class,
            $list[TableInterface::class][self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME]
        );
    }

    #[Testing]
    public function executeMigrationsGroup(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '0'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        $objClass = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs(['0', '1'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_VIEW . self::FILE_NAME);

        $objClass = new (self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs(['0', '2'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_STORE_PROCEDURE . self::FILE_NAME);

        $objClass = new (self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoreProcedureInterface::class,
        ]);

        $this->migrations->executeMigrationsGroup([
            self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME,
            self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME,
            self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME,
        ]);
    }
}
