<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\ExceptionsCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Request\Request;
use Lion\Request\Response;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class ExceptionsCommandTest extends Test
{
    const URL_PATH = './app/Exceptions/';
    const NAMESPACE_CLASS = 'App\\Exceptions\\';
    const CLASS_NAME = 'TestException';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'exception has been generated';
    const CUSTOM_MESSAGE = 'Custom message';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies(new ExceptionsCommand()));

        $this->commandTester = new CommandTester($application->find('new:exception'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['exception' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }

    public function testExecuteWithException(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['exception' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
        $this->expectException(self::OBJECT_NAME);
        $this->expectExceptionCode(Request::HTTP_INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage(self::CUSTOM_MESSAGE);

        throw new (self::OBJECT_NAME)(self::CUSTOM_MESSAGE, Response::ERROR, Request::HTTP_INTERNAL_SERVER_ERROR);
    }
}
