<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\DB\DBSeedCommand;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DBSeedCommandTest extends Test
{
    private const string URL_PATH = './database/Seed/';
    private const string CLASS_NAME = 'TestSeed';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'run seed';
    private const string OUTPUT_MESSAGE_NOT_EXISTS_ERROR = 'there are no defined seeds';
    private const string OUTPUT_MESSAGE_NEW_SEED = 'seed has been generated';

    private CommandTester $commandTester;
    private CommandTester $commandTesterNewSeed;
    private DBSeedCommand $dbSeedCommand;

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws DependencyException
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
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(DBSeedCommand::class, $this->dbSeedCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setMigrations(): void
    {
        $this->assertInstanceOf(DBSeedCommand::class, $this->dbSeedCommand->setMigrations(new Migrations()));
        $this->assertInstanceOf(Migrations::class, $this->getPrivateProperty('migrations'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->createDirectory(self::URL_PATH);

        $this->assertSame(Command::SUCCESS, $this->commandTesterNewSeed->execute([
            'seed' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE_NEW_SEED, $this->commandTesterNewSeed->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
        $this->assertSame(Command::SUCCESS, $this->commandTester->setInputs(['0'])->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $this->rmdirRecursively('./database/');
    }

    #[Testing]
    public function executeIfPathDoesNotExist(): void
    {
        $this->assertSame(Command::FAILURE, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_NOT_EXISTS_ERROR, $this->commandTester->getDisplay());
    }
}
