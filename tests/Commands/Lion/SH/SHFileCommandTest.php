<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\SH;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\SHFileCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SHFileCommandTest extends Test
{
    private const string URL_PATH = './storage/sh/';
    private const string FILE_NAME = 'test-app';
    private const string FILE = self::URL_PATH . self::FILE_NAME . '.sh';
    private const string OUTPUT_MESSAGE = 'The script was generated successfully.';

    private CommandTester $commandTester;
    private SHFileCommand $shFileCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var SHFileCommand $shFileCommand */
        $shFileCommand = new Container()->resolve(SHFileCommand::class);

        $this->shFileCommand = $shFileCommand;

        $application = new Application();

        $application->add($this->shFileCommand);

        $this->commandTester = new CommandTester($application->find('new:sh'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->shFileCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively(self::URL_PATH);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(SHFileCommand::class, $this->shFileCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(SHFileCommand::class, $this->shFileCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'sh' => self::FILE_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::FILE);
    }
}
