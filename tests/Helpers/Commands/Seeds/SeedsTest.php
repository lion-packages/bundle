<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Seeds;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SeedsTest extends Test
{
    private const string SEED_NAME = 'TestSeed';
    private const string FILE_NAME = self::SEED_NAME . '.php';
    private const string URL_PATH_SEED = 'database/Seed/';
    private const string NAMESPACE_SEED = 'Database\\Seed\\';
    private const string MESSAGE_OUTPUT = 'The seed was generated correctly.';

    private CommandTester $commandTester;
    private Seeds $seeds;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var SeedCommand $seedCommand */
        $seedCommand = $container->resolve(SeedCommand::class);

        /** @var Seeds $seeds */
        $seeds = $container->resolve(Seeds::class);

        $this->seeds = $seeds;

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
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['seed' => self::SEED_NAME]));
        $this->assertStringContainsString(self::MESSAGE_OUTPUT, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_SEED . self::FILE_NAME);

        $classObject = new (self::NAMESPACE_SEED . self::SEED_NAME)();

        $this->assertIsObject($classObject);

        $this->assertInstances($classObject, [
            SeedInterface::class,
            self::NAMESPACE_SEED . self::SEED_NAME,
        ]);

        $this->seeds->executeSeedsGroup([
            self::NAMESPACE_SEED . self::SEED_NAME,
        ]);
    }
}
