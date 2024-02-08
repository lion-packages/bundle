<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\SH;

use Lion\Bundle\Commands\Lion\SH\SHFileCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class SHFileCommandTest extends Test
{
    const URL_PATH = './storage/sh/';
    const FILE_NAME = 'test-app';
    const FILE = self::URL_PATH . self::FILE_NAME . '.sh';
    const OUTPUT_MESSAGE = 'File generated successfully';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new SHFileCommand()));
        $this->commandTester = new CommandTester($application->find('sh:new'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['sh' => self::FILE_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::FILE);
    }
}
