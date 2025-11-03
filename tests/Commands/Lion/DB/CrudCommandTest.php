<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\DB\CrudCommand;
use Lion\Bundle\Commands\Lion\DB\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\DB\RulesDBCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\ControllerCommand;
use Lion\Bundle\Commands\Lion\New\InterfaceCommand;
use Lion\Bundle\Commands\Lion\New\ModelCommand;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Commands\Lion\New\TestCommand;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Command\Kernel;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CrudCommandTest extends Test
{
    private const string ENTITY = 'users';

    private CommandTester $commandTester;
    private CrudCommand $crudCommand;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        $this->createTables();

        $container = new Container();

        /** @var InterfaceCommand $interfaceCommand */
        $interfaceCommand = $container->resolve(InterfaceCommand::class);

        /** @var TestCommand $testsCommand */
        $testsCommand = $container->resolve(TestCommand::class);

        /** @var ModelCommand $modelCommand */
        $modelCommand = $container->resolve(ModelCommand::class);

        /** @var ControllerCommand $controllerCommand */
        $controllerCommand = $container->resolve(ControllerCommand::class);

        /** @var RulesCommand $rulesCommand */
        $rulesCommand = $container->resolve(RulesCommand::class);

        /** @var CapsuleCommand $capsuleCommand */
        $capsuleCommand = $container->resolve(CapsuleCommand::class);

        /** @var RulesDBCommand $rulesDbCommand */
        $rulesDbCommand = $container->resolve(RulesDBCommand::class);

        /** @var DBCapsuleCommand $dbCapsuleCommand */
        $dbCapsuleCommand = $container->resolve(DBCapsuleCommand::class);

        /** @var CrudCommand $crudCommand */
        $crudCommand = $container->resolve(CrudCommand::class);

        $this->crudCommand = $crudCommand;

        $kernel = new Kernel();

        $kernel->commandsOnObjects([
            $interfaceCommand,
            $testsCommand,
            $modelCommand,
            $controllerCommand,
            $rulesCommand,
            $capsuleCommand,
            $rulesDbCommand,
            $dbCapsuleCommand,
            $this->crudCommand,
        ]);

        $this->commandTester = new CommandTester($kernel->getApplication()->find('db:crud'));

        $this->initReflection($this->crudCommand);
    }

    protected function tearDown(): void
    {
        $this->dropTables();
    }

    private function createTables(): void
    {
        /** @var string $dbName */
        $dbName = env('DB_DEFAULT');

        /** @var string $dbNameTestPostgresql */
        $dbNameTestPostgresql = env('DB_NAME_TEST_POSTGRESQL');

        Schema::connection($dbName)
            ->createTable(self::ENTITY, function (): void {
                Schema::int('id')->notNull()->autoIncrement()->primaryKey();
                Schema::varchar('name', 25)->notNull();
                Schema::varchar('last_name', 25)->notNull();
            })
            ->execute();

        PostgreSQL::connection($dbNameTestPostgresql)
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS public.users CASCADE;
                SQL
            )
            ->query(
                <<<SQL
                CREATE TABLE public.users (
                    id serial4 NOT NULL,
                    name varchar(255) NOT NULL,
                    last_name varchar(255) NOT NULL,
                    CONSTRAINT users_pkey PRIMARY KEY (id)
                );
                SQL
            )
            ->execute();
    }

    private function dropTables(): void
    {
        /** @var string $dbName */
        $dbName = env('DB_DEFAULT');

        /** @var string $dbNameTestPostgresql */
        $dbNameTestPostgresql = env('DB_NAME_TEST_POSTGRESQL');

        Schema::connection($dbName)
            ->dropTable(self::ENTITY)
            ->execute();

        PostgreSQL::connection($dbNameTestPostgresql)
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS public.users CASCADE;
                SQL
            )
            ->execute();
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setFileWriter(): void
    {
        $this->assertInstanceOf(CrudCommand::class, $this->crudCommand->setFileWriter(new FileWriter()));
        $this->assertInstanceOf(FileWriter::class, $this->getPrivateProperty('fileWriter'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(CrudCommand::class, $this->crudCommand->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(CrudCommand::class, $this->crudCommand->setDatabaseEngine(new DatabaseEngine()));
        $this->assertInstanceOf(DatabaseEngine::class, $this->getPrivateProperty('databaseEngine'));
    }

    #[Testing]
    public function execute(): void
    {
        $execute = $this->commandTester
            ->setInputs([
                '0',
            ])
            ->execute([
                'entity' => self::ENTITY,
            ]);

        $this->assertSame(Command::SUCCESS, $execute);

        $this->assertFileExists('app/Rules/LionDatabase/MySQL/Users/IdRule.php');
        $this->assertFileExists('app/Rules/LionDatabase/MySQL/Users/NameRule.php');
        $this->assertFileExists('app/Rules/LionDatabase/MySQL/Users/LastNameRule.php');
        $this->assertFileExists('app/Http/Controllers/LionDatabase/MySQL/UsersController.php');
        $this->assertFileExists('app/Models/LionDatabase/MySQL/UsersModel.php');
        $this->assertFileExists('tests/App/Models/LionDatabase/MySQL/UsersModelTest.php');
        $this->assertFileExists('tests/App/Http/Controllers/LionDatabase/MySQL/UsersControllerTest.php');
        $this->assertFileExists('database/Class/LionDatabase/MySQL/Users.php');
        $this->assertFileExists('tests/Database/Class/LionDatabase/MySQL/UsersTest.php');

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString('App\\Rules\\LionDatabase\\MySQL\\Users\\IdRule', $display);
        $this->assertStringContainsString('App\\Rules\\LionDatabase\\MySQL\\Users\\NameRule', $display);
        $this->assertStringContainsString('App\\Rules\\LionDatabase\\MySQL\\Users\\LastNameRule', $display);
        $this->assertStringContainsString('App\\Http\\Controllers\\LionDatabase\\MySQL\\UsersController', $display);
        $this->assertStringContainsString('App\\Models\\LionDatabase\\MySQL\\UsersModel', $display);
        $this->assertStringContainsString('Tests\\App\\Models\\LionDatabase\\MySQL\\UsersModelTest', $display);

        $this->assertStringContainsString(
            'Tests\\App\\Http\\Controllers\\LionDatabase\\MySQL\\UsersControllerTest',
            $display
        );

        $this->assertStringContainsString('Database\\Class\\LionDatabase\\MySQL\\Users', $display);
        $this->assertStringContainsString('Tests\\Database\\Class\\LionDatabase\\MySQL\\UsersTest', $display);

        $this->rmdirRecursively('./app/');

        $this->rmdirRecursively('./database/');

        $this->rmdirRecursively('./tests/App/');

        $this->rmdirRecursively('./tests/Database/');

        $this->assertDirectoryDoesNotExist('./app/');
        $this->assertDirectoryDoesNotExist('./database/');
        $this->assertDirectoryDoesNotExist('./tests/App/');
        $this->assertDirectoryDoesNotExist('./tests/Database/');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }

    #[Testing]
    public function executeWithPostgreSQL(): void
    {
        $execute = $this->commandTester
            ->setInputs([
                '2',
            ])
            ->execute([
                'entity' => self::ENTITY,
            ]);

        $this->assertSame(Command::SUCCESS, $execute);

        $this->assertFileExists('app/Rules/LionDatabase/PostgreSQL/Users/IdRule.php');
        $this->assertFileExists('app/Rules/LionDatabase/PostgreSQL/Users/NameRule.php');
        $this->assertFileExists('app/Rules/LionDatabase/PostgreSQL/Users/LastNameRule.php');
        $this->assertFileExists('app/Http/Controllers/LionDatabase/PostgreSQL/UsersController.php');
        $this->assertFileExists('app/Models/LionDatabase/PostgreSQL/UsersModel.php');
        $this->assertFileExists('tests/App/Models/LionDatabase/PostgreSQL/UsersModelTest.php');
        $this->assertFileExists('tests/App/Http/Controllers/LionDatabase/PostgreSQL/UsersControllerTest.php');
        $this->assertFileExists('database/Class/LionDatabase/PostgreSQL/Users.php');
        $this->assertFileExists('tests/Database/Class/LionDatabase/PostgreSQL/UsersTest.php');

        $display = $this->commandTester->getDisplay();

        $this->assertStringContainsString('App\\Rules\\LionDatabase\\PostgreSQL\\Users\\IdRule', $display);
        $this->assertStringContainsString('App\\Rules\\LionDatabase\\PostgreSQL\\Users\\NameRule', $display);
        $this->assertStringContainsString('App\\Rules\\LionDatabase\\PostgreSQL\\Users\\LastNameRule', $display);

        $this->assertStringContainsString(
            'App\\Http\\Controllers\\LionDatabase\\PostgreSQL\\UsersController',
            $display
        );

        $this->assertStringContainsString('App\\Models\\LionDatabase\\PostgreSQL\\UsersModel', $display);
        $this->assertStringContainsString('Tests\\App\\Models\\LionDatabase\\PostgreSQL\\UsersModelTest', $display);

        $this->assertStringContainsString(
            'Tests\\App\\Http\\Controllers\\LionDatabase\\PostgreSQL\\UsersControllerTest',
            $display
        );

        $this->assertStringContainsString('Database\\Class\\LionDatabase\\PostgreSQL\\Users', $display);
        $this->assertStringContainsString('Tests\\Database\\Class\\LionDatabase\\PostgreSQL\\UsersTest', $display);

        $this->rmdirRecursively('./app/');

        $this->rmdirRecursively('./database/');

        $this->rmdirRecursively('./tests/App/');

        $this->rmdirRecursively('./tests/Database/');

        $this->assertDirectoryDoesNotExist('./app/');
        $this->assertDirectoryDoesNotExist('./database/');
        $this->assertDirectoryDoesNotExist('./tests/App/');
        $this->assertDirectoryDoesNotExist('./tests/Database/');

        unset($_ENV['SELECTED_CONNECTION']);

        $this->assertArrayNotHasKey('SELECTED_CONNECTION', $_ENV);
    }
}
