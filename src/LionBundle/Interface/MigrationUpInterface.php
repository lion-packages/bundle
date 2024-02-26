<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

/**
 * Implement abstract methods for migrations
 *
 * @package Lion\Bundle\Interface
 */
interface MigrationUpInterface
{
    /**
     * Run the process and create a table in the database
     *
     * @return object
     * */
    public function up(): object;
}
