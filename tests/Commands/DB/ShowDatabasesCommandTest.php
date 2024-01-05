<?php

declare(strict_types=1);

namespace Tests\Commands\DB;

use LionBundle\Commands\DB\ShowDatabasesCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Command;
use LionCommand\Kernel;
use LionTest\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\ConnectionTrait;

class ShowDatabasesCommandTest extends Test
{
    use ConnectionTrait;

    const MYSQL = 'mysql';
    const DB = 'db';
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
        $this->assertStringContainsString(self::DB, $display);
        $this->assertStringContainsString(self::PORT, $display);
        $this->assertStringContainsString(self::DATABASE_NAME, $display);
        $this->assertStringContainsString(self::DATABASE_USER, $display);
    }
}
