<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\ExceptionCore;
use Lion\Bundle\Commands\Lion\New\ExceptionsCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Request\Request;
use Lion\Request\Response;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class ExceptionCoreTest extends Test
{
    const NAMESPACE_CLASS = 'App\\Exceptions\\';
    const CLASS_PATH = 'app/Exceptions/';
    const CLASS_NAME = 'TestException';
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const EXCEPTION_MESSAGE = 'exception message';

    private ExceptionCore $exceptionCore;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies(new ExceptionsCommand()));

        $this->commandTester = new CommandTester($application->find('new:exception'));

        $this->exceptionCore = new ExceptionCore();
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    public function testExceptionHandler(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['exception' => self::CLASS_NAME]));
        $this->assertFileExists(self::CLASS_PATH . self::FILE_NAME);

        $this->exceptionCore->exceptionHandler();

        $this->assertWithOb(json_encode([
            'code' => Request::HTTP_INTERNAL_SERVER_ERROR,
            'status' => Response::ERROR,
            'message' => self::EXCEPTION_MESSAGE,
            'data' => [
                'file' => __DIR__ . '/ExceptionCoreTest.php',
                'line' => 63
            ]
        ]), function () {
            echo (json_encode(
                $this->getExceptionFromApi(function () {
                    throw new (self::OBJECT_NAME)(self::EXCEPTION_MESSAGE, Request::HTTP_INTERNAL_SERVER_ERROR);
                })
            ));
        });
    }
}
