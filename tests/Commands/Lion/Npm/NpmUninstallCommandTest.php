<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmUninstallCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NpmUninstallCommandTest extends Test
{
    const PROJECT_NAME = 'test-app';
    const OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    const OUTPUT_MESSAGE_INSTALL = 'dependencies have been installed';
    const OUTPUT_MESSAGE_UNINSTALL = 'dependencies have been uninstalled';

    private CommandTester $commandTesterNpmIn;
    private CommandTester $commandTesterNpmI;
    private CommandTester $commandTesterNpmU;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NpmInitCommand()));
        $application->add((new Container())->injectDependencies(new NpmInstallCommand()));
        $application->add((new Container())->injectDependencies(new NpmUninstallCommand()));

        $this->commandTesterNpmIn = new CommandTester($application->find('npm:init'));
        $this->commandTesterNpmI = new CommandTester($application->find('npm:install'));
        $this->commandTesterNpmU = new CommandTester($application->find('npm:uninstall'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./vite/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmIn->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmIn->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmI->execute(['packages' => 'axios']));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INSTALL, $this->commandTesterNpmI->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmU->execute(['packages' => 'axios']));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_UNINSTALL, $this->commandTesterNpmU->getDisplay());
    }
}
