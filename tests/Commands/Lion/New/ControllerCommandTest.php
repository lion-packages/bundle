<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\ControllerCommand;
use Lion\Bundle\Commands\Lion\New\ModelCommand;
use Lion\Bundle\Helpers\Commands\ClassCommandFactory;
use Lion\Dependency\Injection\Container;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ControllerCommandTest extends Test
{
    private const string NAMESPACE_CLASS = 'App\\Http\\Controllers\\';
    private const string CLASS_NAME = 'TestController';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The controller class has been generated successfully.';
    private const array CONTROLLER_METHODS = [
        'createTest',
        'readTest',
        'updateTest',
        'deleteTest',
    ];

    private const string NAMESPACE_CLASS_MODEL = 'App\\Models\\';
    private const string CLASS_NAME_MODEL = 'TestModel';
    private const string OBJECT_NAME_MODEL = self::NAMESPACE_CLASS_MODEL . self::CLASS_NAME_MODEL;
    private const string FILE_NAME_MODEL = self::CLASS_NAME_MODEL . '.php';
    private const string OUTPUT_MESSAGE_MODEL = 'The model was generated successfully.';
    private const array MODEL_METHODS = [
        'createTestDB',
        'readTestDB',
        'updateTestDB',
        'deleteTestDB',
    ];

    private CommandTester $commandTester;
    private ControllerCommand $controllerCommand;

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws DependencyException
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var ControllerCommand $controllerCommand */
        $controllerCommand = $container->resolve(ControllerCommand::class);

        $this->controllerCommand = $controllerCommand;

        /** @var ModelCommand $modelCommand */
        $modelCommand = $container->resolve(ModelCommand::class);

        $application = new Application();

        $application->add($this->controllerCommand);

        $application->add($modelCommand);

        $this->commandTester = new CommandTester($application->find('new:controller'));

        $this->createDirectory(ControllerCommand::PATH_CONTROLLER);

        $this->createDirectory(ControllerCommand::PATH_MODEL);

        $this->initReflection($this->controllerCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(ControllerCommand::class, $this->controllerCommand->setStr(new Str()));
        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassCommandFactory(): void
    {
        $this->assertInstanceOf(
            ControllerCommand::class,
            $this->controllerCommand->setClassCommandFactory(new ClassCommandFactory())
        );

        $this->assertInstanceOf(ClassCommandFactory::class, $this->getPrivateProperty('classCommandFactory'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'controller' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(ControllerCommand::PATH_CONTROLLER . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::CONTROLLER_METHODS, get_class_methods($objClass));
    }

    #[Testing]
    public function executeWithModel(): void
    {
        $commandExecute = $this->commandTester->execute([
            'controller' => self::CLASS_NAME,
            '--model' => null,
        ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_MODEL, $this->commandTester->getDisplay());
        $this->assertFileExists(ControllerCommand::PATH_CONTROLLER . self::FILE_NAME);
        $this->assertFileExists(ControllerCommand::PATH_MODEL . self::FILE_NAME_MODEL);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::CONTROLLER_METHODS, get_class_methods($objClass));

        /** @phpstan-ignore-next-line */
        $objModelClass = new (self::OBJECT_NAME_MODEL)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME_MODEL, $objModelClass);
        $this->assertSame(self::MODEL_METHODS, get_class_methods($objModelClass));
    }

    #[Testing]
    public function executeWithModelWrite(): void
    {
        $commandExecute = $this->commandTester->execute([
            'controller' => self::CLASS_NAME,
            '--model' => self::CLASS_NAME_MODEL,
        ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_MODEL, $this->commandTester->getDisplay());
        $this->assertFileExists(ControllerCommand::PATH_CONTROLLER . self::FILE_NAME);
        $this->assertFileExists(ControllerCommand::PATH_MODEL . self::FILE_NAME_MODEL);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::CONTROLLER_METHODS, get_class_methods($objClass));

        /** @phpstan-ignore-next-line */
        $objModelClass = new (self::OBJECT_NAME_MODEL)();

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME_MODEL, $objModelClass);
        $this->assertSame(self::MODEL_METHODS, get_class_methods($objModelClass));
    }
}
