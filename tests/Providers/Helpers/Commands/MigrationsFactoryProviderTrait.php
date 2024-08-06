<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers\Commands;

trait MigrationsFactoryProviderTrait
{
    public static function getMySQLTableBodyProvider(): array
    {
        return [
            [
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                use Lion\Bundle\Interface\Migrations\TableInterface;
                use Lion\Database\Drivers\Schema\MySQL as Schema;

                /**
                 * Description
                 */
                return new class implements TableInterface
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

                PHP
            ],
        ];
    }

    public static function getPostgreSQLTableBodyProvider(): array
    {
        return [
            [
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                use Lion\Bundle\Interface\Migrations\TableInterface;
                use Lion\Database\Drivers\PostgreSQL;

                /**
                 * Description
                 */
                return new class implements TableInterface
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

                PHP
            ],
        ];
    }

    public static function getMySQLViewBodyProvider(): array
    {
        return [
            [
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                use Lion\Bundle\Interface\Migrations\ViewInterface;
                use Lion\Database\Drivers\MySQL;
                use Lion\Database\Drivers\Schema\MySQL as Schema;

                /**
                 * Description
                 */
                return new class implements ViewInterface
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

                PHP
            ],
        ];
    }

    public static function getPostgreSQLViewBodyProvider(): array
    {
        return [
            [
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                use Lion\Bundle\Interface\Migrations\ViewInterface;
                use Lion\Database\Drivers\PostgreSQL;

                /**
                 * Description
                 */
                return new class implements ViewInterface
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

                PHP
            ],
        ];
    }

    public static function getMySQLStoreProcedureBodyProvider(): array
    {
        return [
            [
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
                use Lion\Database\Drivers\MySQL;
                use Lion\Database\Drivers\Schema\MySQL as Schema;

                /**
                 * Description
                 */
                return new class implements StoreProcedureInterface
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

                PHP
            ],
        ];
    }

    public static function getPostgreSQLStoreProcedureBodyProvider(): array
    {
        return [
            [
                'body' => <<<PHP
                <?php

                declare(strict_types=1);

                use Lion\Bundle\Interface\Migrations\StoreProcedureInterface;
                use Lion\Database\Drivers\PostgreSQL;

                /**
                 * Description
                 */
                return new class implements StoreProcedureInterface
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

                PHP
            ],
        ];
    }
}
