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
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;

class FreshMigrationsCommandTest extends Test
{
    private const string OUTPUT_SEED_CREATE_MESSAGE = 'seed has been generated';
    private const string OUTPUT_MESSAGE = 'Migrations executed successfully';
    private const string OUTPUT_SEED_MESSAGE = 'seeds executed';
    private const string OUTPUT_MIGRATION_CREATE_MESSAGE = 'migration has been generated';
    private const string SEED_NAMESPACE = 'Database\\Seed\\';
    private const string SEED_CLASS = 'ExampleSeed';
    private const string SEED_PATH = './database/Seed/';
    private const string SEED_FILE = self::SEED_PATH . 'ExampleSeed.php';
    private const string SEED_OBJECT = self::SEED_NAMESPACE . self::SEED_CLASS;
    private const array SEED_METHODS = ['run'];

    private CommandTester $commandTesterNew;
    private CommandTester $commandTesterSeed;
    private CommandTester $commandTesterFresh;

    protected function setUp(): void
    {
        $kernel = new Kernel();

        $container = new Container();

        $kernel->commandsOnObjects([
            $container->resolve(DBSeedCommand::class),
            $container->resolve(SeedCommand::class),
            $container->resolve(MigrationCommand::class),
            $container->resolve(FreshMigrationsCommand::class),
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

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNew->execute(['migration' => 'test']));
        $this->assertStringContainsString(self::OUTPUT_MIGRATION_CREATE_MESSAGE, $this->commandTesterNew->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTesterFresh->getDisplay());
    }

    #[Testing]
    public function executeWithSeed(): void
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
