<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Migrations;

use Lion\Helpers\Str;

class Migration
{
    private Str $str;

    public function __construct()
    {
        $this->str = new Str();
    }

    public function getTableBody(): string
    {
        return $this->str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("use Lion\Bundle\Interface\MigrationRunInterface;")->ln()
            ->concat("use Lion\Bundle\Interface\MigrationUpInterface;")->ln()
            ->concat("use Lion\Bundle\Traits\Migration;")->ln()
            ->concat("use Lion\Database\Drivers\Schema\MySQL as DB;")->ln()->ln()
            ->concat("return new class implements MigrationRunInterface, MigrationUpInterface\n{")->ln()
            ->lt()->concat('use Migration;')->ln()->ln()
            ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
            ->lt()->concat("public function up(): object\n\t{")->ln()
            ->lt()->lt()->concat("return DB::connection('--CONNECTION--')")->ln()
            ->lt()->lt()->lt()->concat("->createTable('', function() {")->ln()
            ->lt()->lt()->lt()->lt()->concat("DB::int('id')->notNull()->autoIncrement()->primaryKey();")->ln()
            ->lt()->lt()->lt()->concat('})')->ln()
            ->lt()->lt()->lt()->concat("->execute();")->ln()
            ->lt()->concat("}")->ln()->ln()
            ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
            ->lt()->concat("public function run(): array\n\t{")->ln()
            ->lt()->lt()->concat("return [\n\t\t\t'columns' => [],\n\t\t\t'rows' => []\n\t\t];")->ln()
            ->lt()->concat("}")->ln()
            ->concat("};")
            ->get();
    }

    public function getViewBody(): string
    {
        return $this->str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("use Lion\Bundle\Interface\MigrationUpInterface;")->ln()
            ->concat("use Lion\Database\Drivers\MySQL;")->ln()
            ->concat("use Lion\Database\Drivers\Schema\MySQL as Schema;")->ln()->ln()
            ->concat("return new class implements MigrationUpInterface\n{")->ln()
            ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
            ->lt()->concat("public function up(): object\n\t{")->ln()
            ->lt()->lt()->concat("return Schema::connection('--CONNECTION--')")->ln()
            ->lt()->lt()->lt()->concat("->createView('', " . 'function(MySQL $db) {')->ln()
            ->lt()->lt()->lt()->lt()->concat('$db->table' . "('table')->select();")->ln()
            ->lt()->lt()->lt()->concat("})")->ln()
            ->lt()->lt()->lt()->concat("->execute();")->ln()
            ->lt()->concat("}")->ln()
            ->concat("};")
            ->get();
    }

    public function getStoreProcedureBody(): string
    {
        return $this->str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat('use Lion\Bundle\Interface\MigrationUpInterface;')->ln()
            ->concat('use Lion\Bundle\Traits\Migration;')->ln()
            ->concat('use Lion\Database\Drivers\MySQL;')->ln()
            ->concat('use Lion\Database\Drivers\Schema\MySQL as Schema;')->ln()->ln()
            ->concat("return new class implements MigrationUpInterface\n{")->ln()
            ->lt()->concat('use Migration;')->ln()->ln()
            ->lt()->concat("/**\n\t * {@inheritdoc}\n\t * */")->ln()
            ->lt()->concat("public function up(): object\n\t{")->ln()
            ->lt()->lt()->concat("return Schema::connection('--CONNECTION--')")->ln()
            ->lt()->lt()->lt()->concat("->createStoreProcedure('', function() {")->ln()
            ->lt()->lt()->lt()->lt()->concat("Schema::in()->varchar('name', 25);")->ln()
            ->lt()->lt()->lt()->concat('}, function(MySQL $db) {')->ln()
            ->lt()->lt()->lt()->lt()->concat('$db->table(' . "''" . ')->insert([' . "'name' => ''" . ']);')->ln()
            ->lt()->lt()->lt()->concat('})')->ln()
            ->lt()->lt()->lt()->concat("->execute();")->ln()
            ->lt()->concat("}")->ln()
            ->concat("};")
            ->get();
    }
}
