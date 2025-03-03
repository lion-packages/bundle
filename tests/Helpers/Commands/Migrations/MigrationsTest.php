<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class MigrationsTest extends Test
{
    private const string MIGRATION_NAME = 'test-migration';
    private const string CLASS_NAME = 'TestMigration';
    private const string CLASS_NAMESPACE_TABLE = 'Database\\Migrations\\LionDatabase\\MySQL\\Tables\\';
    private const string CLASS_NAMESPACE_VIEW = 'Database\\Migrations\\LionDatabase\\MySQL\\Views\\';
    private const string CLASS_NAMESPACE_STORE_PROCEDURE =
        'Database\\Migrations\\LionDatabase\\MySQL\\StoredProcedures\\';
    private const string URL_PATH_MYSQL_TABLE = './database/Migrations/LionDatabase/MySQL/Tables/';
    private const string URL_PATH_MYSQL_VIEW = './database/Migrations/LionDatabase/MySQL/Views/';
    private const string URL_PATH_MYSQL_STORED_PROCEDURE = './database/Migrations/LionDatabase/MySQL/StoredProcedures/';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'migration has been generated';

    private CommandTester $commandTester;
    private Migrations $migrations;
    private Store $store;

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws DependencyException
     */
    protected function setUp(): void
    {
        $this->store = new Store();

        $container = new Container();

        /** @var Migrations $migrations */
        $migrations = $container->resolve(Migrations::class);

        $this->migrations = $migrations;

        /** @var MigrationCommand $migrationCommand */
        $migrationCommand = $container->resolve(MigrationCommand::class);

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
            ->setInputs([
                '0',
                '0',
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $tableMigration = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertInstances($tableMigration, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $tableNamespace = $this->store->getNamespaceFromFile(
            (self::URL_PATH_MYSQL_TABLE . self::FILE_NAME),
            'Database\\Migrations\\',
            'Migrations/'
        );

        /** @var array<string, MigrationUpInterface> $migrations */
        $migrations = [
            $tableNamespace => $tableMigration,
        ];

        $list = $this->migrations->orderList($migrations);

        $this->assertNotEmpty($list);
        $this->assertArrayHasKey($tableNamespace, $list);
        $this->assertSame($migrations, $list);
        $this->assertInstanceOf(TableInterface::class, $list[$tableNamespace]);
    }

    #[Testing]
    public function getMigrations(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                '0',
                '0',
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $tableMigration = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertInstances($tableMigration, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs([
                '0',
                '1',
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_VIEW . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $viewMigration = new (self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME)();

        $this->assertInstances($viewMigration, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs([
                '0',
                '2',
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_STORED_PROCEDURE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $viewMigration = new (self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME)();

        $this->assertInstances($viewMigration, [
            MigrationUpInterface::class,
            StoredProcedureInterface::class,
        ]);

        $list = $this->migrations->getMigrations();

        $this->assertNotEmpty($list);
        $this->assertArrayHasKey(TableInterface::class, $list);
        $this->assertArrayHasKey(ViewInterface::class, $list);
        $this->assertArrayHasKey(StoredProcedureInterface::class, $list);
        $this->assertNotEmpty($list[TableInterface::class]);
        $this->assertNotEmpty($list[ViewInterface::class]);
        $this->assertNotEmpty($list[StoredProcedureInterface::class]);

        $this->assertInstanceOf(
            TableInterface::class,
            $list[TableInterface::class][self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME]
        );

        $this->assertInstanceOf(
            ViewInterface::class,
            $list[ViewInterface::class][self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME]
        );

        $this->assertInstanceOf(
            StoredProcedureInterface::class,
            $list[StoredProcedureInterface::class][self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME]
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

        /** @phpstan-ignore-next-line */
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

        /** @phpstan-ignore-next-line */
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
        $this->assertFileExists(self::URL_PATH_MYSQL_STORED_PROCEDURE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoredProcedureInterface::class,
        ]);

        /** @phpstan-ignore-next-line */
        $this->migrations->executeMigrationsGroup([
            self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME,
            self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME,
            self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME,
        ]);
    }
}
