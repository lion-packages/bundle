<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Seeds;

use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
use Lion\Bundle\Interface\SeedInterface;
use Lion\Command\Command;
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
    private const string MESSAGE_OUTPUT = 'seed has been generated';

    private CommandTester $commandTester;
    private Seeds $seeds;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->seeds = (new Seeds())
            ->setMigrations(new Migrations());

        $application = new Application();

        $application->add(
            (new SeedCommand())
                ->setClassFactory(
                    (new ClassFactory())
                        ->setStore(new Store())
                )
                ->setStore(new Store())
        );

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