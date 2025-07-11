<?php

declare(strict_types=1);

namespace Lion\Bundle\Test;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
use Lion\Bundle\Interface\CapsuleInterface;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test as Testing;
use ReflectionClass;
use ReflectionException;

/**
 * Extend testing functions
 *
 * @package Lion\Bundle\Test
 */
abstract class Test extends Testing
{
    /**
     * Dependency Injection Container Wrapper
     *
     * @var Container|null $container
     */
    private ?Container $container = null;

    /**
     * Manages the processes of creating or executing migrations
     *
     * @var Migrations|null $migrations
     */
    private ?Migrations $migrations = null;

    /**
     * Manages the processes of creating or executing seeds
     *
     * @var Seeds|null $seeds
     */
    private ?Seeds $seeds = null;

    /**
     * Run a group of migrations
     *
     * @param array<int, class-string> $migrations List of classes
     *
     * @return void
     *
     * @throws Exception If an error occurs while deleting the file
     * @throws DependencyException Error while resolving the entry
     * @throws NotFoundException No entry found for the given name
     *
     * @codeCoverageIgnore
     */
    protected function executeMigrationsGroup(array $migrations): void
    {
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
     * Run a group of seeds
     *
     * @param array<int, class-string> $seeds List of classes
     *
     * @return void
     *
     * @throws DependencyException Error while resolving the entry
     * @throws NotFoundException No entry found for the given name
     *
     * @codeCoverageIgnore
     */
    protected function executeSeedsGroup(array $seeds): void
    {
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
     * Run tests for capsule classes by testing getter, setter, and column methods
     *
     * @param string $capsuleClass Capsule class namespace
     * @param string $entity Name of the entity
     * @param array<class-string, array{
     *     column: string,
     *     set: mixed,
     *     get: mixed
     * }> $interfaces Capsule class namespace
     *
     * @throws ReflectionException
     */
    public function assertCapsule(string $capsuleClass, string $entity, array $interfaces): void
    {
        /** @phpstan-ignore-next-line */
        $reflection = new ReflectionClass($capsuleClass);

        $instance = $reflection->newInstance();

        /**
         * -----------------------------------------------------------------------------
         * Validate the capsule class
         * -----------------------------------------------------------------------------
         * The capsule class is validated to determine that it meets the defined data
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
             * Validate column values
             * -----------------------------------------------------------------------------
             * Column methods are used to validate that the expected value is obtained
             * -----------------------------------------------------------------------------
             */

            $column = $config['column'];

            $pascal = str_replace(' ', '', ucwords(str_replace('_', ' ', $column)));

            $staticGetter = "get{$pascal}Column";

            $this->assertTrue($reflection->hasMethod($staticGetter));

            $actualColumn = $capsuleClass::$staticGetter();

            $this->assertSame($column, $actualColumn, );

            /**
             * -----------------------------------------------------------------------------
             * Test Getter and Setter methods
             * -----------------------------------------------------------------------------
             * Unit tests are performed to determine that the Getter and Setter methods
             * perform their function
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
}
