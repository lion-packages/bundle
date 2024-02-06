<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NpmInitCommandTest extends Test
{
    const PROJECT_NAME = 'test-app';
    const OUTPUT_MESSAGE = 'project has been generated successfully';
    const OUTPUT_MESSAGE_ERROR = 'a resource with this name already exists';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NpmInitCommand()));
        $this->commandTester = new CommandTester($application->find('npm:init'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./vite/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
    }

    public function testExecuteExistProject(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());

        $this->assertSame(Command::FAILURE, $this->commandTester->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_ERROR, $this->commandTester->getDisplay());
    }
}
