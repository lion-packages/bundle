<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Schedule;

use Lion\Bundle\Commands\Lion\Schedule\RunQueuedTasksCommand;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class RunQueuedTasksCommandTest extends Test
{
    use ConnectionProviderTrait;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $application = (new Kernel())
            ->getApplication();

        $application->add(
            (new Container())
                ->injectDependencies(new RunQueuedTasksCommand())
        );

        $this->commandTester = new CommandTester($application->find('schedule:run'));
    }

    public function testExecute(): void
    {
        $this->assertTrue(true);
    }
}
