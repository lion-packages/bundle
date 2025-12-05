<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lion\Bundle\Commands\Lion\New\MigrationCommand;
use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Bundle\Interface\Migrations\SchemaInterface;
use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
use Lion\Bundle\Interface\Migrations\TableInterface;
use Lion\Bundle\Interface\Migrations\ViewInterface;
use Lion\Bundle\Interface\MigrationUpInterface;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Interface\ExecuteInterface;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Str;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use RuntimeException;
use stdClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class MigrationsTest extends Test
{
    private const string MIGRATION_NAME = 'test-migration';
    private const string CLASS_NAME = 'TestMigration';
    private const string CLASS_NAMESPACE_SCHEMA = 'Database\\Migrations\\LionDatabase\\MySQL\\Schemas\\';
    private const string CLASS_NAMESPACE_TABLE = 'Database\\Migrations\\LionDatabase\\MySQL\\Tables\\';
    private const string CLASS_NAMESPACE_VIEW = 'Database\\Migrations\\LionDatabase\\MySQL\\Views\\';
    private const string CLASS_NAMESPACE_STORE_PROCEDURE =
        'Database\\Migrations\\LionDatabase\\MySQL\\StoredProcedures\\';
    private const string URL_PATH_MYSQL_SCHEMA = './database/Migrations/LionDatabase/MySQL/Schemas/';
    private const string URL_PATH_MYSQL_TABLE = './database/Migrations/LionDatabase/MySQL/Tables/';
    private const string URL_PATH_MYSQL_VIEW = './database/Migrations/LionDatabase/MySQL/Views/';
    private const string URL_PATH_MYSQL_STORED_PROCEDURE = './database/Migrations/LionDatabase/MySQL/StoredProcedures/';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'The migration was generated successfully.';

    private CommandTester $commandTester;
    private Migrations $migrations;
    private Store $store;

    /**
     * @throws NotFoundException Error while resolving the entry.
     * @throws DependencyException No entry found for the given name.
     */
    protected function setUp(): void
    {
        $this->store = new Store();

        $container = new Container();

        /** @var Migrations $migrations */
        $migrations = $container->resolve(Migrations::class);

        $this->migrations = $migrations;

        /** @var MigrationCommand $migrationCommand */
        $migrationCommand = $container->resolve(MigrationCommand::class);

        $application = new Application();

        $application->addCommand($migrationCommand);

        $this->commandTester = new CommandTester($application->find('new:migration'));

        $this->initReflection($this->migrations);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('database/');
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(Migrations::class, $this->migrations->setStore($this->store));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    #[Testing]
    public function orderList(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::TABLE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => env('DB_DEFAULT'),
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $tableMigration = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertIsObject($tableMigration);

        $this->assertInstances($tableMigration, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $tableNamespace = $this->store->getNamespaceFromFile(
            (self::URL_PATH_MYSQL_TABLE . self::FILE_NAME),
            'Database\\Migrations\\',
            'Migrations/'
        );

        /** @var array<string, MigrationUpInterface> $migrations */
        $migrations = [
            $tableNamespace => $tableMigration,
        ];

        $list = $this->migrations->orderList($migrations);

        $this->assertNotEmpty($list);
        $this->assertArrayHasKey($tableNamespace, $list);
        $this->assertSame($migrations, $list);
        $this->assertInstanceOf(TableInterface::class, $list[$tableNamespace]);
    }

    #[Testing]
    public function getMigrations(): void
    {
        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::SCHEMA,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => env('DB_DEFAULT'),
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_SCHEMA . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $tableMigration = new (self::CLASS_NAMESPACE_SCHEMA . self::CLASS_NAME)();

        $this->assertIsObject($tableMigration);

        $this->assertInstances($tableMigration, [
            MigrationUpInterface::class,
            SchemaInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::TABLE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => env('DB_DEFAULT'),
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $tableMigration = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertIsObject($tableMigration);

        $this->assertInstances($tableMigration, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::VIEW,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => env('DB_DEFAULT'),
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_VIEW . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $viewMigration = new (self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME)();

        $this->assertIsObject($viewMigration);

        $this->assertInstances($viewMigration, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::STORED_PROCEDURE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => env('DB_DEFAULT'),
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_STORED_PROCEDURE . self::FILE_NAME);

        /** @phpstan-ignore-next-line */
        $viewMigration = new (self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME)();

        $this->assertIsObject($viewMigration);

        $this->assertInstances($viewMigration, [
            MigrationUpInterface::class,
            StoredProcedureInterface::class,
        ]);

        $connections = Connection::getConnections();

        /** @phpstan-ignore-next-line */
        $connection = $connections[env('DB_DEFAULT')];

        /** @var string $dbNamePascal */
        $dbNamePascal = new Str()
            ->of($connection[Connection::CONNECTION_DBNAME])
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->pascal()
            ->get();

        $dbType = new DatabaseEngine()->getDriver($connection[Connection::CONNECTION_TYPE]);

        $list = $this->migrations->getMigrations("{$dbNamePascal}/{$dbType}/");

        $this->assertNotEmpty($list);
        $this->assertArrayHasKey(TableInterface::class, $list);
        $this->assertArrayHasKey(ViewInterface::class, $list);
        $this->assertArrayHasKey(StoredProcedureInterface::class, $list);
        $this->assertNotEmpty($list[TableInterface::class]);
        $this->assertNotEmpty($list[ViewInterface::class]);
        $this->assertNotEmpty($list[StoredProcedureInterface::class]);

        $this->assertInstanceOf(
            SchemaInterface::class,
            $list[SchemaInterface::class][self::CLASS_NAMESPACE_SCHEMA . self::CLASS_NAME]
        );

        $this->assertInstanceOf(
            TableInterface::class,
            $list[TableInterface::class][self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME]
        );

        $this->assertInstanceOf(
            ViewInterface::class,
            $list[ViewInterface::class][self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME]
        );

        $this->assertInstanceOf(
            StoredProcedureInterface::class,
            $list[StoredProcedureInterface::class][self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME]
        );
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function executeMigrationsGroup(): void
    {
        $connectionName = 'local';

        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::TABLE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => $connectionName,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_TABLE . self::FILE_NAME);

        /**
         * @var object $objClass
         *
         * @phpstan-ignore-next-line
         */
        $objClass = new (self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            TableInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::VIEW,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => $connectionName,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_VIEW . self::FILE_NAME);

        /**
         * @var object $objClass
         *
         * @phpstan-ignore-next-line
         */
        $objClass = new (self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME)();

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            ViewInterface::class,
        ]);

        $commandExecute = $this->commandTester
            ->setInputs([
                MigrationFactory::STORED_PROCEDURE,
            ])
            ->execute([
                'migration' => self::MIGRATION_NAME,
                '--connection' => $connectionName,
            ]);

        $this->assertSame(Command::SUCCESS, $commandExecute);
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH_MYSQL_STORED_PROCEDURE . self::FILE_NAME);

        $objClass = new (self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME)();

        $this->assertIsObject($objClass);

        $this->assertInstances($objClass, [
            MigrationUpInterface::class,
            StoredProcedureInterface::class,
        ]);

        /** @phpstan-ignore-next-line */
        $this->migrations->executeMigrationsGroup([
            self::CLASS_NAMESPACE_TABLE . self::CLASS_NAME,
            self::CLASS_NAMESPACE_VIEW . self::CLASS_NAME,
            self::CLASS_NAMESPACE_STORE_PROCEDURE . self::CLASS_NAME,
        ]);
    }

    #[Testing]
    #[TestWith(['connectionName' => 'not-exists-1'], 'case-0')]
    #[TestWith(['connectionName' => 'not-exists-2'], 'case-1')]
    #[TestWith(['connectionName' => 'not-exists-3'], 'case-2')]
    #[TestWith(['connectionName' => 'not-exists-4'], 'case-3')]
    public function processingWithStaticConnectionsConnectionDoesNotExists(string $connectionName): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage("The connection '{$connectionName}' does not exist.");

        $this->migrations->processingWithStaticConnections($connectionName, fn (): bool => true);
    }

    #[Testing]
    public function processingWithStaticConnections(): void
    {
        /** @var string $defaultConnection */
        $defaultConnection = env('DB_DEFAULT');

        $this->migrations
            ->processingWithStaticConnections($defaultConnection, function () use ($defaultConnection): void {
                $connections = Connection::getConnections();

                $numberOfConnections = count($connections);

                $this->assertSame(NUMBER_OF_ACTIVE_CONNECTIONS, $numberOfConnections);
                $this->assertArrayNotHasKey(Connection::CONNECTION_DBNAME, $connections[$defaultConnection]);
            });

        $connections = Connection::getConnections();

        $numberOfConnections = count($connections);

        $this->assertSame(NUMBER_OF_ACTIVE_CONNECTIONS, $numberOfConnections);

        /** @var string $dbName */
        $dbName = env('DB_NAME');

        $this->assertSame($dbName, $connections[$defaultConnection][Connection::CONNECTION_DBNAME]);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function resetDatabaseForSQLite(): void
    {
        /** @var string $dbName */
        $dbName = env('DB_NAME_TEST_SQLITE');

        $this->migrations->processingWithStaticConnections('lion_database_sqlite', function () use ($dbName): void {
            $this->migrations
                ->resetDatabase($dbName, 'lion_database_sqlite', Driver::SQLITE, function () use ($dbName): void {
                    $this->assertFileDoesNotExist($dbName);
                });
        });

        $this->assertFileExists($dbName);

        new Store()->remove($dbName);

        $this->assertFileDoesNotExist($dbName);

        Connection::clearConnectionList();
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function resetDatabaseForMySQL(): void
    {
        /** @var string $connectionName */
        $connectionName = env('DB_DEFAULT');

        /** @var string $dbName */
        $dbName = env('DB_NAME');

        $existDatabase = MySQL::connection($connectionName)
            ->query(
                <<<SQL
                SELECT COUNT(*) AS cont FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?;
                SQL
            )
            ->addRows([
                $dbName,
            ])
            ->get();

        $this->assertIsObject($existDatabase);
        $this->assertInstanceOf(stdClass::class, $existDatabase);
        $this->assertObjectHasProperty('cont', $existDatabase);
        $this->assertIsInt($existDatabase->cont);
        $this->assertSame(1, $existDatabase->cont);

        $this->migrations->processingWithStaticConnections(
            connectionName: $connectionName,
            callback: function () use ($connectionName, $dbName): void {
                $this->migrations->resetDatabase(
                    $dbName,
                    $connectionName,
                    Driver::MYSQL,
                    function () use ($connectionName, $dbName): void {
                        $existDatabase = MySQL::connection($connectionName)
                            ->query(
                                <<<SQL
                                SELECT COUNT(*) AS cont FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?;
                                SQL
                            )
                            ->addRows([
                                $dbName,
                            ])
                            ->get();

                        $this->assertIsObject($existDatabase);
                        $this->assertInstanceOf(stdClass::class, $existDatabase);
                        $this->assertObjectHasProperty('cont', $existDatabase);
                        $this->assertIsInt($existDatabase->cont);
                        $this->assertSame(0, $existDatabase->cont);
                    }
                );
            }
        );

        $existDatabase = MySQL::connection($connectionName)
            ->query(
                <<<SQL
                SELECT COUNT(*) AS cont FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?;
                SQL
            )
            ->addRows([
                $dbName,
            ])
            ->get();

        $this->assertIsObject($existDatabase);
        $this->assertInstanceOf(stdClass::class, $existDatabase);
        $this->assertObjectHasProperty('cont', $existDatabase);
        $this->assertIsInt($existDatabase->cont);
        $this->assertSame(1, $existDatabase->cont);

        Connection::clearConnectionList();
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function resetDatabaseForPostgreSQL(): void
    {
        /** @var string $connectionName */
        $connectionName = env('DB_NAME_TEST_POSTGRESQL');

        /** @var string $dbName */
        $dbName = env('DB_NAME');

        $existDatabase = PostgreSQL::connection($connectionName)
            ->query(
                <<<SQL
                SELECT COUNT(*) as cont FROM pg_database WHERE datname = ?;
                SQL
            )
            ->addRows([
                $dbName,
            ])
            ->get();

        $this->assertIsObject($existDatabase);
        $this->assertInstanceOf(stdClass::class, $existDatabase);
        $this->assertObjectHasProperty('cont', $existDatabase);
        $this->assertIsInt($existDatabase->cont);
        $this->assertSame(1, $existDatabase->cont);

        $this->migrations->processingWithStaticConnections(
            connectionName: $connectionName,
            callback: function () use ($connectionName, $dbName): void {
                $this->migrations->resetDatabase(
                    $dbName,
                    $connectionName,
                    Driver::POSTGRESQL,
                    function () use ($connectionName, $dbName): void {
                        $existDatabase = PostgreSQL::connection($connectionName)
                            ->query(
                                <<<SQL
                                SELECT COUNT(*) as cont FROM pg_database WHERE datname = ?;
                                SQL
                            )
                            ->addRows([
                                $dbName,
                            ])
                            ->get();

                        $this->assertIsObject($existDatabase);
                        $this->assertInstanceOf(stdClass::class, $existDatabase);
                        $this->assertObjectHasProperty('cont', $existDatabase);
                        $this->assertIsInt($existDatabase->cont);
                        $this->assertSame(0, $existDatabase->cont);
                    }
                );
            }
        );

        $this->migrations->processingWithStaticConnections(
            connectionName: $connectionName,
            callback: function () use ($connectionName, $dbName): void {
                $existDatabase = PostgreSQL::connection($connectionName)
                    ->query(
                        <<<SQL
                        SELECT COUNT(*) as cont FROM pg_database WHERE datname = ?;
                        SQL
                    )
                    ->addRows([
                        $dbName,
                    ])
                    ->get();

                $this->assertIsObject($existDatabase);
                $this->assertInstanceOf(stdClass::class, $existDatabase);
                $this->assertObjectHasProperty('cont', $existDatabase);
                $this->assertIsInt($existDatabase->cont);
                $this->assertSame(1, $existDatabase->cont);
            }
        );

        Connection::clearConnectionList();
    }

    #[Testing]
    public function truncateTableIsNull(): void
    {
        $this->assertNull($this->migrations->truncateTable('test', 'test-connection', 'test_table'));
    }

    #[Testing]
    public function truncateTableForMySQL(): void
    {
        /** @var string $connectionName */
        $connectionName = env('DB_DEFAULT');

        MySQL::connection($connectionName)
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS roles;
                SQL
            )
            ->query(
                <<<SQL
                CREATE TABLE roles (
                    id INT NOT NULL AUTO_INCREMENT,
                    roles_name VARCHAR(30) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE = INNODB DEFAULT CHARACTER SET = UTF8MB4 COLLATE = UTF8MB4_SPANISH_CI;
                SQL
            )
            ->query(
                <<<SQL
                INSERT INTO roles (roles_name) VALUES ('ROLE_USER');
                SQL
            )
            ->execute();

        $roles = MySQL::connection($connectionName)
            ->table('roles')
            ->select()
            ->getAll();

        $this->assertIsArray($roles);
        $this->assertNotEmpty($roles);

        $rol = reset($roles);

        $this->assertIsObject($rol);
        $this->assertInstanceOf(stdClass::class, $rol);
        $this->assertObjectHasProperty('id', $rol);
        $this->assertObjectHasProperty('roles_name', $rol);

        /** @var ExecuteInterface $execute */
        $execute = $this->migrations->truncateTable(Driver::MYSQL, $connectionName, 'roles');

        $executeResponse = $execute->execute();

        $this->assertIsObject($executeResponse);
        $this->assertInstanceOf(stdClass::class, $executeResponse);
        $this->assertObjectHasProperty('status', $executeResponse);
        $this->assertSame(Status::SUCCESS, $executeResponse->status);

        $roles = MySQL::connection($connectionName)
            ->table('roles')
            ->select()
            ->getAll();

        $this->assertIsArray($roles);
        $this->assertEmpty($roles);

        MySQL::connection($connectionName)
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS roles;
                SQL
            )
            ->execute();
    }

    #[Testing]
    public function truncateTableForPostgreSQL(): void
    {
        /** @phpstan-ignore-next-line */
        PostgreSQL::connection(env('DB_NAME_TEST_POSTGRESQL'))
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS public.roles;
                SQL
            )
            ->query(
                <<<SQL
                CREATE TABLE public.roles (
                    id SERIAL PRIMARY KEY,
                    roles_name VARCHAR(30) NOT NULL
                );
                SQL
            )
            ->query(
                <<<SQL
                INSERT INTO roles (roles_name) VALUES ('ROLE_USER');
                SQL
            )
            ->execute();

        /** @phpstan-ignore-next-line */
        $roles = PostgreSQL::connection(env('DB_NAME_TEST_POSTGRESQL'))
            ->table('roles', false)
            ->select()
            ->getAll();

        $this->assertIsArray($roles);
        $this->assertNotEmpty($roles);

        $rol = reset($roles);

        $this->assertIsObject($rol);
        $this->assertInstanceOf(stdClass::class, $rol);

        /**
         * @var ExecuteInterface $execute
         * @phpstan-ignore-next-line
         */
        $execute = $this->migrations->truncateTable(Driver::POSTGRESQL, env('DB_NAME_TEST_POSTGRESQL'), 'roles');

        $executeResponse = $execute->execute();

        $this->assertIsObject($executeResponse);
        $this->assertInstanceOf(stdClass::class, $executeResponse);
        $this->assertObjectHasProperty('status', $executeResponse);
        $this->assertSame(Status::SUCCESS, $executeResponse->status);

        /** @phpstan-ignore-next-line */
        $roles = PostgreSQL::connection(env('DB_NAME_TEST_POSTGRESQL'))
            ->table('roles', false)
            ->select()
            ->getAll();

        $this->assertIsArray($roles);
        $this->assertEmpty($roles);

        /** @phpstan-ignore-next-line */
        PostgreSQL::connection(env('DB_NAME_TEST_POSTGRESQL'))
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS roles;
                SQL
            )
            ->execute();
    }
}
