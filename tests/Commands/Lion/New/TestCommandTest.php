<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lion\Bundle\Commands\Lion\New\TestCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TestCommandTest extends Test
{
    private const string CLASS_NAME = 'TestTest';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The test has been generated successfully.';

    private CommandTester $commandTester;
    private TestCommand $testCommand;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        /** @var TestCommand $testCommand */
        $testCommand = new Container()->resolve(TestCommand::class);

        $this->testCommand = $testCommand;

        $application = new Application();

        $application->add($this->testCommand);

        $this->commandTester = new CommandTester($application->find('new:test'));

        $this->initReflection($this->testCommand);
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        new Store()->remove(TestCommand::TEST_PATH . self::FILE_NAME);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(TestCommand::class, $this->testCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(TestCommand::class, $this->testCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'test' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(TestCommand::TEST_PATH . self::FILE_NAME);
    }
}
