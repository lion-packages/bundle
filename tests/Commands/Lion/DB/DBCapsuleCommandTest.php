<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\DB\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\InterfaceCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\Commands\Selection\MenuCommand;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Dependency\Injection\Container;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use stdClass;
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

        $application->addCommand($interfaceCommand);

        $application->addCommand($capsuleCommand);

        $application->addCommand($this->dbCapsuleCommand);

        $this->commandTester = new CommandTester($application->find('db:capsule'));

        $this->initReflection($this->dbCapsuleCommand);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./app/');

        $this->rmdirRecursively('./database/');
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
        Schema::connection(getDefaultConnection())
            ->createTable(self::ENTITY, function (): void {
                Schema::int('id')
                    ->notNull()
                    ->autoIncrement()
                    ->primaryKey();

                Schema::varchar('name', 25)
                    ->notNull();
            })
            ->execute();

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

        Schema::connection(getDefaultConnection())
            ->dropTable(self::ENTITY)
            ->execute();
    }

    #[Testing]
    public function executeWithForeignKeys(): void
    {
        Schema::connection(getDefaultConnection())
            ->createTable('roles', function (): void {
                Schema::int('idroles')
                    ->notNull()
                    ->autoIncrement()
                    ->primaryKey();

                Schema::varchar('roles_name', 25)
                    ->notNull();
            })
            ->execute();

        Schema::connection(getDefaultConnection())
            ->createTable('users', function (): void {
                Schema::int('idusers')
                    ->notNull()
                    ->autoIncrement()
                    ->primaryKey();

                Schema::int('idroles')
                    ->notNull()
                    ->foreign('roles', 'idroles');

                Schema::varchar('users_name', 25)
                    ->notNull();
            })
            ->execute();

        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => 'roles',
            ]);

        $this->assertSame(Command::SUCCESS, $execute);

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString(<<<EOT
        INTERFACE: App\Interfaces\Database\Class\LionDatabase\MySQL\Roles\IdrolesInterface
        EOT, $display);

        $this->assertStringContainsString(<<<EOT
        INTERFACE: App\Interfaces\Database\Class\LionDatabase\MySQL\Roles\RolesNameInterface
        EOT, $display);

        $this->assertStringContainsString(<<<EOT
        CAPSULE: Database\Class\LionDatabase\MySQL\Roles
        EOT, $display);

        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => 'users',
            ]);

        $this->assertSame(Command::SUCCESS, $execute);

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString(<<<EOT
        >>  INTERFACE: App\Interfaces\Database\Class\LionDatabase\MySQL\Users\IdusersInterface
        EOT, $display);

        $this->assertStringContainsString(<<<EOT
        >>  INTERFACE: App\Interfaces\Database\Class\LionDatabase\MySQL\Users\UsersNameInterface
        EOT, $display);

        $this->assertStringContainsString(<<<EOT
        >>  CAPSULE: Database\Class\LionDatabase\MySQL\Users
        EOT, $display);

        Schema::connection(getDefaultConnection())
            ->dropTable('users')
            ->execute();

        Schema::connection(getDefaultConnection())
            ->dropTable('roles')
            ->execute();
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

    #[Testing]
    public function executeWithForeignsReturningErrorObject(): void
    {
        Schema::connection(getDefaultConnection())
            ->createTable('roles', function (): void {
                Schema::int('idroles')
                    ->notNull()
                    ->autoIncrement()
                    ->primaryKey();

                Schema::varchar('roles_name', 25)
                    ->notNull();
            })
            ->execute();

        Schema::connection(getDefaultConnection())
            ->createTable('users', function (): void {
                Schema::int('idusers')
                    ->notNull()
                    ->autoIncrement()
                    ->primaryKey();

                Schema::int('idroles')
                    ->notNull()
                    ->foreign('roles', 'idroles');

                Schema::varchar('users_name', 25)
                    ->notNull();
            })
            ->execute();

        $capsuleCommand = new class () extends DBCapsuleCommand {
            /**
             * Get the foreign keys of a table.
             *
             * @param string $driver Database engine.
             * @param string $connectionName Database connection.
             * @param string $databaseName Database name.
             * @param string $entity Entity name.
             *
             * @return array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|stdClass
             *
             * @phpstan-ignore-next-line
             */
            protected function getTableForeigns(
                string $driver,
                string $connectionName,
                string $databaseName,
                string $entity
            ): array|stdClass {
                $class = new stdClass();

                $class->message = 'ERROR MESSAGE';

                return $class;
            }
        };

        $capsuleCommand
            ->setStr(new Str())
            ->setArr(new Arr());

        $capsuleCommand
            ->setDatabaseEngine(new DatabaseEngine());

        $application = new Application();

        $application->addCommand($capsuleCommand);

        $tester = new CommandTester($application->find('db:capsule'));

        $exitCode = $tester
            ->setInputs(['0'])
            ->execute(['entity' => 'users']);

        $this->assertEquals(Command::FAILURE, $exitCode);

        $this->assertStringContainsString('>>  CAPSULE: ERROR MESSAGE', $tester->getDisplay());

        Schema::connection(getDefaultConnection())
            ->dropTable('users')
            ->execute();

        Schema::connection(getDefaultConnection())
            ->dropTable('roles')
            ->execute();
    }
}
