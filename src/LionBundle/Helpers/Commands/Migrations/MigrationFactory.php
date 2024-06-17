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
     * @return string
     */
    public function getTableBody(): string
    {
        return <<<PHP
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
                return Schema::connection(env('DB_NAME', 'lion_database'))
                    ->createTable('example', function (): void {
                        Schema::int('id')->notNull()->autoIncrement()->primaryKey();
                    })
                    ->execute();
            }
        };

        PHP;
    }

    /**
     * Returns the body of the migration of type view
     *
     * @return string
     */
    public function getViewBody(): string
    {
        return <<<PHP
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
             * */
            public function up(): stdClass
            {
                return Schema::connection(env('DB_NAME', 'lion_database'))
                    ->createView('read_example', function (MySQL \$db): void {
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
     * Returns the body of the migration of type store-procedure
     *
     * @return string
     */
    public function getStoreProcedureBody(): string
    {
        return <<<PHP
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
             * */
            public function up(): stdClass
            {
                return Schema::connection(env('DB_NAME', 'lion_database'))
                    ->createStoreProcedure('example', function (): void {
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
}
