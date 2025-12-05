<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmUpdateCommand;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class NpmUpdateCommandTest extends Test
{
    private const string PROJECT_NAME = 'test-app';
    private const string OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    private const string OUTPUT_MESSAGE_UPDATE = 'dependencies have been updated';

    private CommandTester $commandTesterNpmIn;
    private CommandTester $commandTesterNpmU;
    private NpmUpdateCommand $npmUpdateCommand;

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

        /** @var NpmUpdateCommand $npmUpdateCommand */
        $npmUpdateCommand = $container->resolve(NpmUpdateCommand::class);

        $this->npmUpdateCommand = $npmUpdateCommand;

        $application->addCommand($npmInitCommand);

        $application->addCommand($this->npmUpdateCommand);

        $this->commandTesterNpmIn = new CommandTester($application->find('npm:init'));

        $this->commandTesterNpmU = new CommandTester($application->find('npm:update'));

        $this->initReflection($this->npmUpdateCommand);
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
        $this->assertInstanceOf(NpmUpdateCommand::class, $this->npmUpdateCommand->setKernel(new Kernel()));
        $this->assertInstanceOf(Kernel::class, $this->getPrivateProperty('kernel'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmIn->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmIn->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmU->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_UPDATE, $this->commandTesterNpmU->getDisplay());
    }
}
