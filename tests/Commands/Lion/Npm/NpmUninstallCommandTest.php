<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmUninstallCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;

class NpmUninstallCommandTest extends Test
{
    private const string PROJECT_NAME = 'test-app';
    private const string OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    private const string OUTPUT_MESSAGE_INSTALL = 'dependencies have been installed';
    private const string OUTPUT_MESSAGE_UNINSTALL = 'dependencies have been uninstalled';

    private CommandTester $commandTesterNpmIn;
    private CommandTester $commandTesterNpmI;
    private CommandTester $commandTesterNpmU;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $container = new Container();

        $application->add($container->resolve(NpmInitCommand::class));

        $application->add($container->resolve(NpmInstallCommand::class));

        $application->add($container->resolve(NpmUninstallCommand::class));

        $this->commandTesterNpmIn = new CommandTester($application->find('npm:init'));

        $this->commandTesterNpmI = new CommandTester($application->find('npm:install'));

        $this->commandTesterNpmU = new CommandTester($application->find('npm:uninstall'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('resources/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmIn->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmIn->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmI->execute(['packages' => ['axios']]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INSTALL, $this->commandTesterNpmI->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmU->execute(['packages' => ['axios']]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_UNINSTALL, $this->commandTesterNpmU->getDisplay());
    }
}
