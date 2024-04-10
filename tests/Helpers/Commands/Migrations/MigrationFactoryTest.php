<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Migrations;

use Lion\Bundle\Helpers\Commands\Migrations\MigrationFactory;
use Lion\Dependency\Injection\Container;
use Lion\Helpers\Str;
use Lion\Test\Test;

class MigrationFactoryTest extends Test
{
    private MigrationFactory $migrationFactory;
    private Str $str;

    protected function setUp(): void
    {
        $this->migrationFactory = (new Container())->injectDependencies(new MigrationFactory());

        $this->str = new Str();
    }

    public function testGetTableBody(): void
    {
        $tableBody = $this->migrationFactory->getTableBody();

        $this->assertIsString($tableBody);

        $this->assertSame(
            $this->str->of("<?php")->ln()->ln()
                ->concat('declare(strict_types=1);')->ln()->ln()
                ->concat("use Lion\Bundle\Interface\MigrationUpInterface;")->ln()
                ->concat("use Lion\Database\Drivers\Schema\MySQL as DB;")->ln()->ln()
                ->concat("return new class implements MigrationUpInterface\n{")->ln()
                ->lt()->concat('const INDEX = null;')->ln()->ln()
                ->lt()->concat("/**\n\t * {@inheritdoc}\n\t */")->ln()
                ->lt()->concat("public function up(): object\n\t{")->ln()
                ->lt()->lt()->concat("return DB::connection(env('--CONNECTION--', 'lion_database'))")->ln()
                ->lt()->lt()->lt()->concat("->createTable('example', function() {")->ln()
                ->lt()->lt()->lt()->lt()->concat("DB::int('id')->notNull()->autoIncrement()->primaryKey();")->ln()
                ->lt()->lt()->lt()->concat('})')->ln()
                ->lt()->lt()->lt()->concat("->execute();")->ln()
                ->lt()->concat("}")->ln()
                ->concat("};")->ln()
                ->get(),
            $tableBody
        );
    }

    public function testGetViewBody(): void
    {
        $viewBody = $this->migrationFactory->getViewBody();

        $this->assertIsString($viewBody);

        $this->assertSame(
            $this->str->of("<?php")->ln()->ln()
                ->concat('declare(strict_types=1);')->ln()->ln()
                ->concat("use Lion\Bundle\Interface\MigrationUpInterface;")->ln()
                ->concat("use Lion\Database\Drivers\MySQL;")->ln()
                ->concat("use Lion\Database\Drivers\Schema\MySQL as Schema;")->ln()->ln()
                ->concat("return new class implements MigrationUpInterface\n{")->ln()
                ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
                ->lt()->concat("public function up(): object\n\t{")->ln()
                ->lt()->lt()->concat("return Schema::connection(env('--CONNECTION--', 'lion_database'))")->ln()
                ->lt()->lt()->lt()->concat("->createView('read_example', " . 'function(MySQL $db) {')->ln()
                ->lt()->lt()->lt()->lt()->concat('$db->table' . "('table')->select();")->ln()
                ->lt()->lt()->lt()->concat("})")->ln()
                ->lt()->lt()->lt()->concat("->execute();")->ln()
                ->lt()->concat("}")->ln()
                ->concat("};")
                ->get(),
            $viewBody
        );
    }

    public function testGetStoreProcedureBody(): void
    {
        $storeProcedureBody = $this->migrationFactory->getStoreProcedureBody();

        $this->assertIsString($storeProcedureBody);

        $this->assertSame(
            $this->str->of("<?php")->ln()->ln()
                ->concat('declare(strict_types=1);')->ln()->ln()
                ->concat('use Lion\Bundle\Interface\MigrationUpInterface;')->ln()
                ->concat('use Lion\Database\Drivers\MySQL;')->ln()
                ->concat('use Lion\Database\Drivers\Schema\MySQL as Schema;')->ln()->ln()
                ->concat("return new class implements MigrationUpInterface\n{")->ln()
                ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
                ->lt()->concat("public function up(): object\n\t{")->ln()
                ->lt()->lt()->concat("return Schema::connection(env('--CONNECTION--', 'lion_database'))")->ln()
                ->lt()->lt()->lt()->concat("->createStoreProcedure('example', function() {")->ln()
                ->lt()->lt()->lt()->lt()->concat("Schema::in()->varchar('name', 25);")->ln()
                ->lt()->lt()->lt()->concat('}, function(MySQL $db) {')->ln()
                ->lt()->lt()->lt()->lt()->concat('$db->table(' . "''" . ')->insert([' . "'name' => ''" . ']);')->ln()
                ->lt()->lt()->lt()->concat('})')->ln()
                ->lt()->lt()->lt()->concat("->execute();")->ln()
                ->lt()->concat("}")->ln()
                ->concat("};")
                ->get(),
            $storeProcedureBody
        );
    }
}
