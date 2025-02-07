<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NpmInitCommandTest extends Test
{
    private const string PROJECT_NAME = 'test-app';
    private const string OUTPUT_MESSAGE = 'project has been generated successfully';
    private const string OUTPUT_MESSAGE_ERROR = 'a resource with this name already exists';

    private CommandTester $commandTester;
    private NpmInitCommand $npmInitCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var NpmInitCommand $npmInitCommand */
        $npmInitCommand = new Container()->resolve(NpmInitCommand::class);

        $this->npmInitCommand = $npmInitCommand;

        $application = new Application();

        $application->add($this->npmInitCommand);

        $this->commandTester = new CommandTester($application->find('npm:init'));

        $this->initReflection($this->npmInitCommand);
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
        $this->assertInstanceOf(NpmInitCommand::class, $this->npmInitCommand->setKernel(new Kernel()));
        $this->assertInstanceOf(Kernel::class, $this->getPrivateProperty('kernel'));
    }

    #[Testing]
    public function executeViteProject(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                0,
                2,
                0,
            ])
            ->execute([
                'project' => self::PROJECT_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }

    #[Testing]
    public function executeAstroProject(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                1,
            ])
            ->execute([
                'project' => self::PROJECT_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }

    #[Testing]
    public function executeExistProject(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                0,
                2,
                0,
            ])
            ->execute([
                'project' => self::PROJECT_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertSame(Command::FAILURE, $this->commandTester->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_ERROR, $this->commandTester->getDisplay());
    }

    #[Testing]
    public function executeElectronVite(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                0,
                8,
                2,
                0,
            ])
            ->execute([
                'project' => self::PROJECT_NAME,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }
}
