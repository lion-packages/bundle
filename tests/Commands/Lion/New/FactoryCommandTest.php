<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\FactoryCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class FactoryCommandTest extends Test
{
    private const string URL_PATH = './database/Factory/';
    private const string NAMESPACE_CLASS = 'Database\\Factory\\';
    private const string CLASS_NAME = 'TestFactory';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'factory has been generated';
    private const array METHOD = [
        'columns',
        'definition',
    ];

    private CommandTester $commandTester;
    private FactoryCommand $factoryCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var FactoryCommand $factoryCommand */
        $factoryCommand = new Container()->resolve(FactoryCommand::class);

        $this->factoryCommand = $factoryCommand;

        $application = new Application();

        $application->add($this->factoryCommand);

        $this->commandTester = new CommandTester($application->find('new:factory'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->factoryCommand);
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
        $this->assertInstanceOf(FactoryCommand::class, $this->factoryCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(FactoryCommand::class, $this->factoryCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'factory' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::METHOD, get_class_methods($objClass));

        /** @phpstan-ignore-next-line */
        $definition = $objClass->definition();

        $this->assertIsArray($definition);
        $this->assertCount(1, $definition);
    }
}
