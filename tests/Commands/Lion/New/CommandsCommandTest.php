<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\CommandsCommand;
use Lion\Bundle\Commands\Lion\New\HtmlCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CommandsCommandTest extends Test
{
    private const string URL_PATH = './app/Console/Commands/';
    private const string NAMESPACE_CLASS = 'App\\Console\\Commands\\';
    private const string CLASS_NAME = 'TestCommand';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'command has been generated';

    private CommandTester $commandTester;
    private CommandsCommand $commandsCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var CommandsCommand $commandsCommand */
        $commandsCommand = new Container()->resolve(CommandsCommand::class);

        $this->commandsCommand = $commandsCommand;

        $application = new Application();

        $application->add($this->commandsCommand);

        $this->commandTester = new CommandTester($application->find('new:command'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->commandsCommand);
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
        $this->assertInstanceOf(CommandsCommand::class, $this->commandsCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(CommandsCommand::class, $this->commandsCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'new-command' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->assertInstances($objClass, [
            self::OBJECT_NAME,
            Command::class,
        ]);
    }
}
