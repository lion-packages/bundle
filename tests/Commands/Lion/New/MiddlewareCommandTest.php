<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\MiddlewareCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class MiddlewareCommandTest extends Test
{
    private const string URL_PATH = './app/Http/Middleware/';
    private const string NAMESPACE_CLASS = 'App\\Http\\Middleware\\';
    private const string CLASS_NAME = 'TestMiddlewares';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The middleware has been generated successfully.';

    private CommandTester $commandTester;
    private MiddlewareCommand $middlewareCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var MiddlewareCommand $middlewareCommand */
        $middlewareCommand = new Container()->resolve(MiddlewareCommand::class);

        $this->middlewareCommand = $middlewareCommand;

        $application = new Application();

        $application->addCommand($this->middlewareCommand);

        $this->commandTester = new CommandTester($application->find('new:middleware'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->middlewareCommand);
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
            MiddlewareCommand::class,
            $this->middlewareCommand->setClassFactory(new ClassFactory())
        );

        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(MiddlewareCommand::class, $this->middlewareCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'middleware' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
    }
}
