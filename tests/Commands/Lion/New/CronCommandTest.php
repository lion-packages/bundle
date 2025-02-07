<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\CronCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CronCommandTest extends Test
{
    private const string URL_PATH = './app/Console/Cron/';
    private const string NAMESPACE_CLASS = 'App\\Console\\Cron\\';
    private const string CLASS_NAME = 'TestCron';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'cron has been generated';

    private CommandTester $commandTester;
    private CronCommand $cronCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var CronCommand $cronCommand */
        $cronCommand = new Container()->resolve(CronCommand::class);

        $this->cronCommand = $cronCommand;

        $application = new Application();

        $application->add($this->cronCommand);

        $this->commandTester = new CommandTester($application->find('new:cron'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->cronCommand);
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
        $this->assertInstanceOf(CronCommand::class, $this->cronCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(CronCommand::class, $this->cronCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([
            'cron' => self::CLASS_NAME,
        ]));

        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $cronObject = new (self::OBJECT_NAME)();

        /** @phpstan-ignore-next-line */
        $this->assertInstances($cronObject, [
            self::OBJECT_NAME,
            ScheduleInterface::class,
        ]);

        /** @phpstan-ignore-next-line */
        $this->assertContains('schedule', get_class_methods($cronObject));
    }
}
