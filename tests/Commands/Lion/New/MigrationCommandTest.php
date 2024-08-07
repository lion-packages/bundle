<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class MigrationCommandTest extends Test
{
    use ConnectionProviderTrait;

    const MIGRATION_NAME = 'test-migration';
    const CLASS_NAME = 'TestMigration';
    const URL_PATH_TABLE = './database/Migrations/LionDatabase/MySQL/Tables/';
    const URL_PATH_VIEW = './database/Migrations/LionDatabase/MySQL/Views/';
    const URL_PATH_STORE_PROCEDURES = './database/Migrations/LionDatabase/MySQL/StoreProcedures/';
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'migration has been generated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $application = new Application();

        $application->add((new Container())->injectDependencies(new MigrationCommand()));

        $this->commandTester = new CommandTester($application->find('new:migration'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    #[Testing]
    public function executeIsInvalid(): void
    {
        $this->assertSame(Command::INVALID, $this->commandTester->execute(['migration' => 'users/create-users']));
    }

    #[Testing]
    public function executeForTable(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '0'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_TABLE . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_TABLE . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);
    }

    #[Testing]
    public function executeForView(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '1'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_VIEW . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_VIEW . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);
    }

    #[Testing]
    public function executeForStoreProcedure(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs(['0', '2'])
            ->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_STORE_PROCEDURES . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_STORE_PROCEDURES . self::FILE_NAME);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoreProcedureInterface::class,
        ]);
    }
}
