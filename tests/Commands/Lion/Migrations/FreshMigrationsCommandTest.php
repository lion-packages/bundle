<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Migrations;

use Lion\Bundle\Commands\Lion\Migrations\FreshMigrationsCommand;
use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class FreshMigrationsCommandTest extends Test
{
    const OUTPUT_MESSAGE = 'Migrations executed successfully';

    private CommandTester $commandTester;
    private CommandTester $commandTesterFresh;

    protected function setUp(): void
    {
        $kernel = new Kernel();
        $container = new Container();

        $kernel->commandsOnObjects([
            $container->injectDependencies(new MigrationCommand()),
            $container->injectDependencies(new FreshMigrationsCommand()),
        ]);

        $this->commandTesterFresh = new CommandTester($kernel->getApplication()->find('new:migration'));
        $this->commandTester = new CommandTester($kernel->getApplication()->find('migrate:fresh'));

        $this->createDirectory('./database/Migrations/');
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterFresh->execute(['migration' => 'test']));
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
