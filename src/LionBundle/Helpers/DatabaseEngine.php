<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Lion\Database\Connection;
use Lion\Database\Driver;

/**
 * Manages basic database engine processes.
 */
class DatabaseEngine
{
    /**
     * Gets the database engine with Pascalcase Format [Default: MySQL].
     *
     * @param string $driver Database engine.
     *
     * @return string
     */
    public function getDriver(string $driver): string
    {
        $drivers = [
            Driver::MYSQL => 'MySQL',
            Driver::POSTGRESQL => 'PostgreSQL',
            Driver::SQLITE => 'SQLite',
        ];

        return $drivers[$driver] ?? 'MySQL';
    }

    /**
     * Gets the database engine type of the defined connection.
     *
     * @param string $connectionName Connection name.
     *
     * @return string|null
     */
    public function getDatabaseEngineType(string $connectionName): ?string
    {
        $connections = Connection::getConnections();

        return $connections[$connectionName]['type'] ?? null;
    }
}
