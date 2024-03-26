<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Schedule;

use Lion\Bundle\Commands\Lion\New\CommandsCommand;
use Lion\Bundle\Commands\Lion\New\CronCommand;
use Lion\Bundle\Commands\Lion\Schedule\UpScheduleCommand;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class UpScheduleCommandTest extends Test
{
	const URL_PATH = './app/Console/Cron/';
    const URL_PATH_COMMAND = './app/Console/Commands/';
    const NAMESPACE_CLASS = 'App\\Console\\Cron\\';
    const CLASS_NAME = 'TestCron';
    const CLASS_NAME_COMMAND = 'ExampleCommand';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const FILE_NAME_COMMAND = self::CLASS_NAME_COMMAND . '.php';
    const OUTPUT_MESSAGE = 'cron has been generated';
    const OUTPUT_MESSAGE_COMMAND = 'command has been generated';
    const CONFIGURE_OUTPUT_MESSAGE = "SCHEDULE: App\Console\Commands\ExampleCommand";

    private CommandTester $commandTester;
    private CommandTester $commandTesterCommand;
    private CommandTester $commandTesterUp;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies(new CommandsCommand()));
        $application->add((new Container())->injectDependencies(new CronCommand()));
        $application->add((new Container())->injectDependencies(new UpScheduleCommand()));

        $this->commandTesterCommand = new CommandTester($application->find('new:command'));
        $this->commandTester = new CommandTester($application->find('new:cron'));
        $this->commandTesterUp = new CommandTester($application->find('schedule:up'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    public function testExecute(): void
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
        $this->assertSame(Command::SUCCESS, $this->commandTesterUp->execute([]));
        $this->assertStringContainsString(self::CONFIGURE_OUTPUT_MESSAGE, $this->commandTesterUp->getDisplay());
    }
}
