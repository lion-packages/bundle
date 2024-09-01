<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\ModelCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
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

    protected function setUp(): void
    {
        $application = new Application();

        $application->add(
            (new ModelCommand())
                ->setClassFactory(
                    (new ClassFactory())
                        ->setStore(new Store())
                )
                ->setStore(new Store())
                ->setStr(new Str())
        );

        $this->commandTester = new CommandTester($application->find('new:model'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['model' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::MODEL_METHODS, get_class_methods($objClass));
    }
}
