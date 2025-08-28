<?php

declare(strict_types=1);

namespace Lion\Bundle\Test;

use Closure;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
use Lion\Bundle\Interface\CapsuleInterface;
use Lion\Database\Connection;
use Lion\Database\Drivers\Schema\MySQL;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test as Testing;
use ReflectionClass;
use ReflectionException;

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
    private ?Container $container = null;

    /**
     * Manages the processes of creating or executing migrations.
     *
     * @var Migrations|null $migrations
     */
    private ?Migrations $migrations = null;

    /**
     * Manages the processes of creating or executing seeds.
     *
     * @var Seeds|null $seeds
     */
    private ?Seeds $seeds = null;

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
    final protected function executeMigrationsGroup(array $migrations): void
    {
        /**
         * -----------------------------------------------------------------------------
         * Initializes objects.
         * -----------------------------------------------------------------------------
         */

        if (null === $this->container) {
            $this->container = new Container();
        }

        if (null === $this->migrations) {
            /** @var Migrations $migrationsInstance */
            $migrationsInstance = $this->container->resolve(Migrations::class);

            $this->migrations = $migrationsInstance;
        }

        $this->migrations->executeMigrationsGroup($migrations);
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
    final protected function executeSeedsGroup(array $seeds): void
    {
        /**
         * -----------------------------------------------------------------------------
         * Initializes objects.
         * -----------------------------------------------------------------------------
         */

        if (null === $this->container) {
            $this->container = new Container();
        }

        if (null === $this->seeds) {
            /** @var Seeds $seedsInstance */
            $seedsInstance = $this->container->resolve(Seeds::class);

            $this->seeds = $seedsInstance;
        }

        $this->seeds->executeSeedsGroup($seeds);
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
     *     #[RunInSeparateProcess]
     *     public function testApp(): void
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
     */
    final protected function runInSeparateDatabase(Closure $callable): void
    {
        /**
         * -----------------------------------------------------------------------------
         * Initializes objects.
         * -----------------------------------------------------------------------------
         */

        if (null === $this->container) {
            $this->container = new Container();
        }

        if (null === $this->migrations) {
            /** @var Migrations $migrationsInstance */
            $migrationsInstance = $this->container->resolve(Migrations::class);

            $this->migrations = $migrationsInstance;
        }

        /**
         * -----------------------------------------------------------------------------
         * Get connection values.
         * -----------------------------------------------------------------------------
         * Connection values are obtained to create alternate database connections.
         * -----------------------------------------------------------------------------
         */

        $connectionName = getDefaultConnection();

        $connections = Connection::getConnections();

        $connection = $connections[$connectionName];

        $dbName = $connection['dbname'];

        $tempConnectionName = $connection['dbname'] . uniqid(uniqid('_'));

        /**
         * -----------------------------------------------------------------------------
         * Create database.
         * -----------------------------------------------------------------------------
         * Create the alternate database to run processes.
         * -----------------------------------------------------------------------------
         */

        $this->migrations->processingWithStaticConnections(function () use (
            $connectionName,
            $callable,
            $connection,
            $dbName,
            $tempConnectionName
        ): void {
            MySQL::connection($connectionName)
                ->createDatabase($tempConnectionName)
                ->execute();

            Connection::addConnection($tempConnectionName, [
                ...$connection,
                'dbname' => $tempConnectionName,
            ]);

            Connection::setDefaultConnectionName($tempConnectionName);

            $this->migrations->cloneDatabase($dbName, $connectionName, $tempConnectionName);

            $callable($tempConnectionName);

            MySQL::connection($connectionName)
                ->dropDatabase($tempConnectionName)
                ->execute();

            Connection::removeConnection($tempConnectionName);

            Connection::setDefaultConnectionName($connectionName);
        });
    }
}
