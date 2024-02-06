<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB\MySQL;

use Lion\Bundle\Commands\Lion\DB\MySQL\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\DB\MySQL\DBCapsulesCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\DependencyInjection\Container;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class DBCapsulesCommandTest extends Test
{
    use ConnectionProviderTrait;

    const URL_PATH = './database/Class/LionDatabase/MySQL/';
    const NAMESPACE_CLASS = 'Database\\Class\\LionDatabase\\MySQL\\';
    const TEST_ENTITY = 'test';
    const USERS_ENTITY = 'users';
    const CLASS_NAME_TEST = 'Test';
    const CLASS_NAME_USERS = 'Users';
    const OBJECT_NAME_TEST = self::NAMESPACE_CLASS . self::CLASS_NAME_TEST;
    const OBJECT_NAME_USERS = self::NAMESPACE_CLASS . self::CLASS_NAME_USERS;
    const OUTPUT_MESSAGE = 'capsule has been generated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();
        $this->createDirectory(self::URL_PATH);

        Schema::createTable(self::TEST_ENTITY, function() {
            Schema::int('id', 11)->notNull()->autoIncrement()->primaryKey()
                ->varchar('name', 25)->notNull();
        })->execute();

        Schema::createTable(self::USERS_ENTITY, function() {
            Schema::int('id', 11)->notNull()->autoIncrement()->primaryKey()
                ->varchar('name', 25)->notNull();
        })->execute();

        $application = (new Kernel())->getApplication();
        $application->add((new Container())->injectDependencies(new CapsuleCommand()));
        $application->add((new Container())->injectDependencies(new DBCapsuleCommand()));
        $application->add((new Container())->injectDependencies(new DBCapsulesCommand()));
        $this->commandTester = new CommandTester($application->find('db:mysql:capsules'));
    }

    protected function tearDown(): void
    {
        Schema::dropTable(self::TEST_ENTITY);
        Schema::dropTable(self::USERS_ENTITY);
        $this->rmdirRecursively('./database/');
    }

    public function testExecute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertInstanceOf(self::OBJECT_NAME_TEST, new (self::OBJECT_NAME_TEST)());
        $this->assertInstanceOf(self::OBJECT_NAME_USERS, new (self::OBJECT_NAME_USERS)());
    }
}
