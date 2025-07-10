<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\ServiceCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ServiceCommandTest extends Test
{
    private const string URL_PATH = './app/Http/Services/';
    private const string NAMESPACE_CLASS = 'App\\Http\\Services\\';
    private const string CLASS_NAME = 'TestService';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The service was generated successfully.';

    private CommandTester $commandTester;
    private ServiceCommand $serviceCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var ServiceCommand $serviceCommand */
        $serviceCommand = new Container()->resolve(ServiceCommand::class);

        $this->serviceCommand = $serviceCommand;

        $application = new Application();

        $application->add($this->serviceCommand);

        $this->commandTester = new CommandTester($application->find('new:service'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->serviceCommand);
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
        $this->assertInstanceOf(ServiceCommand::class, $this->serviceCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(ServiceCommand::class, $this->serviceCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'service' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, new (self::OBJECT_NAME)());
    }
}
