<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Schedule;

use Lion\Bundle\Commands\Lion\New\CronCommand;
use Lion\Bundle\Commands\Lion\Schedule\ListScheduleCommand;
use Lion\Bundle\Interface\ScheduleInterface;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;

class ListScheduleCommandTest extends Test
{
    const URL_PATH = './app/Console/Cron/';
    const NAMESPACE_CLASS = 'App\\Console\\Cron\\';
    const CLASS_NAME = 'TestCron';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const FILE_NAME = self::CLASS_NAME . '.php';
    const OUTPUT_MESSAGE = 'cron has been generated';
    const CONFIGURE_OUTPUT_MESSAGE = "cron has not been configured 'App\Console\Cron\TestCron'";

    private CommandTester $commandTester;
    private CommandTester $commandTesterList;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new CronCommand()));
        $application->add((new Container())->injectDependencies(new ListScheduleCommand()));

        $this->commandTester = new CommandTester($application->find('new:cron'));
        $this->commandTesterList = new CommandTester($application->find('schedule:list'));

        $this->createDirectory(self::URL_PATH);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');
    }

    public function testExecute(): void
    {
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
