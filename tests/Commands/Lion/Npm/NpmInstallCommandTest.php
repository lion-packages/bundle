<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NpmInstallCommandTest extends Test
{
    const PROJECT_NAME = 'test-app';
    const OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    const OUTPUT_MESSAGE_INSTALL = 'project has been generated successfully';

    private CommandTester $commandTesterNpmInit;
    private CommandTester $commandTesterNpmInstall;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NpmInitCommand()));
        $application->add((new Container())->injectDependencies(new NpmInstallCommand()));

        $this->commandTesterNpmInit = new CommandTester($application->find('npm:init'));
        $this->commandTesterNpmInstall = new CommandTester($application->find('npm:install'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./vite/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmInit->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmInit->getDisplay());

        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmInstall->execute(['packages' => '']));
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmInstall->execute(['packages' => 'axios']));
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmInstall->execute(['packages' => 'dayjs jwt-decode']));
    }
}
