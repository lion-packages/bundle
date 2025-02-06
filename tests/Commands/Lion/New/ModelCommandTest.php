<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\ModelCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ModelCommandTest extends Test
{
    private const string URL_PATH = './app/Models/';
    private const string NAMESPACE_CLASS = 'App\\Models\\';
    private const string CLASS_NAME = 'TestModel';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'model has been generated';
    private const array MODEL_METHODS = [
        'createTestDB',
        'readTestDB',
        'updateTestDB',
        'deleteTestDB',
    ];

    private CommandTester $commandTester;
    private ModelCommand $modelCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var ModelCommand $modelCommand */
        $modelCommand = new Container()->resolve(ModelCommand::class);

        $this->modelCommand = $modelCommand;

        $application = new Application();

        $application->add($this->modelCommand);

        $this->commandTester = new CommandTester($application->find('new:model'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->modelCommand);
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
        $this->assertInstanceOf(ModelCommand::class, $this->modelCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(ModelCommand::class, $this->modelCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(ModelCommand::class, $this->modelCommand->setStr(new Str()));
        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'model' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::MODEL_METHODS, get_class_methods($objClass));
    }
}
