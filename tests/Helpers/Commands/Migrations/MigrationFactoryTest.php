<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use stdClass;
use Tests\Providers\Helpers\Commands\MigrationsFactoryProviderTrait;

class MigrationFactoryTest extends Test
{
    use MigrationsFactoryProviderTrait;

    private MigrationFactory $migrationFactory;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->migrationFactory = new MigrationFactory()
            ->setDatabaseEngine(new DatabaseEngine());

        $this->initReflection($this->migrationFactory);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setDatabaseEngine(): void
    {
        $this->assertInstanceOf(
            MigrationFactory::class,
            $this->migrationFactory->setDatabaseEngine(new DatabaseEngine())
        );

        $this->assertInstanceOf(DatabaseEngine::class, $this->getPrivateProperty('databaseEngine'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getBodyProvider')]
    public function getBody(
        string $className,
        string $selectedType,
        string $dbPascal,
        string $driver,
        string $path,
        string $return
    ): void {
        $body = $this->getPrivateMethod('getBody', [
            'className' => $className,
            'selectedType' => $selectedType,
            'dbPascal' => $dbPascal,
            'driver' => $driver,
        ]);

        $this->assertIsObject($body);
        $this->assertInstanceOf(stdClass::class, $body);
        $this->assertObjectHasProperty('body', $body);
        $this->assertObjectHasProperty('path', $body);
        $this->assertIsString($body->body);
        $this->assertIsString($body->path);
        $this->assertSame($return, $body->body);
        $this->assertSame($path, $body->path);
    }

    #[Testing]
    #[DataProvider('getMySQLTableBodyProvider')]
    public function getMySQLTableBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getMySQLTableBody($className, $namespace);

        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLTableBodyProvider')]
    public function getPostgreSQLTableBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLTableBody($className, $namespace);

        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getMySQLViewBodyProvider')]
    public function getMySQLViewBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getMySQLViewBody($className, $namespace);

        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLViewBodyProvider')]
    public function getPostgreSQLViewBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLViewBody($className, $namespace);

        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getMySQLStoreProcedureBodyProvider')]
    public function getMySQLStoreProcedureBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getMySQLStoredProcedureBody($className, $namespace);

        $this->assertSame($body, $return);
    }

    #[Testing]
    #[DataProvider('getPostgreSQLStoreProcedureBodyProvider')]
    public function getPostgreSQLStoreProcedureBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getPostgreSQLStoredProcedureBody($className, $namespace);

        $this->assertSame($body, $return);
    }
}
