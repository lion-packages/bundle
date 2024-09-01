<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\ControllerCommand;
use Lion\Bundle\Commands\Lion\New\ModelCommand;
use Lion\Bundle\Helpers\Commands\ClassCommandFactory;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ControllerCommandTest extends Test
{
    private const string NAMESPACE_CLASS = 'App\\Http\\Controllers\\';
    private const string CLASS_NAME = 'TestController';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'controller has been generated';
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
    private const string OUTPUT_MESSAGE_MODEL = 'model has been generated';
    private const array MODEL_METHODS = [
        'createTestDB',
        'readTestDB',
        'updateTestDB',
        'deleteTestDB',
    ];

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = new Application();

        $application->add(
            (new ControllerCommand())
                ->setStr(new Str())
                ->setClassCommandFactory(
                    (new ClassCommandFactory())
                        ->setContainer(new Container())
                        ->setStore(new Store())
                )
        );

        $application->add(
            (new ModelCommand())
                ->setClassFactory(
                    (new ClassFactory())
                        ->setStore(new Store())
                )
                ->setStore(new Store())
                ->setStr(new Str())
        );

        $this->commandTester = new CommandTester($application->find('new:controller'));

        $this->createDirectory(ControllerCommand::PATH_CONTROLLER);

        $this->createDirectory(ControllerCommand::PATH_MODEL);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['controller' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(ControllerCommand::PATH_CONTROLLER . self::FILE_NAME);

        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
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

        $this->assertIsInt($commandExecute);
        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_MODEL, $this->commandTester->getDisplay());
        $this->assertFileExists(ControllerCommand::PATH_CONTROLLER . self::FILE_NAME);
        $this->assertFileExists(ControllerCommand::PATH_MODEL . self::FILE_NAME_MODEL);

        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::CONTROLLER_METHODS, get_class_methods($objClass));

        $objModelClass = new (self::OBJECT_NAME_MODEL)();

        $this->assertIsObject($objModelClass);
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

        $this->assertIsInt($commandExecute);
        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_MODEL, $this->commandTester->getDisplay());
        $this->assertFileExists(ControllerCommand::PATH_CONTROLLER . self::FILE_NAME);
        $this->assertFileExists(ControllerCommand::PATH_MODEL . self::FILE_NAME_MODEL);

        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::CONTROLLER_METHODS, get_class_methods($objClass));

        $objModelClass = new (self::OBJECT_NAME_MODEL)();

        $this->assertIsObject($objModelClass);
        $this->assertInstanceOf(self::OBJECT_NAME_MODEL, $objModelClass);
        $this->assertSame(self::MODEL_METHODS, get_class_methods($objModelClass));
    }
}
