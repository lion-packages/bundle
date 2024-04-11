<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmUpdateCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NpmUpdateCommandTest extends Test
{
    const PROJECT_NAME = 'test-app';
    const OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    const OUTPUT_MESSAGE_UPDATE = 'dependencies have been updated';

    private CommandTester $commandTesterNpmIn;
    private CommandTester $commandTesterNpmU;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NpmInitCommand()));
        $application->add((new Container())->injectDependencies(new NpmUpdateCommand()));

        $this->commandTesterNpmIn = new CommandTester($application->find('npm:init'));
        $this->commandTesterNpmU = new CommandTester($application->find('npm:update'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./vite/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmIn->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmIn->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmU->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_UPDATE, $this->commandTesterNpmU->getDisplay());
    }
}
