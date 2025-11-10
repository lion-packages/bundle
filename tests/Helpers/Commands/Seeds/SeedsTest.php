<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Seeds;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
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

class SeedsTest extends Test
{
    private const string CLASS_NAME = 'TestSeed';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string MESSAGE_OUTPUT = 'The seed was generated correctly.';

    private CommandTester $commandTester;
    private Seeds $seeds;
    private ClassFactory $classFactory;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var Seeds $seeds */
        $seeds = $container->resolve(Seeds::class);

        $this->seeds = $seeds;

        /** @var ClassFactory $classFactory */
        $classFactory = $container->resolve(ClassFactory::class);

        $this->classFactory = $classFactory;

        /** @var SeedCommand $seedCommand */
        $seedCommand = $container->resolve(SeedCommand::class);

        $application = new Application();

        $application->add($seedCommand);

        $this->commandTester = new CommandTester($application->find('new:seed'));

        $this->initReflection($this->seeds);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('database/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setMigrations(): void
    {
        $this->assertInstanceOf(Seeds::class, $this->seeds->setMigrations(new Migrations()));
        $this->assertInstanceOf(Migrations::class, $this->getPrivateProperty('migrations'));
    }

    #[Testing]
    public function executeSeedsGroup(): void
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

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'seed' => self::CLASS_NAME,
            '--connection' => $connectionName,
        ]));

        $this->assertStringContainsString(self::MESSAGE_OUTPUT, $this->commandTester->getDisplay());
        $this->assertFileExists($seedsPath . self::FILE_NAME);

        $this->classFactory->classFactory($seedsPath, self::CLASS_NAME);

        $seedClass = new ($this->classFactory->getNamespace() . "\\" . self::CLASS_NAME)();

        $this->assertInstanceOf(SeedInterface::class, $seedClass);

        $this->seeds->executeSeedsGroup([
            $seedClass::class,
        ]);
    }
}
