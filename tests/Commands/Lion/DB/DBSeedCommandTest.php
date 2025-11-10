<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use DI\DependencyException;
use DI\NotFoundException;
use InvalidArgumentException;
use Lion\Bundle\Commands\Lion\DB\DBSeedCommand;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Connection;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DBSeedCommandTest extends Test
{
    private const string CLASS_NAME = 'TestSeed';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'OK';
    private const string OUTPUT_MESSAGE_NOT_EXISTS_ERROR = 'There are no defined seeds.';
    private const string OUTPUT_MESSAGE_NEW_SEED = 'The seed was generated correctly.';

    private CommandTester $commandTester;
    private CommandTester $commandTesterNewSeed;
    private DBSeedCommand $dbSeedCommand;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var SeedCommand $seedCommand */
        $seedCommand = $container->resolve(SeedCommand::class);

        /** @var DBSeedCommand $dbSeedCommand */
        $dbSeedCommand = $container->resolve(DBSeedCommand::class);

        $this->dbSeedCommand = $dbSeedCommand;

        $application = new Application();

        $application->add($seedCommand);

        $application->add($this->dbSeedCommand);

        $this->commandTester = new CommandTester($application->find('db:seed'));

        $this->commandTesterNewSeed = new CommandTester($application->find('new:seed'));

        $this->initReflection($this->dbSeedCommand);
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(DBSeedCommand::class, $this->dbSeedCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(DBSeedCommand::class, $this->dbSeedCommand->setStr(new Str()));
        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(DBSeedCommand::class, $this->dbSeedCommand->setDatabaseEngine(new DatabaseEngine()));
        $this->assertInstanceOf(DatabaseEngine::class, $this->getPrivateProperty('databaseEngine'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setMigrations(): void
    {
        $this->assertInstanceOf(DBSeedCommand::class, $this->dbSeedCommand->setMigrations(new Migrations()));
        $this->assertInstanceOf(Migrations::class, $this->getPrivateProperty('migrations'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(DBSeedCommand::class, $this->dbSeedCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    #[Testing]
    public function executeWithoutConnection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The '--connection' option is required.");
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->commandTester->execute([]);
    }

    #[Testing]
    public function execute(): void
    {
        $this->createDirectory(Migrations::SEEDS_PATH);

        $connections = Connection::getConnections();

        $connectionName = getDefaultConnection();

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

        $this->assertSame(Command::SUCCESS, $this->commandTesterNewSeed->execute([
            'seed' => self::CLASS_NAME,
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE_NEW_SEED, $this->commandTesterNewSeed->getDisplay());
        $this->assertFileExists($seedsPath . self::FILE_NAME);

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists($seedsPath . self::FILE_NAME);

        $this->rmdirRecursively('./database/');
    }

    #[Testing]
    public function executeIfPathDoesNotExist(): void
    {
        $this->assertSame(Command::FAILURE, $this->commandTester->execute([
            '--connection' => getDefaultConnection(),
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE_NOT_EXISTS_ERROR, $this->commandTester->getDisplay());
    }
}
