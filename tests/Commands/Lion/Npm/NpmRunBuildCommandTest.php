<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand;
use Lion\Bundle\Commands\Lion\Npm\NpmRunBuildCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class NpmRunBuildCommandTest extends Test
{
    const DIST_PATH = './vite/test-app/dist/';
    const PROJECT_NAME = 'test-app';
    const OUTPUT_MESSAGE_INIT_PROJECT = 'project has been generated successfully';
    const OUTPUT_MESSAGE_BUILD = 'project dist has been generated';

    private CommandTester $commandTesterNpmInit;
    private CommandTester $commandTesterNpmBuild;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new NpmInitCommand()));
        $application->add((new Container())->injectDependencies(new NpmRunBuildCommand()));

        $this->commandTesterNpmInit = new CommandTester($application->find('npm:init'));
        $this->commandTesterNpmBuild = new CommandTester($application->find('npm:build'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./vite/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmInit->execute(['project' => self::PROJECT_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_INIT_PROJECT, $this->commandTesterNpmInit->getDisplay());
        $this->assertSame(Command::SUCCESS, $this->commandTesterNpmBuild->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_BUILD, $this->commandTesterNpmBuild->getDisplay());
        $this->assertDirectoryExists(self::DIST_PATH);
    }
}
