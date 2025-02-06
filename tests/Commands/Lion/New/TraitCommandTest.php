<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\TraitCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TraitCommandTest extends Test
{
    private const string URL_PATH = './app/Traits/';
    private const string CLASS_NAME = 'TestTrait';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'trait has been generated';

    private CommandTester $commandTester;
    private TraitCommand $traitCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var TraitCommand $traitCommand */
        $traitCommand = new Container()->resolve(TraitCommand::class);

        $this->traitCommand = $traitCommand;

        $application = new Application();

        $application->add($this->traitCommand);

        $this->commandTester = new CommandTester($application->find('new:trait'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->traitCommand);
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
        $this->assertInstanceOf(TraitCommand::class, $this->traitCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(TraitCommand::class, $this->traitCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'trait' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
