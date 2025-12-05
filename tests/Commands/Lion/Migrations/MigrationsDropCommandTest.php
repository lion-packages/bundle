<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\Migrations\MigrationsDropCommand;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Test\Test;
use Lion\Dependency\Injection\Container;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class MigrationsDropCommandTest extends Test
{
    private const string OUTPUT_MESSAGE = 'Drop the database schema.';

    private CommandTester $commandTester;
    private MigrationsDropCommand $migrationsDropCommand;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        /** @var MigrationsDropCommand $migrationsDropCommand */
        $migrationsDropCommand = new Container()->resolve(MigrationsDropCommand::class);

        $this->migrationsDropCommand = $migrationsDropCommand;

        $application = new Application();

        $application->addCommand($this->migrationsDropCommand);

        $this->commandTester = new CommandTester($application->find('migrate:drop'));

        $this->initReflection($this->migrationsDropCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('lion_database.sqlite');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setMigrations(): void
    {
        $this->assertInstanceOf(
            MigrationsDropCommand::class,
            $this->migrationsDropCommand->setMigrations(new Migrations())
        );

        $this->assertInstanceOf(Migrations::class, $this->getPrivateProperty('migrations'));
    }

    #[Testing]
    public function execute(): void
    {
        $response = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $response);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
