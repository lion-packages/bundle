<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\ShowDatabasesCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class ShowDatabasesCommandTest extends Test
{
    use ConnectionProviderTrait;

    const MYSQL = 'mysql';
    const PORT = '3306';
    const DATABASE_NAME = 'lion_database (default)';
    const DATABASE_USER = 'root';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new ShowDatabasesCommand()));
        $this->commandTester = new CommandTester($application->find('db:show'));

        $this->runDatabaseConnections();
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString(self::MYSQL, $display);
        $this->assertStringContainsString(self::PORT, $display);
        $this->assertStringContainsString(self::DATABASE_NAME, $display);
        $this->assertStringContainsString(self::DATABASE_USER, $display);
    }
}
