<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Schedule;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\New\CommandsCommand;
use Lion\Bundle\Commands\Lion\New\CronCommand;
use Lion\Bundle\Commands\Lion\Schedule\ListScheduleCommand;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ListScheduleCommandTest extends Test
{
    private const string URL_PATH = './app/Console/Cron/';
    private const string URL_PATH_COMMAND = './app/Console/Commands/';
    private const string NAMESPACE_CLASS = 'App\\Console\\Cron\\';
    private const string CLASS_NAME = 'TestCron';
    private const string CLASS_NAME_COMMAND = 'ExampleCommand';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string FILE_NAME_COMMAND = self::CLASS_NAME_COMMAND . '.php';
    private const string OUTPUT_MESSAGE = 'cron has been generated';
    private const string OUTPUT_MESSAGE_COMMAND = 'command has been generated';
    private const string CONFIGURE_OUTPUT_MESSAGE = 'example';

    private CommandTester $commandTester;
    private CommandTester $commandTesterCommand;
    private CommandTester $commandTesterList;
    private ListScheduleCommand $listScheduleCommand;

    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $application = new Application();

        $container = new Container();

        /** @var CommandsCommand $commandsCommand */
        $commandsCommand = $container->resolve(CommandsCommand::class);

        /** @var CronCommand $cronCommand */
        $cronCommand = $container->resolve(CronCommand::class);

        /** @var ListScheduleCommand $listScheduleCommand */
        $listScheduleCommand = $container->resolve(ListScheduleCommand::class);

        $this->listScheduleCommand = $listScheduleCommand;

        $application->add($commandsCommand);

        $application->add($cronCommand);

        $application->add($this->listScheduleCommand);

        $this->commandTesterCommand = new CommandTester($application->find('new:command'));

        $this->commandTester = new CommandTester($application->find('new:cron'));

        $this->commandTesterList = new CommandTester($application->find('schedule:list'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->listScheduleCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(ListScheduleCommand::class, $this->listScheduleCommand->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(ListScheduleCommand::class, $this->listScheduleCommand->setStr(new Str()));
        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(
            Command::SUCCESS,
            $this->commandTesterCommand->execute(['new-command' => self::CLASS_NAME_COMMAND])
        );

        $this->assertStringContainsString(self::OUTPUT_MESSAGE_COMMAND, $this->commandTesterCommand->getDisplay());
        $this->assertFileExists(self::URL_PATH_COMMAND . self::FILE_NAME_COMMAND);
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['cron' => self::CLASS_NAME]));
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
        $this->assertSame(Command::SUCCESS, $this->commandTesterList->execute([]));
        $this->assertStringContainsString(self::CONFIGURE_OUTPUT_MESSAGE, $this->commandTesterList->getDisplay());
    }
}
