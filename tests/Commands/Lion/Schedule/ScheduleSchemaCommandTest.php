<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Schedule;

use Lion\Bundle\Commands\Lion\Schedule\ScheduleSchemaCommand;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Request\Status;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class ScheduleSchemaCommandTest extends Test
{
    use ConnectionProviderTrait;

    const MESSAGE = 'the schema for queued tasks has been created';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies(new ScheduleSchemaCommand()));

        $this->commandTester = new CommandTester($application->find('schedule:schema'));
    }

    protected function tearDown(): void
    {
        Schema::dropTable('task_queue');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->setInputs(['0'])->execute([]));
        $this->assertStringContainsString(self::MESSAGE, $this->commandTester->getDisplay());

        $response = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(Status::SUCCESS, $response->status);
        $this->assertSame('no data available', $response->message);
    }
}
