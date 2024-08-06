<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use Tests\Providers\Helpers\Commands\MigrationsFactoryProviderTrait;

class MigrationFactoryTest extends Test
{
    use MigrationsFactoryProviderTrait;

    private MigrationFactory $migrationFactory;

    protected function setUp(): void
    {
        $this->migrationFactory = new MigrationFactory();
    }

    #[Testing]
    #[DataProvider('getMySQLTableBodyProvider')]
    public function getMySQLTableBody(string $body): void
    {
        $return = $this->migrationFactory->getMySQLTableBody();

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLTableBodyProvider')]
    public function getPostgreSQLTableBody(string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLTableBody();

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getMySQLViewBodyProvider')]
    public function getMySQLViewBody(string $body): void
    {
        $return = $this->migrationFactory->getMySQLViewBody();

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLViewBodyProvider')]
    public function getPostgreSQLViewBody(string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLViewBody();

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getMySQLStoreProcedureBodyProvider')]
    public function getMySQLStoreProcedureBody(string $body): void
    {
        $return = $this->migrationFactory->getMySQLStoreProcedureBody();

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLStoreProcedureBodyProvider')]
    public function getPostgreSQLStoreProcedureBody(string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLStoreProcedureBody();

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }
}
