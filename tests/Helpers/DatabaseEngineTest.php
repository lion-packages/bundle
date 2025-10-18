<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Driver;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;

class DatabaseEngineTest extends Test
{
    private DatabaseEngine $databaseEngine;

    protected function setUp(): void
    {
        $this->databaseEngine = new DatabaseEngine();
    }

    #[Testing]
    #[TestWith(['driver' => Driver::MYSQL, 'return' => 'MySQL'])]
    #[TestWith(['driver' => Driver::POSTGRESQL, 'return' => 'PostgreSQL'])]
    #[TestWith(['driver' => Driver::SQLITE, 'return' => 'SQLite'])]
    #[TestWith(['driver' => 'redis', 'return' => 'MySQL'])]
    public function getDriver(string $driver, string $return): void
    {
        $returnDriver = $this->databaseEngine->getDriver($driver);

        $this->assertSame($return, $returnDriver);
    }

    #[Testing]
    #[TestWith(['connectionName' => 'local', 'return' => 'mysql'])]
    #[TestWith(['connectionName' => 'lion_database_test', 'return' => 'mysql'])]
    #[TestWith(['connectionName' => 'lion_database_postgres', 'return' => 'postgresql'])]
    #[TestWith(['connectionName' => 'not-exist', 'return' => null])]
    public function getDatabaseEngineType(string $connectionName, ?string $return): void
    {
        $engineType = $this->databaseEngine->getDatabaseEngineType($connectionName);

        $this->assertSame($return, $engineType);
    }
}
