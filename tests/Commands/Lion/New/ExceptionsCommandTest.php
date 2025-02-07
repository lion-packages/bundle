<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\ExceptionsCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Exceptions\Exception;
use Lion\Files\Store;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
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
    private ExceptionsCommand $exceptionsCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var ExceptionsCommand $exceptionsCommand */
        $exceptionsCommand = new Container()->resolve(ExceptionsCommand::class);

        $this->exceptionsCommand = $exceptionsCommand;

        $application = new Application();

        $application->add($this->exceptionsCommand);

        $this->commandTester = new CommandTester($application->find('new:exception'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->exceptionsCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(
            ExceptionsCommand::class,
            $this->exceptionsCommand->setClassFactory(new ClassFactory())
        );

        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(ExceptionsCommand::class, $this->exceptionsCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'exception' => self::CLASS_NAME,
        ]));

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
                $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
                    'exception' => self::CLASS_NAME,
                ]));

                $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
                $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
            });
    }
}
