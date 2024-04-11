<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use Lion\Bundle\Commands\Lion\DB\DBSeedCommand;
use Lion\Bundle\Commands\Lion\Migrations\FreshMigrationsCommand;
use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class FreshMigrationsCommandTest extends Test
{
    const OUTPUT_SEED_CREATE_MESSAGE = 'seed has been generated';
    const OUTPUT_MESSAGE = 'Migrations executed successfully';
    const OUTPUT_SEED_MESSAGE = 'seeds executed';
    const OUTPUT_MIGRATION_CREATE_MESSAGE = 'migration has been generated';
    const SEED_NAMESPACE = 'Database\\Seed\\';
    const SEED_CLASS = 'ExampleSeed';
    const SEED_PATH = './database/Seed/';
    const SEED_FILE = self::SEED_PATH . 'ExampleSeed.php';
    const SEED_OBJECT = self::SEED_NAMESPACE . self::SEED_CLASS;
    const SEED_METHODS = ['run'];

    private CommandTester $commandTesterNew;
    private CommandTester $commandTesterSeed;
    private CommandTester $commandTesterFresh;

    protected function setUp(): void
    {
        $kernel = new Kernel();
        $container = new Container();

        $kernel->commandsOnObjects([
            $container->injectDependencies(new DBSeedCommand()),
            $container->injectDependencies(new SeedCommand()),
            $container->injectDependencies(new MigrationCommand()),
            $container->injectDependencies(new FreshMigrationsCommand()),
        ]);

        $this->commandTesterNew = new CommandTester($kernel->getApplication()->find('new:migration'));
        $this->commandTesterSeed = new CommandTester($kernel->getApplication()->find('new:seed'));
        $this->commandTesterFresh = new CommandTester($kernel->getApplication()->find('migrate:fresh'));

        $this->createDirectory('./database/Migrations/');
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNew->execute(['migration' => 'test']));
        $this->assertStringContainsString(self::OUTPUT_MIGRATION_CREATE_MESSAGE, $this->commandTesterNew->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTesterFresh->getDisplay());
    }

    public function testExecuteWithSeed(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterSeed->execute(['seed' => self::SEED_CLASS]));
        $this->assertStringContainsString(self::OUTPUT_SEED_CREATE_MESSAGE, $this->commandTesterSeed->getDisplay());
        $this->assertFileExists(self::SEED_FILE);

        $objClass = new (self::SEED_OBJECT)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::SEED_OBJECT, $objClass);
        $this->assertSame(self::SEED_METHODS, get_class_methods($objClass));
        $this->assertSame(Command::SUCCESS, $this->commandTesterNew->execute(['migration' => 'test']));
        $this->assertStringContainsString(self::OUTPUT_MIGRATION_CREATE_MESSAGE, $this->commandTesterNew->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute(['--seed' => '']));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTesterFresh->getDisplay());
        $this->assertStringContainsString(self::OUTPUT_SEED_MESSAGE, $this->commandTesterFresh->getDisplay());
    }
}
