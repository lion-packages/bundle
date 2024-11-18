<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\DB;

use Lion\Bundle\Commands\Lion\DB\CrudCommand;
use Lion\Bundle\Commands\Lion\DB\DBCapsuleCommand;
use Lion\Bundle\Commands\Lion\DB\RulesDBCommand;
use Lion\Bundle\Commands\Lion\New\CapsuleCommand;
use Lion\Bundle\Commands\Lion\New\ControllerCommand;
use Lion\Bundle\Commands\Lion\New\ModelCommand;
use Lion\Bundle\Commands\Lion\New\RulesCommand;
use Lion\Bundle\Commands\Lion\New\TestCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class CrudCommandTest extends Test
{
    use ConnectionProviderTrait;

    private const string ENTITY = 'users';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->createTables();

        $kernel = new Kernel();

        $container = new Container();

        $kernel->commandsOnObjects([
            $container->resolve(TestCommand::class),
            $container->resolve(ModelCommand::class),
            $container->resolve(ControllerCommand::class),
            $container->resolve(RulesCommand::class),
            $container->resolve(CapsuleCommand::class),
            $container->resolve(RulesDBCommand::class),
            $container->resolve(DBCapsuleCommand::class),
            $container->resolve(CrudCommand::class),
        ]);

        $this->commandTester = new CommandTester($kernel->getApplication()->find('db:crud'));
    }

    protected function tearDown(): void
    {
        $this->dropTables();
    }

    private function createTables(): void
    {
        Schema::connection(env('DB_NAME'))
            ->createTable(self::ENTITY, function (): void {
                Schema::int('id')->notNull()->autoIncrement()->primaryKey();
                Schema::varchar('name', 25)->notNull();
                Schema::varchar('last_name', 25)->notNull();
            })
            ->execute();

        PostgreSQL::connection(env('DB_NAME_TEST_POSTGRESQL'))
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
        Schema::connection(env('DB_NAME'))
            ->dropTable(self::ENTITY)
            ->execute();

        PostgreSQL::connection(env('DB_NAME_TEST_POSTGRESQL'))
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS public.users CASCADE;
                SQL
            )
            ->execute();
    }

    #[Testing]
    public function execute(): void
    {
        $execute = $this->commandTester->setInputs(['0'])->execute(['entity' => self::ENTITY]);

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
        $execute = $this->commandTester->setInputs(['2'])->execute(['entity' => self::ENTITY]);

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
        $this->assertStringContainsString('App\\Http\\Controllers\\LionDatabase\\PostgreSQL\\UsersController', $display);
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
