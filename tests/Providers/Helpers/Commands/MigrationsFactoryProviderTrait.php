<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;

trait MigrationsFactoryProviderTrait
{
    public static function getBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'Test',
                'selectedType' => MigrationFactory::TABLE,
                'dbPascal' => 'LionDatabase',
                'driver' => 'MySQL',
                'path' => 'database/Migrations/LionDatabase/MySQL/Tables/',
                'return' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\Tables;

                use Lion\Bundle\Interface\Migrations\TableInterface;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * Table schema for the entity 'Test'.
                 */
                class Test implements TableInterface
                {
                    /**
                     * Name of the migration.
                     *
                     * @const NAME
                     */
                    public const string NAME = '--NAME--';

                    /**
                     * Index number for seed execution priority.
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
                                Schema::int('id--NAME--')
                                    ->notNull()
                                    ->autoIncrement()
                                    ->primaryKey();
                            })
                            ->execute();
                    }
                }

                PHP,
            ],
            'case-1' => [
                'className' => 'Test',
                'selectedType' => MigrationFactory::TABLE,
                'dbPascal' => 'LionDatabase',
                'driver' => 'PostgreSQL',
                'path' => 'database/Migrations/LionDatabase/PostgreSQL/Tables/',
                'return' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\PostgreSQL\Tables;

                use Lion\Bundle\Interface\Migrations\TableInterface;
                use Lion\Database\Drivers\PostgreSQL;
                use stdClass;

                /**
                 * Table schema for the entity 'Test'.
                 */
                class Test implements TableInterface
                {
                    /**
                     * Name of the migration.
                     *
                     * @const NAME
                     */
                    public const string NAME = '--NAME--';

                    /**
                     * Index number for seed execution priority.
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

                PHP,
            ],
            'case-2' => [
                'className' => 'Test',
                'selectedType' => MigrationFactory::VIEW,
                'dbPascal' => 'LionDatabase',
                'driver' => 'MySQL',
                'path' => 'database/Migrations/LionDatabase/MySQL/Views/',
                'return' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\Views;

                use Lion\Bundle\Interface\Migrations\ViewInterface;
                use Lion\Database\Drivers\MySQL;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * View schema to run queries.
                 */
                class Test implements ViewInterface
                {
                    /**
                     * Name of the migration.
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

                PHP,
            ],
            'case-3' => [
                'className' => 'Test',
                'selectedType' => MigrationFactory::VIEW,
                'dbPascal' => 'LionDatabase',
                'driver' => 'PostgreSQL',
                'path' => 'database/Migrations/LionDatabase/PostgreSQL/Views/',
                'return' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\PostgreSQL\Views;

                use Lion\Bundle\Interface\Migrations\ViewInterface;
                use Lion\Database\Drivers\PostgreSQL;
                use stdClass;

                /**
                 * View schema to run queries.
                 */
                class Test implements ViewInterface
                {
                    /**
                     * Name of the migration.
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

                PHP,
            ],
            'case-4' => [
                'className' => 'Test',
                'selectedType' => MigrationFactory::STORED_PROCEDURE,
                'dbPascal' => 'LionDatabase',
                'driver' => 'MySQL',
                'path' => 'database/Migrations/LionDatabase/MySQL/StoredProcedures/',
                'return' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\StoredProcedures;

                use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
                use Lion\Database\Drivers\MySQL;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * Generates a schema to execute processes in a database.
                 */
                class Test implements StoredProcedureInterface
                {
                    /**
                     * Name of the migration.
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
                            ->createStoredProcedure(self::NAME, function (): void {
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

                PHP,
            ],
            'case-5' => [
                'className' => 'Test',
                'selectedType' => MigrationFactory::STORED_PROCEDURE,
                'dbPascal' => 'LionDatabase',
                'driver' => 'PostgreSQL',
                'path' => 'database/Migrations/LionDatabase/PostgreSQL/StoredProcedures/',
                'return' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\PostgreSQL\StoredProcedures;

                use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
                use Lion\Database\Drivers\PostgreSQL;
                use stdClass;

                /**
                 * Generates a schema to execute processes in a database.
                 */
                class Test implements StoredProcedureInterface
                {
                    /**
                     * Name of the migration.
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

                PHP,
            ],
            'case-6' => [
                'className' => 'Test',
                'selectedType' => MigrationFactory::SCHEMA,
                'dbPascal' => 'LionDatabase',
                'driver' => 'MySQL',
                'path' => 'database/Migrations/LionDatabase/MySQL/Schemas/',
                'return' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\Schemas;

                use Lion\Bundle\Interface\Migrations\SchemaInterface;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * Database schema.
                 */
                class Test implements SchemaInterface
                {
                    /**
                     * Name of the migration.
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
                            ->createDatabase(self::NAME)
                            ->execute();
                    }
                }

                PHP,
            ],
        ];
    }

    public static function getMySQLSchemaBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'LionDatabase',
                'namespace' => 'Database\\Migrations\\LionDatabase\\MySQL\\Schemas',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\Schemas;

                use Lion\Bundle\Interface\Migrations\SchemaInterface;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * Database schema.
                 */
                class LionDatabase implements SchemaInterface
                {
                    /**
                     * Name of the migration.
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
                            ->createDatabase(self::NAME)
                            ->execute();
                    }
                }

                PHP,
            ],
        ];
    }

    public static function getMySQLTableBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\MySQL\\Tables',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\Tables;

                use Lion\Bundle\Interface\Migrations\TableInterface;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * Table schema for the entity 'Test'.
                 */
                class Test implements TableInterface
                {
                    /**
                     * Name of the migration.
                     *
                     * @const NAME
                     */
                    public const string NAME = '--NAME--';

                    /**
                     * Index number for seed execution priority.
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
                                Schema::int('id--NAME--')
                                    ->notNull()
                                    ->autoIncrement()
                                    ->primaryKey();
                            })
                            ->execute();
                    }
                }

                PHP
            ],
        ];
    }

    public static function getPostgreSQLTableBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\PostgreSQL\\Tables',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\PostgreSQL\Tables;

                use Lion\Bundle\Interface\Migrations\TableInterface;
                use Lion\Database\Drivers\PostgreSQL;
                use stdClass;

                /**
                 * Table schema for the entity 'Test'.
                 */
                class Test implements TableInterface
                {
                    /**
                     * Name of the migration.
                     *
                     * @const NAME
                     */
                    public const string NAME = '--NAME--';

                    /**
                     * Index number for seed execution priority.
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

                PHP
            ],
        ];
    }

    public static function getMySQLViewBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\MySQL\\Views',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\Views;

                use Lion\Bundle\Interface\Migrations\ViewInterface;
                use Lion\Database\Drivers\MySQL;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * View schema to run queries.
                 */
                class Test implements ViewInterface
                {
                    /**
                     * Name of the migration.
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

                PHP
            ],
        ];
    }

    public static function getPostgreSQLViewBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\PostgreSQL\\Views',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\PostgreSQL\Views;

                use Lion\Bundle\Interface\Migrations\ViewInterface;
                use Lion\Database\Drivers\PostgreSQL;
                use stdClass;

                /**
                 * View schema to run queries.
                 */
                class Test implements ViewInterface
                {
                    /**
                     * Name of the migration.
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

                PHP
            ],
        ];
    }

    public static function getMySQLStoreProcedureBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\MySQL\\StoreProcedures',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\StoreProcedures;

                use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
                use Lion\Database\Drivers\MySQL;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * Generates a schema to execute processes in a database.
                 */
                class Test implements StoredProcedureInterface
                {
                    /**
                     * Name of the migration.
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
                            ->createStoredProcedure(self::NAME, function (): void {
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

                PHP,
            ],
        ];
    }

    public static function getPostgreSQLStoreProcedureBodyProvider(): array
    {
        return [
            'case-0' => [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\PostgreSQL\\StoreProcedures',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\PostgreSQL\StoreProcedures;

                use Lion\Bundle\Interface\Migrations\StoredProcedureInterface;
                use Lion\Database\Drivers\PostgreSQL;
                use stdClass;

                /**
                 * Generates a schema to execute processes in a database.
                 */
                class Test implements StoredProcedureInterface
                {
                    /**
                     * Name of the migration.
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

                PHP,
            ],
        ];
    }
}
