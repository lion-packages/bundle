<?php

declare(strict_types=1);

namespace Lion\Bundle\Test;

use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Helpers\Commands\Seeds\Seeds;
use Lion\Bundle\Interface\CapsuleInterface;
use Lion\Test\Test as Testing;

/**
 * Extend testing functions
 *
 * @property Migrations|null $migrations [Manages the processes of creating or
 * executing migrations]
 * @property Seeds|null $seeds [Manages the processes of creating or executing
 * seeds]
 *
 * @package Lion\Bundle
 */
class Test extends Testing
{
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
     * @param array<int, string> $migrations [List of classes]
     *
     * @return void
     */
    protected function executeMigrationsGroup(array $migrations): void
    {
        if (NULL_VALUE === $this->migrations) {
            $this->migrations = new Migrations();
        }

        $this->migrations->executeMigrationsGroup($migrations);
    }

    /**
     * Run a group of seeds
     *
     * @param array $seeds [List of classes]
     *
     * @return void
     */
    protected function executeSeedsGroup(array $seeds): void
    {
        if (NULL_VALUE === $this->seeds) {
            $this->seeds = new Seeds();
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
        $this->assertIsString($capsuleInterface->getTableName());
        $this->assertSame($entity, $capsuleInterface->getTableName());
    }
}
