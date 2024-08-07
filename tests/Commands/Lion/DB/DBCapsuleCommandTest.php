<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Command\Command;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class DBCapsuleCommandTest extends Test
{
    use ConnectionProviderTrait;

    private const string URL_PATH = './database/Class/LionDatabase/MySQL/';
    private const string NAMESPACE_CLASS = 'Database\\Class\\LionDatabase\\MySQL\\';
    private const string ENTITY = 'test';
    private const string CLASS_NAME = 'Test';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string OUTPUT_MESSAGE = 'capsule has been generated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->createDirectory(self::URL_PATH);

        $this->createTables();

        $application = new Application();

        $application->add((new Container())->injectDependencies(new CapsuleCommand()));

        $application->add((new Container())->injectDependencies(new DBCapsuleCommand()));

        $this->commandTester = new CommandTester($application->find('db:capsule'));
    }

    protected function tearDown(): void
    {
        Schema::dropTable(self::ENTITY)->execute();

        $this->rmdirRecursively('./database/');
    }

    private function createTables(): void
    {
        Schema::connection(env('DB_NAME'))
            ->createTable(self::ENTITY, function () {
                Schema::int('id', 11)->notNull()->autoIncrement()->primaryKey();
                Schema::varchar('name', 25)->notNull();
            })
            ->execute();
    }

    #[Testing]
    public function execute(): void
    {
        $execute = $this->commandTester
            ->setInputs(['0'])
            ->execute(['entity' => self::ENTITY]);

        $this->assertSame(Command::SUCCESS, $execute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertInstanceOf(self::OBJECT_NAME, new (self::OBJECT_NAME));

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }
}
