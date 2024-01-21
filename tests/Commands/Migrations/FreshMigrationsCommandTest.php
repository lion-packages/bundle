<?php

declare(strict_types=1);

namespace Tests\Commands\Migrations;

use Lion\Bundle\Commands\Migrations\FreshMigrationsCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class FreshMigrationsCommandTest extends Test
{
    const OUTPUT_MESSAGE = 'Migrations executed successfully';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new FreshMigrationsCommand()));
        $this->commandTester = new CommandTester($application->find('migrate:fresh'));

        $this->createDirectory('./database/Migrations/');
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/Migrations/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
