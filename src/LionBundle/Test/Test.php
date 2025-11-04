<?php

declare(strict_types=1);

namespace Lion\Bundle\Test;

use Closure;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
use Lion\Bundle\Helpers\Env;
use Lion\Bundle\Interface\CapsuleInterface;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Database\Drivers\Schema\MySQL;
use Lion\Dependency\Injection\Container;
use Lion\Request\Http;
use Lion\Test\Test as Testing;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

/**
 * Extend testing functions.
 */
abstract class Test extends Testing
{
    /**
     * Dependency Injection Container Wrapper.
     *
     * @var Container|null $container
     */
    private static ?Container $container = null;

    /**
     * Manages the processes of creating or executing migrations.
     *
     * @var Migrations|null $migrations
     */
    private static ?Migrations $migrations = null;

    /**
     * Manages the processes of creating or executing seeds.
     *
     * @var Seeds|null $seeds
     */
    private static ?Seeds $seeds = null;

    /**
     * Defines whether the running process is in a temporary database.
     *
     * @var bool $runAnIsolatedDatabase
     */
    private static bool $runningATemporaryDatabase = false;

    /**
     * Run a group of migrations.
     *
     * @param array<int, class-string> $migrations List of classes.
     *
     * @return void
     *
     * @throws Exception If an error occurs while deleting the file.
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     *
     * @codeCoverageIgnore
     */
    final protected static function executeMigrationsGroup(array $migrations): void
    {
        /**
         * -----------------------------------------------------------------------------
         * Initializes objects.
         * -----------------------------------------------------------------------------
         */

        if (null === self::$container) {
            self::$container = new Container();
        }

        if (null === self::$migrations) {
            /** @var Migrations $migrationsInstance */
            $migrationsInstance = self::$container->resolve(Migrations::class);

            self::$migrations = $migrationsInstance;
        }

        self::$migrations->executeMigrationsGroup($migrations, self::$runningATemporaryDatabase);
    }

    /**
     * Run a group of seeds.
     *
     * @param array<int, class-string> $seeds List of classes.
     *
     * @return void
     *
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     *
     * @codeCoverageIgnore
     */
    final protected static function executeSeedsGroup(array $seeds): void
    {
        /**
         * -----------------------------------------------------------------------------
         * Initializes objects.
         * -----------------------------------------------------------------------------
         */

        if (null === self::$container) {
            self::$container = new Container();
        }

        if (null === self::$seeds) {
            /** @var Seeds $seedsInstance */
            $seedsInstance = self::$container->resolve(Seeds::class);

            self::$seeds = $seedsInstance;
        }

        self::$seeds->executeSeedsGroup($seeds);
    }

    /**
     * Run tests for capsule classes by testing getter, setter, and column methods.
     *
     * @param string $capsuleClass Capsule class namespace.
     * @param string $entity Name of the entity.
     * @param array<class-string, array{
     *     column: string,
     *     set: mixed,
     *     get: mixed
     * }> $interfaces Capsule class namespace.
     *
     * @throws ReflectionException
     */
    final public function assertCapsule(string $capsuleClass, string $entity, array $interfaces): void
    {
        /** @phpstan-ignore-next-line */
        $reflection = new ReflectionClass($capsuleClass);

        $instance = $reflection->newInstance();

        /**
         * -----------------------------------------------------------------------------
         * Validate the capsule class.
         * -----------------------------------------------------------------------------
         * The capsule class is validated to determine that it meets the defined data.
         * -----------------------------------------------------------------------------
         */

        $this->assertInstanceOf(
            CapsuleInterface::class,
            $reflection
                ->getMethod('capsule')
                ->invoke($instance)
        );

        $this->assertIsArray(
            $reflection
                ->getMethod('jsonSerialize')
                ->invoke($instance)
        );

        $this->assertSame(
            $entity,
            $reflection
                ->getMethod('getTableName')
                ->invoke($instance)
        );

        foreach ($interfaces as $interfaceName => $config) {
            $interface = new ReflectionClass($interfaceName);

            /**
             * -----------------------------------------------------------------------------
             * Validate column values.
             * -----------------------------------------------------------------------------
             * Column methods are used to validate that the expected value is obtained.
             * -----------------------------------------------------------------------------
             */

            $column = $config['column'];

            $pascal = str_replace(' ', '', ucwords(str_replace('_', ' ', $column)));

            $staticGetter = "get{$pascal}Column";

            $this->assertTrue($reflection->hasMethod($staticGetter));

            $actualColumn = $capsuleClass::$staticGetter();

            $this->assertSame($column, $actualColumn);

            /**
             * -----------------------------------------------------------------------------
             * Test Getter and Setter methods.
             * -----------------------------------------------------------------------------
             * Unit tests are performed to determine that the Getter and Setter methods
             * perform their function.
             * -----------------------------------------------------------------------------
             */

            foreach ($interface->getMethods() as $method) {
                $methodName = $method->getName();

                if (str_starts_with($methodName, 'get') && !$method->isStatic()) {
                    $property = substr($methodName, 3);

                    $setter = 'set' . $property;

                    if ($reflection->hasMethod($setter)) {
                        $reflection
                            ->getMethod($setter)
                            ->invoke($instance, $config['set']);

                        $result = $reflection
                            ->getMethod($methodName)
                            ->invoke($instance);

                        $this->assertSame($config['get'], $result);
                    }
                }
            }
        }
    }

    /**
     * Runs the given closure inside an isolated environment sandbox.
     *
     * Each call creates a unique sandbox context, so tests can run in parallel
     * without interfering with each other. The sandbox is destroyed after the
     * closure completes, restoring the original environment.
     *
     * <code>
     *     #[Testing]
     *     #[RunInSeparateProcess]
     *     public function app(): void
     * </code>
     *
     * @param Closure $callable The code to run in the isolated environment.
     *
     * @return void
     *
     * @throws RuntimeException If sandbox activation or cleanup fails.
     */
    final protected function runInSeparateEnvironment(Closure $callable): void
    {
        // Generate a unique context ID (per test call)
        $contextId = uniqid('sandbox_', true);

        // Enable a fresh sandbox context (empty or seeded from $_ENV)
        Env::enableSandbox($contextId);

        try {
            // Run the test code inside the sandbox
            $callable();
        } finally {
            // Always clean up after execution
            Env::disableSandbox($contextId);
        }
    }

    /**
     * Executes a callback using a temporary database connection.
     *
     * This method clones the default database connection and runs the provided callback
     * within that isolated environment. It ensures that any queries or changes do not
     * affect the original database.
     *
     * Currently, this functionality is only supported with MySQL.
     * The temporary connection is based on the default connection configured in the system.
     *
     * For safe execution in PHPUnit, it is recommended to use the attribute:
     *
     * <code>
     *     #[Testing]
     *     #[RunInSeparateProcess]
     *     public function app(): void
     * </code>
     *
     * This ensures that changes to static connection state or global settings do not
     * interfere with other tests running in parallel.
     *
     * @param Closure $callable A function to execute within the connection.
     *
     * @return void
     *
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     *
     * @codeCoverageIgnore
     */
    final protected function runInSeparateDatabase(Closure $callable): void
    {
        self::$runningATemporaryDatabase = true;

        if (null === self::$container) {
            self::$container = new Container();
        }

        if (null === self::$migrations) {
            /** @var Migrations $migrationsInstance */
            $migrationsInstance = self::$container->resolve(Migrations::class);

            self::$migrations = $migrationsInstance;
        }

        // Create isolated databases
        $connections = Connection::getConnections();

        $mysqlConnections = array_filter($connections, fn (array $conn) => $conn['type'] === Driver::MYSQL);

        $tempConnections = [];

        foreach ($mysqlConnections as $connectionName => $connection) {
            $tempDbName = "{$connection['dbname']}_" . dechex(mt_rand(0, 0xfffff));

            $tempConnections[$connectionName] = $tempDbName;

            $response = MySQL::connection($connectionName)
                ->createDatabase($tempDbName)
                ->execute();

            if (isError($response)) {
                throw new RuntimeException(
                    'An error occurred while creating the database.',
                    Http::INTERNAL_SERVER_ERROR
                );
            }

            Connection::addConnection($connectionName, [
                ...$connection,
                'dbname' => $tempDbName,
            ]);
        }

        try {
            // Run tests
            $callable($tempConnections);
        } finally {
            // Reset connections
            foreach ($mysqlConnections as $connectionName => $connection) {
                Connection::addConnection($connectionName, [
                    ...$connection,
                    'dbname' => $connection['dbname'],
                ]);

                $response = MySQL::connection($connectionName)
                    ->dropDatabase($tempConnections[$connectionName])
                    ->execute();

                if (isError($response)) {
                    throw new RuntimeException(
                        'An error occurred while deleting the database.',
                        Http::INTERNAL_SERVER_ERROR
                    );
                }
            }

            self::$runningATemporaryDatabase = false;
        }
    }
}
