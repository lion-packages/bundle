<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmRunBuildCommand;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NpmRunBuildCommandTest extends Test
{
    const string PROJECT_NAME = 'test-app';
    const string DIST_PATH = 'resources/' . self::PROJECT_NAME . '/dist/';
    const string OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    const string OUTPUT_MESSAGE_INSTALL = 'dependencies have been installed';
    const string OUTPUT_MESSAGE_BUILD = 'project dist has been generated';

    private CommandTester $commandTesterNpmInit;
    private CommandTester $commandTesterNpmInstall;
    private CommandTester $commandTesterNpmBuild;

    protected function setUp(): void
    {
        $application = new Application();

        $container = new Container();

        $application->add($container->resolve(NpmInitCommand::class));

        $application->add($container->resolve(NpmInstallCommand::class));

        $application->add($container->resolve(NpmRunBuildCommand::class));

        $this->commandTesterNpmInit = new CommandTester($application->find('npm:init'));

        $this->commandTesterNpmInstall = new CommandTester($application->find('npm:install'));

        $this->commandTesterNpmBuild = new CommandTester($application->find('npm:build'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('resources/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmInit->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmInit->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmInstall->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INSTALL, $this->commandTesterNpmInstall->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmBuild->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_BUILD, $this->commandTesterNpmBuild->getDisplay());
        $this->assertDirectoryExists(self::DIST_PATH);
    }
}
