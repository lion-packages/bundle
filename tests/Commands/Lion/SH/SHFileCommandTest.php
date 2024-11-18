<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\SH;

use Lion\Bundle\Commands\Lion\New\SHFileCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;

class SHFileCommandTest extends Test
{
    private const string URL_PATH = './storage/sh/';
    private const string FILE_NAME = 'test-app';
    private const string FILE = self::URL_PATH . self::FILE_NAME . '.sh';
    private const string OUTPUT_MESSAGE = 'File generated successfully';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->resolve(SHFileCommand::class));

        $this->commandTester = new CommandTester($application->find('new:sh'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['sh' => self::FILE_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::FILE);
    }
}
