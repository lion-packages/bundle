<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use Lion\Bundle\Commands\Lion\Migrations\NewMigrationCommand;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class NewMigrationCommandTest extends Test
{
    use ConnectionProviderTrait;

    const MIGRATION_NAME = 'test-migration';
    const CLASS_NAME = 'TestMigration';
    const URL_PATH_TABLE = './database/Migrations/LionDatabase/Tables/';
    const URL_PATH_VIEW = './database/Migrations/LionDatabase/Views/';
    const URL_PATH_STORE_PROCEDURES = './database/Migrations/LionDatabase/StoreProcedures/';
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'migration has been generated';

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NewMigrationCommand()));
        $this->commandTester = new CommandTester($application->find('migrate:new'));
	}

	protected function tearDown(): void
	{
        // $this->rmdirRecursively('./database/');
	}

    public function testExecuteIsInvalid(): void
    {
        $this->assertSame(Command::INVALID, $this->commandTester->execute(['migration' => 'users/create-users']));
    }

    public function testExecuteForTable(): void
    {
        $commandExecute = $this->commandTester->setInputs(['0', '0'])->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_TABLE . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_TABLE . self::FILE_NAME);

        $this->assertInstanceOf(MigrationUpInterface::class, $objClass);
    }

    public function testExecuteForView(): void
    {
        $commandExecute = $this->commandTester->setInputs(['0', '1'])->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_VIEW . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_VIEW . self::FILE_NAME);

        $this->assertInstanceOf(MigrationUpInterface::class, $objClass);
    }

    public function testExecuteForStoreProcedure(): void
    {
        $commandExecute = $this->commandTester->setInputs(['0', '2'])->execute(['migration' => self::MIGRATION_NAME]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_STORE_PROCEDURES . self::FILE_NAME);

        $objClass = include_once(self::URL_PATH_STORE_PROCEDURES . self::FILE_NAME);

        $this->assertInstanceOf(MigrationUpInterface::class, $objClass);
    }
}
