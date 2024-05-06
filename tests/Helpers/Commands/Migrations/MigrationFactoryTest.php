<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;

class MigrationFactoryTest extends Test
{
    private MigrationFactory $migrationFactory;

    protected function setUp(): void
    {
        $this->migrationFactory = (new Container())
            ->injectDependencies(new MigrationFactory());
    }

    public function testGetTableBody(): void
    {
        $tableBody = <<<PHP
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
            const INDEX = null;

            /**
             * {@inheritdoc}
             */
            public function up(): object
            {
                return Schema::connection(env('DB_NAME', 'lion_database'))
                    ->createTable('example', function (): void {
                        Schema::int('id')->notNull()->autoIncrement()->primaryKey();
                    })
                    ->execute();
            }
        };

        PHP;

        $this->assertSame($tableBody, $this->migrationFactory->getTableBody());
    }

    public function testGetViewBody(): void
    {
        $viewBody = <<<PHP
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
            public function up(): object
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

        $this->assertSame($viewBody, $this->migrationFactory->getViewBody());
    }

    public function testGetStoreProcedureBody(): void
    {
        $storeProcedureBody = <<<PHP
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
            public function up(): object
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

        $this->assertSame($storeProcedureBody, $this->migrationFactory->getStoreProcedureBody());
    }
}
