<?php

declare(strict_types=1);

namespace Tests\Commands\Lion;

use Lion\Bundle\Commands\Lion\InfoCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class InfoCommandTest extends Test
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel)->getApplication();
        $application->add((new Container)->injectDependencies(new InfoCommand()));
        $this->commandTester = new CommandTester($application->find('info'));
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
    }
}
