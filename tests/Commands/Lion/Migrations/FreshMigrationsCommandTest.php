<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\DB\DBSeedCommand;
use Lion\Bundle\Commands\Lion\Migrations\FreshMigrationsCommand;
use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Command\Kernel;
use Lion\Database\Drivers\Schema\MySQL;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class FreshMigrationsCommandTest extends Test
{
    private const string OUTPUT_SEED_CREATE_MESSAGE = 'seed has been generated';
    private const string OUTPUT_MESSAGE = 'Migrations executed successfully';
    private const string OUTPUT_MESSAGE_ERROR_PATH = 'there are no defined migration routes';
    private const string OUTPUT_SEED_MESSAGE = 'seeds executed';
    private const string OUTPUT_MIGRATION_CREATE_MESSAGE = 'migration has been generated';
    private const string SEED_NAMESPACE = 'Database\\Seed\\';
    private const string SEED_CLASS = 'ExampleSeed';
    private const string SEED_PATH = './database/Seed/';
    private const string SEED_FILE = self::SEED_PATH . 'ExampleSeed.php';
    private const string SEED_OBJECT = self::SEED_NAMESPACE . self::SEED_CLASS;
    private const array SEED_METHODS = [
        'run',
    ];

    private CommandTester $commandTesterNew;
    private CommandTester $commandTesterSeed;
    private CommandTester $commandTesterFresh;
    private FreshMigrationsCommand $freshMigrationsCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
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
     * @throws ReflectionException
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

    #[Testing]
    public function execute(): void
    {
        $this->createDirectory('./database/Migrations/');

        $this->assertSame(Command::SUCCESS, $this->commandTesterNew->execute([
            'migration' => 'test',
        ]));

        $this->assertStringContainsString(self::OUTPUT_MIGRATION_CREATE_MESSAGE, $this->commandTesterNew->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute([]));
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
        $this->createDirectory('./database/Migrations/');

        $this->assertSame(Command::SUCCESS, $this->commandTesterSeed->execute([
            'seed' => self::SEED_CLASS,
        ]));

        $this->assertStringContainsString(self::OUTPUT_SEED_CREATE_MESSAGE, $this->commandTesterSeed->getDisplay());
        $this->assertFileExists(self::SEED_FILE);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::SEED_OBJECT)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::SEED_OBJECT, $objClass);
        $this->assertSame(self::SEED_METHODS, get_class_methods($objClass));

        $this->assertSame(Command::SUCCESS, $this->commandTesterNew->execute([
            'migration' => 'test',
        ]));

        $this->assertStringContainsString(self::OUTPUT_MIGRATION_CREATE_MESSAGE, $this->commandTesterNew->getDisplay());

        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute([
            '--seed' => '',
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTesterFresh->getDisplay());
        $this->assertStringContainsString(self::OUTPUT_SEED_MESSAGE, $this->commandTesterFresh->getDisplay());

        $this->rmdirRecursively('./database/');
    }

    #[Testing]
    public function executePathDoesNotExist(): void
    {
        $this->assertSame(Command::FAILURE, $this->commandTesterFresh->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_ERROR_PATH, $this->commandTesterFresh->getDisplay());
    }
}
