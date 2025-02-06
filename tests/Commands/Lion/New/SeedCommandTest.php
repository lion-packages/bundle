<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\SeedCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SeedCommandTest extends Test
{
    private const string URL_PATH = './database/Seed/';
    private const string NAMESPACE_CLASS = 'Database\\Seed\\';
    private const string CLASS_NAME = 'TestSeed';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'seed has been generated';
    private const array METHOD = ['run'];

    private CommandTester $commandTester;
    private SeedCommand $seedCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var SeedCommand $seedCommand */
        $seedCommand = new Container()->resolve(SeedCommand::class);

        $this->seedCommand = $seedCommand;

        $application = new Application();

        $application->add($this->seedCommand);

        $this->commandTester = new CommandTester($application->find('new:seed'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->seedCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./database/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(SeedCommand::class, $this->seedCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(SeedCommand::class, $this->seedCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'seed' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $objClass = new (self::OBJECT_NAME)();

        $this->assertIsObject($objClass);
        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertSame(self::METHOD, get_class_methods($objClass));
    }
}
