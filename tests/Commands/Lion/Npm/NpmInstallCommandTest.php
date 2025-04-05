<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class NpmInstallCommandTest extends Test
{
    private const string PROJECT_NAME = 'test-app';
    private const string OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    private const string OUTPUT_MESSAGE_INSTALL = 'dependencies have been installed';

    private CommandTester $commandTesterNpmIn;
    private CommandTester $commandTesterNpmI;
    private NpmInstallCommand $npmInstallCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $container = new Container();

        $application = new Application();

        /** @var NpmInitCommand $npmInitCommand */
        $npmInitCommand = $container->resolve(NpmInitCommand::class);

        /** @var NpmInstallCommand $npmInstallCommand */
        $npmInstallCommand = $container->resolve(NpmInstallCommand::class);

        $this->npmInstallCommand = $npmInstallCommand;

        $application->add($npmInitCommand);

        $application->add($this->npmInstallCommand);

        $this->commandTesterNpmIn = new CommandTester($application->find('npm:init'));

        $this->commandTesterNpmI = new CommandTester($application->find('npm:install'));

        $this->initReflection($this->npmInstallCommand);
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
        $this->assertInstanceOf(NpmInstallCommand::class, $this->npmInstallCommand->setKernel(new Kernel()));
        $this->assertInstanceOf(Kernel::class, $this->getPrivateProperty('kernel'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmIn->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmIn->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmI->execute(['packages' => ['']]));
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmI->execute(['packages' => ['axios']]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INSTALL, $this->commandTesterNpmI->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmI->execute(['packages' => ['dayjs jwt-decode']]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INSTALL, $this->commandTesterNpmI->getDisplay());
    }
}
