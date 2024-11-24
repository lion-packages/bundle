<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Migrations;

/**
 * Factory of the content of the generated migrations
 *
 * @package Lion\Bundle\Helpers\Commands\Migrations
 */
class MigrationFactory
{
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
         * Description
         *
         * @package {$namespace}
         */
        class {$className} implements TableInterface
        {
            /**
             * [Index number for seed execution priority]
             *
             * @const INDEX
             */
            const ?int INDEX = null;

            /**
             * {@inheritdoc}
             */
            public function up(): stdClass
            {
                return Schema::connection(env('DB_NAME_EXAMPLE', 'lion_database'))
                    ->createTable('--NAME--', function (): void {
                        Schema::int('id')->notNull()->autoIncrement()->primaryKey();
                    })
                    ->execute();
            }
        };

        PHP;
    }

    /**
     * Returns the body of the migration of type table
     *
     * @param string $className [Class name]
     * @param string $namespace [Class namespace]
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
         * Description
         *
         * @package {$namespace}
         */
        class {$className} implements TableInterface
        {
            /**
             * [Index number for seed execution priority]
             *
             * @const INDEX
             */
            const ?int INDEX = null;

            /**
             * {@inheritdoc}
             */
            public function up(): stdClass
            {
                return PostgreSQL::connection(env('DB_NAME_EXAMPLE', 'lion_database'))
                    ->query(
                        <<<SQL
                        -- SQL
                        SQL
                    )
                    ->execute();
            }
        };

        PHP;
    }

    /**
     * Returns the body of the migration of type view
     *
     * @param string $className [Class name]
     * @param string $namespace [Class namespace]
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
         * Description
         *
         * @package {$namespace}
         */
        class {$className} implements ViewInterface
        {
            /**
             * {@inheritdoc}
             */
            public function up(): stdClass
            {
                return Schema::connection(env('DB_NAME_EXAMPLE', 'lion_database'))
                    ->createView('--NAME--', function (MySQL \$db): void {
                        \$db
                            ->table('table')
                            ->select();
                    })
                    ->execute();
            }
        };

        PHP;
    }

    /**
     * Returns the body of the migration of type view
     *
     * @param string $className [Class name]
     * @param string $namespace [Class namespace]
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
         * Description
         *
         * @package {$namespace}
         */
        class {$className} implements ViewInterface
        {
            /**
             * {@inheritdoc}
             */
            public function up(): stdClass
            {
                return PostgreSQL::connection(env('DB_NAME_EXAMPLE', 'lion_database'))
                    ->query(
                        <<<SQL
                        -- SQL
                        SQL
                    )
                    ->execute();
            }
        };

        PHP;
    }

    /**
     * Returns the body of the migration of type store-procedure
     *
     * @param string $className [Class name]
     * @param string $namespace [Class namespace]
     *
     * @return string
     */
    public function getMySQLStoreProcedureBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
        use Lion\Database\Drivers\MySQL;
        use Lion\Database\Drivers\Schema\MySQL as Schema;
        use stdClass;

        /**
         * Description
         *
         * @package {$namespace}
         */
        class {$className} implements StoreProcedureInterface
        {
            /**
             * {@inheritdoc}
             */
            public function up(): stdClass
            {
                return Schema::connection(env('DB_NAME_EXAMPLE', 'lion_database'))
                    ->createStoreProcedure('--NAME--', function (): void {
                        Schema::in()->varchar('name', 25);
                    }, function (MySQL \$db): void {
                        \$db
                            ->table('')
                            ->insert(['name' => '']);
                    })
                    ->execute();
            }
        };

        PHP;
    }

    /**
     * Returns the body of the migration of type store-procedure
     *
     * @param string $className [Class name]
     * @param string $namespace [Class namespace]
     *
     * @return string
     */
    public function getPostgreSQLStoreProcedureBody(string $className, string $namespace): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
        use Lion\Database\Drivers\PostgreSQL;
        use stdClass;

        /**
         * Description
         *
         * @package {$namespace}
         */
        class {$className} implements StoreProcedureInterface
        {
            /**
             * {@inheritdoc}
             */
            public function up(): stdClass
            {
                return PostgreSQL::connection(env('DB_NAME_EXAMPLE', 'lion_database'))
                    ->query(
                        <<<SQL
                        -- SQL
                        SQL
                    )
                    ->execute();
            }
        };

        PHP;
    }
}
