<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use Lion\Bundle\Commands\Lion\Migrations\EmptyMigrationsCommand;
use Lion\Command\Command;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class EmptyMigrationsCommandTest extends Test
{
    use ConnectionProviderTrait;

    const OUTPUT_MESSAGE = 'all tables have been truncated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

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
