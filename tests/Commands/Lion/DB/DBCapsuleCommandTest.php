<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\DB\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\InterfaceCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DBCapsuleCommandTest extends Test
{
    private const string NAMESPACE_CLASS = 'Database\\Class\\LionDatabase\\MySQL\\';
    private const string ENTITY = 'test';
    private const string CLASS_NAME = 'Test';
    private const string OBJECT_NAME = self::NAMESPACE_CLASS . self::CLASS_NAME;
    private const string OUTPUT_MESSAGE = 'The capsule class has been generated successfully';
    private const string OUTPUT_MESSAGE_ERROR =
        "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'lion_database.test' doesn't exist";

    private CommandTester $commandTester;
    private DBCapsuleCommand $dbCapsuleCommand;

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws DependencyException
     */
    protected function setUp(): void
    {
        $container = new Container();

        /** @var InterfaceCommand $interfaceCommand */
        $interfaceCommand = $container->resolve(InterfaceCommand::class);

        /** @var CapsuleCommand $capsuleCommand */
        $capsuleCommand = $container->resolve(CapsuleCommand::class);

        /** @var DBCapsuleCommand $dbCapsuleCommand */
        $dbCapsuleCommand = $container->resolve(DBCapsuleCommand::class);

        $this->dbCapsuleCommand = $dbCapsuleCommand;

        $application = new Application();

        $application->add($interfaceCommand);

        $application->add($capsuleCommand);

        $application->add($this->dbCapsuleCommand);

        $this->commandTester = new CommandTester($application->find('db:capsule'));

        $this->initReflection($this->dbCapsuleCommand);
    }

    protected function tearDown(): void
    {
        Schema::connection(getDefaultConnection())
            ->dropTable(self::ENTITY)
            ->execute();

        $this->rmdirRecursively('./app/');

        $this->rmdirRecursively('./database/');
    }

    private function createTables(): void
    {
        Schema::connection(getDefaultConnection())
            ->createTable(self::ENTITY, function () {
                Schema::int('id', 11)->notNull()->autoIncrement()->primaryKey();
                Schema::varchar('name', 25)->notNull();
            })
            ->execute();
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(DBCapsuleCommand::class, $this->dbCapsuleCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(
            DBCapsuleCommand::class,
            $this->dbCapsuleCommand->setDatabaseEngine(new DatabaseEngine())
        );

        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    #[Testing]
    public function execute(): void
    {
        $this->createTables();

        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => self::ENTITY,
            ]);

        $this->assertSame(Command::SUCCESS, $execute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(self::OBJECT_NAME, new (self::OBJECT_NAME));

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }

    #[Testing]
    public function executeWithoutColumns(): void
    {
        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => self::ENTITY,
            ]);

        $this->assertSame(Command::FAILURE, $execute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE_ERROR, $this->commandTester->getDisplay());
    }
}
