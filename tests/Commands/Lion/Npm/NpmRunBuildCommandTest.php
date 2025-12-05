<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmRunBuildCommand;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class NpmRunBuildCommandTest extends Test
{
    private const string PROJECT_NAME = 'test-app';
    private const string DIST_PATH = 'resources/' . self::PROJECT_NAME . '/dist/';
    private const string OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    private const string OUTPUT_MESSAGE_INSTALL = 'dependencies have been installed';
    private const string OUTPUT_MESSAGE_BUILD = 'project dist has been generated';

    private CommandTester $commandTesterNpmInit;
    private CommandTester $commandTesterNpmInstall;
    private CommandTester $commandTesterNpmBuild;
    private NpmRunBuildCommand $npmRunBuildCommand;

    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $application = new Application();

        $container = new Container();

        /** @var NpmInitCommand $npmInitCommand */
        $npmInitCommand = $container->resolve(NpmInitCommand::class);

        /** @var NpmInstallCommand $npmInstallCommand */
        $npmInstallCommand = $container->resolve(NpmInstallCommand::class);

        /** @var NpmRunBuildCommand $npmRunBuildCommand */
        $npmRunBuildCommand = $container->resolve(NpmRunBuildCommand::class);

        $this->npmRunBuildCommand = $npmRunBuildCommand;

        $application->addCommand($npmInitCommand);

        $application->addCommand($npmInstallCommand);

        $application->addCommand($this->npmRunBuildCommand);

        $this->commandTesterNpmInit = new CommandTester($application->find('npm:init'));

        $this->commandTesterNpmInstall = new CommandTester($application->find('npm:install'));

        $this->commandTesterNpmBuild = new CommandTester($application->find('npm:build'));

        $this->initReflection($this->npmRunBuildCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('resources/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setKernel(): void
    {
        $this->assertInstanceOf(NpmRunBuildCommand::class, $this->npmRunBuildCommand->setKernel(new Kernel()));
        $this->assertInstanceOf(Kernel::class, $this->getPrivateProperty('kernel'));
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
