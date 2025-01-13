<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\ShowDatabasesCommand;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ShowDatabasesCommandTest extends Test
{
    private const string MYSQL = 'mysql';
    private const string PORT = '3306';
    private const string DATABASE_NAME = 'lion_database';
    private const string DATABASE_USER = 'root';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = new Application();

        $application->add((new Container())->resolve(ShowDatabasesCommand::class));

        $this->commandTester = new CommandTester($application->find('db:show'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString(self::MYSQL, $display);
        $this->assertStringContainsString(self::PORT, $display);
        $this->assertStringContainsString(self::DATABASE_NAME, $display);
        $this->assertStringContainsString(self::DATABASE_USER, $display);
    }
}
