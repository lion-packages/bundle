<?php

declare(strict_types=1);

namespace Tests\Commands\Migrations;

use LionBundle\Commands\Migrations\NewMigrateCommand;
use LionBundle\Helpers\Commands\Container;
use LionCommand\Command;
use LionCommand\Kernel;
use LionTest\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NewMigrateCommandTest extends Test
{
    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NewMigrateCommand()));
        $this->commandTester = new CommandTester($application->find('migrate:new'));
	}

	protected function tearDown(): void 
	{

	}

    public function testExecuteIsInvalid(): void
    {
        $commandExecute = $this->commandTester->execute([
            'migration' => 'users/create-users',
            'connection' => 'lion_database'
        ]);

        $this->assertSame(Command::INVALID, $commandExecute);
    }
}
