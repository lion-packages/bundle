<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\ClassCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test As Testing;
use Symfony\Component\Console\Tester\CommandTester;

class ClassCommandTest extends Test
{
    private const string URL_PATH = './app/';
    private const string NAMESPACE_CLASS = 'App\\';
    private const string CLASS_NAME = 'TestClass';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'class has been generated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->resolve(ClassCommand::class));

        $this->commandTester = new CommandTester($application->find('new:class'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['class' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $classObject = new (self::OBJECT_NAME)();

        $this->assertIsObject($classObject);
        $this->assertInstanceOf(self::OBJECT_NAME, $classObject);
    }
}
