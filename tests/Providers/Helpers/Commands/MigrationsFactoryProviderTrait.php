<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers\Commands;

trait MigrationsFactoryProviderTrait
{
    public static function getMySQLTableBodyProvider(): array
    {
        return [
            [
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
                 * Description
                 *
                 * @package Database\Migrations\LionDatabase\MySQL\Tables
                 */
                class Test implements TableInterface
                {
                    /**
                     * [Index number for seed execution priority]
                     *
                     * @const INDEX
                     */
                    public const ?int INDEX = null;

                    /**
                     * {@inheritdoc}
                     */
                    public function up(): stdClass
                    {
                        return Schema::connection(env('DB_DEFAULT', 'local'))
                            ->createTable('--NAME--', function (): void {
                                Schema::int('id')->notNull()->autoIncrement()->primaryKey();
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
            [
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
                 * Description
                 *
                 * @package Database\Migrations\LionDatabase\PostgreSQL\Tables
                 */
                class Test implements TableInterface
                {
                    /**
                     * [Index number for seed execution priority]
                     *
                     * @const INDEX
                     */
                    public const ?int INDEX = null;

                    /**
                     * {@inheritdoc}
                     */
                    public function up(): stdClass
                    {
                        return PostgreSQL::connection(env('DB_DEFAULT', 'local'))
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
            [
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
                 * Description
                 *
                 * @package Database\Migrations\LionDatabase\MySQL\Views
                 */
                class Test implements ViewInterface
                {
                    /**
                     * {@inheritdoc}
                     */
                    public function up(): stdClass
                    {
                        return Schema::connection(env('DB_DEFAULT', 'local'))
                            ->createView('--NAME--', function (MySQL \$db): void {
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
            [
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
                 * Description
                 *
                 * @package Database\Migrations\LionDatabase\PostgreSQL\Views
                 */
                class Test implements ViewInterface
                {
                    /**
                     * {@inheritdoc}
                     */
                    public function up(): stdClass
                    {
                        return PostgreSQL::connection(env('DB_DEFAULT', 'local'))
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
            [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\MySQL\\StoreProcedures',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\MySQL\StoreProcedures;

                use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
                use Lion\Database\Drivers\MySQL;
                use Lion\Database\Drivers\Schema\MySQL as Schema;
                use stdClass;

                /**
                 * Description
                 *
                 * @package Database\Migrations\LionDatabase\MySQL\StoreProcedures
                 */
                class Test implements StoreProcedureInterface
                {
                    /**
                     * {@inheritdoc}
                     */
                    public function up(): stdClass
                    {
                        return Schema::connection(env('DB_DEFAULT', 'local'))
                            ->createStoreProcedure('--NAME--', function (): void {
                                Schema::in()->varchar('name', 25);
                            }, function (MySQL \$db): void {
                                \$db
                                    ->table('')
                                    ->insert(['name' => '']);
                            })
                            ->execute();
                    }
                }

                PHP
            ],
        ];
    }

    public static function getPostgreSQLStoreProcedureBodyProvider(): array
    {
        return [
            [
                'className' => 'Test',
                'namespace' => 'Database\\Migrations\\LionDatabase\\PostgreSQL\\StoreProcedures',
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Migrations\LionDatabase\PostgreSQL\StoreProcedures;

                use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
                use Lion\Database\Drivers\PostgreSQL;
                use stdClass;

                /**
                 * Description
                 *
                 * @package Database\Migrations\LionDatabase\PostgreSQL\StoreProcedures
                 */
                class Test implements StoreProcedureInterface
                {
                    /**
                     * {@inheritdoc}
                     */
                    public function up(): stdClass
                    {
                        return PostgreSQL::connection(env('DB_DEFAULT', 'local'))
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
}
