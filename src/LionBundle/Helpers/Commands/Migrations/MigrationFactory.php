<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Migrations;

use Lion\Helpers\Str;

/**
 * Factory of the content of the generated migrations
 *
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Helpers\Commands\Migrations
 */
class MigrationFactory
{
    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * @required
     */
    public function setStr(Str $str): MigrationFactory
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Returns the body of the migration of type table
     *
     * @return string
     */
    public function getTableBody(): string
    {
        return $this->str->of("<?php")->ln()->ln()
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
            ->get();
    }

    /**
     * Returns the body of the migration of type view
     *
     * @return string
     */
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
            ->lt()->lt()->concat("return Schema::connection(env('--CONNECTION--', 'lion_database'))")->ln()
            ->lt()->lt()->lt()->concat("->createView('read_example', " . 'function(MySQL $db) {')->ln()
            ->lt()->lt()->lt()->lt()->concat('$db->table' . "('table')->select();")->ln()
            ->lt()->lt()->lt()->concat("})")->ln()
            ->lt()->lt()->lt()->concat("->execute();")->ln()
            ->lt()->concat("}")->ln()
            ->concat("};")
            ->get();
    }

    /**
     * Returns the body of the migration of type store-procedure
     *
     * @return string
     */
    public function getStoreProcedureBody(): string
    {
        return $this->str->of("<?php")->ln()->ln()
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
            ->get();
    }
}
