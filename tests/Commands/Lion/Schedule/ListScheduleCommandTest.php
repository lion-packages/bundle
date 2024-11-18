<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Schedule;

use Lion\Bundle\Commands\Lion\New\CommandsCommand;
use Lion\Bundle\Commands\Lion\New\CronCommand;
use Lion\Bundle\Commands\Lion\Schedule\ListScheduleCommand;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
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

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $container = new Container();

        $application->add($container->resolve(CommandsCommand::class));

        $application->add($container->resolve(CronCommand::class));

        $application->add($container->resolve(ListScheduleCommand::class));

        $this->commandTesterCommand = new CommandTester($application->find('new:command'));

        $this->commandTester = new CommandTester($application->find('new:cron'));

        $this->commandTesterList = new CommandTester($application->find('schedule:list'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
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

        /** @var ScheduleInterface $cronObject */
        $cronObject = new (self::OBJECT_NAME)();

        $this->assertInstances($cronObject, [self::OBJECT_NAME, ScheduleInterface::class]);
        $this->assertContains('schedule', get_class_methods($cronObject));
        $this->assertSame(Command::SUCCESS, $this->commandTesterList->execute([]));
        $this->assertStringContainsString(self::CONFIGURE_OUTPUT_MESSAGE, $this->commandTesterList->getDisplay());
    }
}
