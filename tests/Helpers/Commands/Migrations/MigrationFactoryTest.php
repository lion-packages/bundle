<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Dependency\Injection\Container;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use stdClass;
use Tests\Providers\Helpers\Commands\MigrationsFactoryProviderTrait;

class MigrationFactoryTest extends Test
{
    use MigrationsFactoryProviderTrait;

    private MigrationFactory $migrationFactory;

    /**
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    protected function setUp(): void
    {
        /** @var MigrationFactory $migrationFactory */
        $migrationFactory = new Container()->resolve(MigrationFactory::class);

        $this->migrationFactory = $migrationFactory;

        $this->initReflection($this->migrationFactory);
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
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
     * @throws Exception If the driver is not supported.
     */
    #[Testing]
    #[TestWith(['driver' => 'PostgreSQL'], 'case-0')]
    #[TestWith(['driver' => 'PostgreSQL'], 'case-1')]
    public function getBodyDriverNotSupported(string $driver): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('Currently, the driver does not support this type of migration.');

        $this->migrationFactory->getBody('Test', MigrationFactory::SCHEMA, 'LionDatabase', $driver);
    }

    /**
     * @throws Exception If the driver is not supported.
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
        $body = $this->migrationFactory->getBody($className, $selectedType, $dbPascal, $driver);

        $this->assertObjectHasProperty('body', $body);
        $this->assertObjectHasProperty('path', $body);
        $this->assertIsString($body->body);
        $this->assertIsString($body->path);
        $this->assertSame($return, $body->body);
        $this->assertSame($path, $body->path);
    }

    #[Testing]
    #[DataProvider('getMySQLSchemaBodyProvider')]
    public function getMySQLSchemaBody(string $className, string $namespace, string $body): void
    {
        $return = $this->migrationFactory->getMySQLSchemaBody($className, $namespace);

        $this->assertSame($body, $return);
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
