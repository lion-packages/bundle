<?php

declare(strict_types=1);

namespace Lion\Bundle\Test;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
use Lion\Bundle\Interface\CapsuleInterface;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test as Testing;

/**
 * Extend testing functions
 *
 * @property Container|null $container [Dependency Injection Container Wrapper]
 * @property Migrations|null $migrations [Manages the processes of creating or
 * executing migrations]
 * @property Seeds|null $seeds [Manages the processes of creating or executing
 * seeds]
 *
 * @package Lion\Bundle
 *
 * @codeCoverageIgnore
 */
class Test extends Testing
{
    /**
     * [Dependency Injection Container Wrapper]
     *
     * @var Container|null $container
     */
    private ?Container $container = null;

    /**
     * [Manages the processes of creating or executing migrations]
     *
     * @var Migrations|null $migrations
     */
    private ?Migrations $migrations = null;

    /**
     * [Manages the processes of creating or executing seeds]
     *
     * @var Seeds|null $seeds
     */
    private ?Seeds $seeds = null;

    /**
     * Run a group of migrations
     *
     * @param array<int, class-string> $migrations [List of classes]
     *
     * @return void
     *
     * @throws DependencyException
     * @throws NotFoundException
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
     * @param array<int, class-string> $seeds [List of classes]
     *
     * @return void
     *
     * @throws DependencyException
     * @throws NotFoundException
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
     * Checks two aspects of an object that implements the CapsuleInterface
     * interface
     *
     * @param CapsuleInterface $capsuleInterface [Implement abstract methods for
     * capsule classes]
     * @param string $entity [Entity name]
     *
     * @return void
     */
    public function assertCapsule(CapsuleInterface $capsuleInterface, string $entity): void
    {
        $this->assertInstanceOf(CapsuleInterface::class, $capsuleInterface->capsule());
        $this->assertIsArray($capsuleInterface->jsonSerialize());
        $this->assertSame($entity, $capsuleInterface->getTableName());
    }
}
