<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB\MySQL;

use Lion\Bundle\Commands\Lion\DB\MySQL\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class DBCapsuleCommandTest extends Test
{
    use ConnectionProviderTrait;

    const URL_PATH = './database/Class/LionDatabase/MySQL/';
    const NAMESPACE_CLASS = 'Database\\Class\\LionDatabase\\MySQL\\';
    const ENTITY = 'test';
    const CLASS_NAME = 'Test';
    const OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    const OUTPUT_MESSAGE = 'capsule has been generated';

    private CommandTester $commandTester;

	protected function setUp(): void 
	{
        $this->runDatabaseConnections();
        $this->createDirectory(self::URL_PATH);

        Schema::createTable(self::ENTITY, function() {
            Schema::int('id', 11)->notNull()->autoIncrement()->primaryKey();
            Schema::varchar('name', 25)->notNull();
        })->execute();

        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new CapsuleCommand()));
        $application->add((new Container())->injectDependencies(new DBCapsuleCommand()));
        $this->commandTester = new CommandTester($application->find('db:mysql:capsule'));
	}

	protected function tearDown(): void 
	{
        Schema::dropTable(self::ENTITY)->execute();
        $this->rmdirRecursively('./database/');
	}

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['entity' => self::ENTITY]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertInstanceOf(self::OBJECT_NAME, new (self::OBJECT_NAME)());
    }
}
