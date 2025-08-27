<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use Lion\Bundle\Commands\Lion\Migrations\EmptyMigrationsCommand;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class EmptyMigrationsCommandTest extends Test
{
    private const string OUTPUT_MESSAGE = 'All tables have been truncated';

    private CommandTester $commandTester;
    private EmptyMigrationsCommand $emptyMigrationsCommand;

    protected function setUp(): void
    {
        $this->emptyMigrationsCommand = new EmptyMigrationsCommand()
            ->setMigrations(new Migrations());

        $application = new Application();

        $application->add($this->emptyMigrationsCommand);

        $this->commandTester = new CommandTester($application->find('migrate:empty'));

        $this->initReflection($this->emptyMigrationsCommand);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setMigrations(): void
    {
        $this->assertInstanceOf(
            EmptyMigrationsCommand::class,
            $this->emptyMigrationsCommand->setMigrations(new Migrations())
        );

        $this->assertInstanceOf(Migrations::class, $this->getPrivateProperty('migrations'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
