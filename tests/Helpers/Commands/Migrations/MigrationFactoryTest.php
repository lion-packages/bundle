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
    public function getMySQLTableBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getMySQLTableBody($className, $namespace);

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLTableBodyProvider')]
    public function getPostgreSQLTableBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLTableBody($className, $namespace);

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getMySQLViewBodyProvider')]
    public function getMySQLViewBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getMySQLViewBody($className, $namespace);

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLViewBodyProvider')]
    public function getPostgreSQLViewBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLViewBody($className, $namespace);

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getMySQLStoreProcedureBodyProvider')]
    public function getMySQLStoreProcedureBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getMySQLStoreProcedureBody($className, $namespace);

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLStoreProcedureBodyProvider')]
    public function getPostgreSQLStoreProcedureBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLStoreProcedureBody($className, $namespace);

        $this->assertIsString($return);
        $this->assertSame($body, $return);
    }
}
