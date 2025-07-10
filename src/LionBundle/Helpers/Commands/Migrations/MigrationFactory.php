<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Migrations;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\DatabaseEngine;
use Lion\Database\Driver;
use stdClass;

/**
 * Factory of the content of the generated migrations
 *
 * @package Lion\Bundle\Helpers\Commands\Migrations
 */
class MigrationFactory
{
    /**
     * Constant for a table
     *
     * @const TABLE
     */
    public const string TABLE = 'Table';

    /**
     * Constant for a view
     *
     * @const VIEW
     */
    public const string VIEW = 'View';

    /**
     * Constant for a store-procedure
     *
     * @const STORE_PROCEDURE
     */
    public const string STORED_PROCEDURE = 'Stored-Procedure';

    /**
     * List of available types
     *
     * @const OPTIONS
     */
    public const array MIGRATIONS_OPTIONS = [
        self::TABLE,
        self::VIEW,
        self::STORED_PROCEDURE,
    ];

    /**
     * Manages basic database engine processes
     *
     * @var DatabaseEngine $databaseEngine
     */
    private DatabaseEngine $databaseEngine;

    #[Inject]
    public function setDatabaseEngine(DatabaseEngine $databaseEngine): MigrationFactory
    {
        $this->databaseEngine = $databaseEngine;

        return $this;
    }

    /**
     * Gets the data to generate the body of the selected migration type
     *
     * @param string $className Class name
     * @param string $selectedType Type of migration
     * @param string $dbPascal Database in PascalCase format
     * @param string $driver Database Engine Type
     *
     * @return stdClass
     */
    public function getBody(string $className, string $selectedType, string $dbPascal, string $driver): stdClass
    {
        $body = '';

        $path = '';

        $typeFolders = [
            self::TABLE => 'Tables',
            self::VIEW => 'Views',
            self::STORED_PROCEDURE => 'StoredProcedures',
        ];

        $mysqlMethods = [
            self::TABLE => 'getMySQLTableBody',
            self::VIEW => 'getMySQLViewBody',
            self::STORED_PROCEDURE => 'getMySQLStoredProcedureBody',
        ];

        $pgsqlMethods = [
            self::TABLE => 'getPostgreSQLTableBody',
            self::VIEW => 'getPostgreSQLViewBody',
            self::STORED_PROCEDURE => 'getPostgreSQLStoredProcedureBody',
        ];

        if (isset($typeFolders[$selectedType])) {
            $folder = $typeFolders[$selectedType];

            $path = "database/Migrations/{$dbPascal}/{$driver}/{$folder}/";

            $namespace = "Database\\Migrations\\{$dbPascal}\\{$driver}\\{$folder}";

            if ($this->databaseEngine->getDriver(Driver::MYSQL) === $driver) {
                $method = $mysqlMethods[$selectedType];

                $body = $this->$method($className, $namespace);
            } elseif ($this->databaseEngine->getDriver(Driver::POSTGRESQL) === $driver) {
                $method = $pgsqlMethods[$selectedType];

                $body = $this->$method($className, $namespace);
            }
        }

        return (object) [
            'body' => str_replace(['\r\n', '\r'], '\n', $body),
            'path' => $path,
        ];
    }


    /**
     * Returns the body of the migration of type table
     *
     * @param string $className [Class name]
     * @param string $namespace [Class namespace]
     *
     * @return string
     */
    public function getMySQLTableBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\TableInterface;
        use Lion\Database\Drivers\Schema\MySQL as Schema;
        use stdClass;

        /**
         * Table schema for the entity '{$className}'
         */
        class {$className} implements TableInterface
        {
            /**
             * Name of the migration
             *
             * @const NAME
             */
            public const string NAME = '--NAME--';

            /**
             * Index number for seed execution priority
             *
             * @const INDEX
             */
            public const ?int INDEX = null;

            /**
             * {@inheritDoc}
             */
            public function up(): stdClass
            {
                return Schema::connection(getDefaultConnection())
                    ->createTable(self::NAME, function (): void {
                        Schema::int('id')
                            ->notNull()
                            ->autoIncrement()
                            ->primaryKey();
                    })
                    ->execute();
            }
        }

        PHP;
    }

    /**
     * Returns the body of the migration of type table
     *
     * @param string $className Class name
     * @param string $namespace Class namespace
     *
     * @return string
     */
    public function getPostgreSQLTableBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\TableInterface;
        use Lion\Database\Drivers\PostgreSQL;
        use stdClass;

        /**
         * Table schema for the entity '{$className}'
         */
        class {$className} implements TableInterface
        {
            /**
             * Name of the migration
             *
             * @const NAME
             */
            public const string NAME = '--NAME--';

            /**
             * Index number for seed execution priority
             *
             * @const INDEX
             */
            public const ?int INDEX = null;

            /**
             * {@inheritDoc}
             */
            public function up(): stdClass
            {
                return PostgreSQL::connection(getDefaultConnection())
                    ->query(
                        <<<SQL
                        -- SQL
                        SQL
                    )
                    ->execute();
            }
        }

        PHP;
    }

    /**
     * Returns the body of the migration of type view
     *
     * @param string $className Class name
     * @param string $namespace Class namespace
     *
     * @return string
     */
    public function getMySQLViewBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\ViewInterface;
        use Lion\Database\Drivers\MySQL;
        use Lion\Database\Drivers\Schema\MySQL as Schema;
        use stdClass;

        /**
         * View schema to run queries
         */
        class {$className} implements ViewInterface
        {
            /**
             * Name of the migration
             *
             * @const NAME
             */
            public const string NAME = '--NAME--';

            /**
             * {@inheritDoc}
             */
            public function up(): stdClass
            {
                return Schema::connection(getDefaultConnection())
                    ->createView(self::NAME, function (MySQL \$db): void {
                        \$db
                            ->table('table')
                            ->select();
                    })
                    ->execute();
            }
        }

        PHP;
    }

    /**
     * Returns the body of the migration of type view
     *
     * @param string $className Class name
     * @param string $namespace Class namespace
     *
     * @return string
     */
    public function getPostgreSQLViewBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\ViewInterface;
        use Lion\Database\Drivers\PostgreSQL;
        use stdClass;

        /**
         * View schema to run queries
         */
        class {$className} implements ViewInterface
        {
            /**
             * Name of the migration
             *
             * @const NAME
             */
            public const string NAME = '--NAME--';

            /**
             * {@inheritDoc}
             */
            public function up(): stdClass
            {
                return PostgreSQL::connection(getDefaultConnection())
                    ->query(
                        <<<SQL
                        -- SQL
                        SQL
                    )
                    ->execute();
            }
        }

        PHP;
    }

    /**
     * Returns the body of the migration of type store-procedure
     *
     * @param string $className Class name
     * @param string $namespace Class namespace
     *
     * @return string
     */
    public function getMySQLStoredProcedureBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
        use Lion\Database\Drivers\MySQL;
        use Lion\Database\Drivers\Schema\MySQL as Schema;
        use stdClass;

        /**
         * Generates a schema to execute processes in a database
         */
        class {$className} implements StoredProcedureInterface
        {
            /**
             * Name of the migration
             *
             * @const NAME
             */
            public const string NAME = '--NAME--';

            /**
             * {@inheritDoc}
             */
            public function up(): stdClass
            {
                return Schema::connection(getDefaultConnection())
                    ->createStoreProcedure(self::NAME, function (): void {
                        Schema::in()->varchar('name', 25);
                    }, function (MySQL \$db): void {
                        \$db
                            ->table('')
                            ->insert([
                                'name' => '',
                            ]);
                    })
                    ->execute();
            }
        }

        PHP;
    }

    /**
     * Returns the body of the migration of type store-procedure
     *
     * @param string $className Class name
     * @param string $namespace Class namespace
     *
     * @return string
     */
    public function getPostgreSQLStoredProcedureBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
        use Lion\Database\Drivers\PostgreSQL;
        use stdClass;

        /**
         * Generates a schema to execute processes in a database
         */
        class {$className} implements StoredProcedureInterface
        {
            /**
             * Name of the migration
             *
             * @const NAME
             */
            public const string NAME = '--NAME--';

            /**
             * {@inheritDoc}
             */
            public function up(): stdClass
            {
                return PostgreSQL::connection(getDefaultConnection())
                    ->query(
                        <<<SQL
                        -- SQL
                        SQL
                    )
                    ->execute();
            }
        }

        PHP;
    }
}
