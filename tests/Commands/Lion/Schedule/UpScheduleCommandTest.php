<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Schedule;

use Lion\Bundle\Commands\Lion\New\CommandsCommand;
use Lion\Bundle\Commands\Lion\New\CronCommand;
use Lion\Bundle\Commands\Lion\Schedule\UpScheduleCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpScheduleCommandTest extends Test
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
    private const string CONFIGURE_OUTPUT_MESSAGE = 'App\Console\Commands\ExampleCommand';

    private CommandTester $commandTesterCron;
    private CommandTester $commandTesterCommand;
    private CommandTester $commandTesterUp;
    private UpScheduleCommand $upScheduleCommand;

    protected function setUp(): void
    {
        $application = new Application();

        $application->add(
            (new CommandsCommand())
                ->setClassFactory(
                    (new ClassFactory())
                        ->setStore(new Store())
                )
                ->setStore(new Store())
        );

        $application->add(
            (new CronCommand())
                ->setClassFactory(
                    (new ClassFactory())
                        ->setStore(new Store())
                )
                ->setStore(new Store())
        );

        $this->upScheduleCommand = new UpScheduleCommand();

        $application->add(
            $this->upScheduleCommand
                ->setClassFactory(
                    (new ClassFactory())
                        ->setStore(new Store())
                )
                ->setContainer(new Container())
                ->setStore(new Store())
                ->setStr(new Str())
        );

        $this->commandTesterCommand = new CommandTester($application->find('new:command'));

        $this->commandTesterCron = new CommandTester($application->find('new:cron'));

        $this->commandTesterUp = new CommandTester($application->find('schedule:up'));

        $this->createDirectory(self::URL_PATH);

        $this->initReflection($this->upScheduleCommand);
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
        $this->assertSame(Command::SUCCESS, $this->commandTesterCron->execute(['cron' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTesterCron->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);

        /** @var ScheduleInterface $cronObject */
        $cronObject = new (self::OBJECT_NAME)();

        $this->assertInstances($cronObject, [self::OBJECT_NAME, ScheduleInterface::class]);
        $this->assertContains('schedule', get_class_methods($cronObject));
        $this->assertSame(Command::SUCCESS, $this->commandTesterUp->execute([]));
        $this->assertStringContainsString(self::CONFIGURE_OUTPUT_MESSAGE, $this->commandTesterUp->getDisplay());
    }
}
