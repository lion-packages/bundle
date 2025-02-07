<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RulesCommandTest extends Test
{
    private const string URL_PATH = './app/Rules/';
    private const string NAMESPACE_CLASS = 'App\\Rules\\';
    private const string CLASS_NAME = 'TestRule';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'rule has been generated';
    private const string METHOD = 'passes';

    private CommandTester $commandTester;
    private Container $container;
    private RulesCommand $rulesCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->container = new Container();

        /** @var RulesCommand $rulesCommand */
        $rulesCommand = $this->container->resolve(RulesCommand::class);

        $this->rulesCommand = $rulesCommand;

        $application = new Application();

        $application->add($this->rulesCommand);

        $this->commandTester = new CommandTester($application->find('new:rule'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->rulesCommand);
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
        $this->assertInstanceOf(RulesCommand::class, $this->rulesCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(RulesCommand::class, $this->rulesCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'rule' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        $objClass = $this->container->resolve(self::OBJECT_NAME);

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, $objClass);
        $this->assertContains(self::METHOD, get_class_methods($objClass));
    }
}
