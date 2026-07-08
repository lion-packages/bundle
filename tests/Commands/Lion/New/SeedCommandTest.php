<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Database\Connection;
use Lion\Dependency\Injection\Container;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SeedCommandTest extends Test
{
    private const string CLASS_NAME = 'TestSeed';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The seed was generated correctly.';

    private CommandTester $commandTester;
    private SeedCommand $seedCommand;
    private ClassFactory $classFactory;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var SeedCommand $seedCommand */
        $seedCommand = $container->resolve(SeedCommand::class);

        $this->seedCommand = $seedCommand;

        /** @var ClassFactory $classFactory */
        $classFactory = $container->resolve(ClassFactory::class);

        $this->classFactory = $classFactory;

        $application = new Application();

        $application->addCommand($this->seedCommand);

        $this->commandTester = new CommandTester($application->find('new:seed'));

        $this->createDirectory(Migrations::SEEDS_PATH);

        $this->initReflection($this->seedCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     *  class.
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(SeedCommand::class, $this->seedCommand->setDatabaseEngine(new DatabaseEngine()));
        $this->assertInstanceOf(DatabaseEngine::class, $this->getPrivateProperty('databaseEngine'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     *  class.
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(SeedCommand::class, $this->seedCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    #[Testing]
    public function execute(): void
    {
        $connectionName = getDefaultConnection();

        $connections = Connection::getConnections();

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

        $this->assertSame(
            Command::SUCCESS,
            $this->commandTester
                ->setInputs([Connection::getDefaultConnectionName()])
                ->execute(['seed' => self::CLASS_NAME])
        );

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists($seedsPath . self::FILE_NAME);

        $this->classFactory->classFactory($seedsPath, self::CLASS_NAME);

        $objClass = new ($this->classFactory->getNamespace() . "\\" . self::CLASS_NAME)();

        $this->assertInstanceOf(SeedInterface::class, $objClass);
    }
}
