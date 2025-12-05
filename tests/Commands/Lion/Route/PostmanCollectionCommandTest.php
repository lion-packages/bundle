<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Route;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Commands\Lion\Route\PostmanCollectionCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\PostmanCollection;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class PostmanCollectionCommandTest extends Test
{
    private const string URL_PATH = './storage/postman/';
    private const string CLASS_NAME_RULE = 'UsersNameRule';

    private CommandTester $commandTester;
    private CommandTester $commandTesterRule;
    private PostmanCollectionCommand $postmanCollectionCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        $this->createDirectory(self::URL_PATH);

        $application = new Application();

        $container = new Container();

        /** @var RulesCommand $rulesCommand */
        $rulesCommand = $container->resolve(RulesCommand::class);

        /** @var PostmanCollectionCommand $postmanCollectionCommand */
        $postmanCollectionCommand = $container->resolve(PostmanCollectionCommand::class);

        $this->postmanCollectionCommand = $postmanCollectionCommand;

        $application->addCommand($rulesCommand);

        $application->addCommand($this->postmanCollectionCommand);

        $this->commandTester = new CommandTester($application->find('route:postman'));

        $this->commandTesterRule = new CommandTester($application->find('new:rule'));

        $this->initReflection($this->postmanCollectionCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);

        $this->rmdirRecursively('./app/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(
            PostmanCollectionCommand::class,
            $this->postmanCollectionCommand->setClassFactory(new ClassFactory())
        );

        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setPostmanCollection(): void
    {
        $this->assertInstanceOf(
            PostmanCollectionCommand::class,
            $this->postmanCollectionCommand->setPostmanCollection(new PostmanCollection())
        );

        $this->assertInstanceOf(PostmanCollection::class, $this->getPrivateProperty('postmanCollection'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(
            PostmanCollectionCommand::class,
            $this->postmanCollectionCommand->setStore(new Store())
        );

        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(
            PostmanCollectionCommand::class,
            $this->postmanCollectionCommand->setStr(new Str())
        );

        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTesterRule->execute(['rule' => self::CLASS_NAME_RULE]));

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));

        $jsonFile = self::URL_PATH . now()->format('Y_m_d') . '_lion_collection.json';

        $this->assertFileExists($jsonFile);

        $this->assertJsonFileEqualsJsonFile('./tests/Providers/Helpers/Commands/PostmanProvider.json', $jsonFile);
    }
}
