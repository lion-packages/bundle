<?php

declare(strict_types=1);

namespace Lion\Bundle;

use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Test\Test as Testing;

/**
 * Extend testing functions
 *
 * @property Migrations|null $migrations [Manages the processes of creating or
 * executing migrations]
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
     * Run a group of migrations
     *
     * @param array<int, string> $migrations [List of classes]
     *
     * @return void
     */
    protected function executeMigrationsGroup(array $migrations): void
    {
        if (null === $this->migrations) {
            $this->migrations = new Migrations();
        }

        $this->migrations->executeMigrationsGroup($migrations);
    }
}
