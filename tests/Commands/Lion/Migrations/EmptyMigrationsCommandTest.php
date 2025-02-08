<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use Lion\Bundle\Commands\Lion\Migrations\EmptyMigrationsCommand;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class EmptyMigrationsCommandTest extends Test
{
    private const string OUTPUT_MESSAGE = 'all tables have been truncated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = new Application();

        $application->add(new EmptyMigrationsCommand());

        $this->commandTester = new CommandTester($application->find('migrate:empty'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
