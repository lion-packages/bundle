<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

use stdClass;

/**
 * Implement abstract methods for migrations.
 */
interface MigrationUpInterface
{
    /**
     * Run the process and create a table in the database.
     *
     * @return int|stdClass
     * */
    public function up(): int|stdClass;
}
