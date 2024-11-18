<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\ExceptionsCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Exceptions\Exception;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;

class ExceptionsCommandTest extends Test
{
    private const string URL_PATH = './app/Exceptions/';
    private const string NAMESPACE_CLASS = 'App\\Exceptions\\';
    private const string CLASS_NAME = 'TestException';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'exception has been generated';
    private const string CUSTOM_MESSAGE = 'Custom message';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->resolve(ExceptionsCommand::class));

        $this->commandTester = new CommandTester($application->find('new:exception'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['exception' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function executeWithException(): void
    {
        $this
            ->exception(self::OBJECT_NAME)
            ->exceptionMessage(self::CUSTOM_MESSAGE)
            ->exceptionStatus(Status::ERROR)
            ->exceptionCode(Http::INTERNAL_SERVER_ERROR)
            ->expectLionException(function (): void {
                $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['exception' => self::CLASS_NAME]));
                $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
                $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
            });
    }
}
