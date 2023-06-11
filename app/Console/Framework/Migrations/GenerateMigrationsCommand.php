<?php

namespace App\Console\Framework\Migrations;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use LionSQL\Drivers\MySQL\MySQL as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMigrationsCommand extends Command {

	protected static $defaultName = "migrate:generate";
    private array $connections;
    private array $list;

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->connections = DB::getConnections();
        $this->list = [];

        foreach (arr->of($this->connections['connections'])->keys()->get() as $key => $connection) {
            // delete migrations
            if (isSuccess(Store::exist("database/Migrations/{$connection}/"))) {
                foreach (Store::view("database/Migrations/{$connection}/") as $key => $file) {
                    $validate_extension = Store::validate([$file], ["php"]);

                    if (isSuccess($validate_extension)) {
                        Store::remove($file);
                    }
                }
            }

            // add entities
            $tables = DB::connection($connection)->show()->full()->tables()->where(DB::equalTo("Table_Type"), 'BASE TABLE')->getAll();
            $views = DB::connection($connection)->show()->full()->tables()->where(DB::equalTo("Table_Type"), 'VIEW')->getAll();
            $procedures = DB::connection($connection)->show()->procedure()->status()->where(DB::equalTo("Db"), $connection)->getAll();

            if (!isset($tables->status)) {
                $this->list[$connection]['tables'] = $tables;
            }

            if (!isset($views->status)) {
                $this->list[$connection]['views'] = $views;
            }

            if (!isset($procedures->status)) {
                $this->list[$connection]['procedures'] = $procedures;
            }
        }
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command required to generate migrations of an existing database");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        foreach ($this->connections['connections'] as $keyConnection => $connection) {
            foreach ($this->list[$keyConnection] as $keyType => $type) {
                if ($keyType === "tables") {
                    foreach ($type as $key => $table) {
                        $table_name = $table->{"Tables_in_{$connection['dbname']}"};
                        $columns_db = DB::connection($connection['dbname'])
                            ->show()
                            ->full()
                            ->columns()
                            ->from($table_name)
                            ->getAll();
                        $foreigns = DB::connection($connection['dbname'])
                            ->fetchMode(\PDO::FETCH_ASSOC)
                            ->table("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", true)
                            ->select("COLUMN_NAME", "REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME")
                            ->where(DB::equalTo("TABLE_SCHEMA"), $connection['dbname'])
                            ->and(DB::equalTo("TABLE_NAME"), $table_name)
                            ->and("REFERENCED_TABLE_NAME")
                            ->isNotNull()
                            ->getAll();
                        $foreigns = !isset($foreigns->status) ? arr->of($foreigns)->keyBy('COLUMN_NAME') : [];

                        $size_columns_db = arr->of($columns_db)->length();
                        $columns = "";

                        $addColumns = function(string $columns, int $key, object $column_db, ?array $info_foreign) use ($table_name, $size_columns_db): string {
                            $column_name = str_replace("{$table_name}_", "", $column_db->Field);
                            $type = explode("(", $column_db->Type);

                            $column_type = $type[0];
                            $column_length = (isset($type[1]) ? (int) explode(")", $type[1])[0] : 0);
                            $column_null = ($column_db->Null === "NO" ? false : true);
                            $column_unique = ($column_db->Key === "UNI" ? true : false);
                            $column_options = (isset($type[1]) ? explode(")", $type[1])[0] : "");
                            $column_foreign = is_array($info_foreign)
                                ? "['table' => '{$info_foreign['REFERENCED_TABLE_NAME']}', 'column' => '{$info_foreign['REFERENCED_COLUMN_NAME']}']"
                                : null;

                            $array_options = "'type' => '{$column_type}'";
                            $array_options .= ($column_length > 0 ? ", 'length' => {$column_length}" : "");
                            $array_options .= (!$column_null ? ", 'null' => false" : ", 'null' => true");
                            $array_options .= (!$column_unique ? "" : ", 'unique' => true");
                            $array_options .= ($column_type === "enum" ? ", 'options' => [{$column_options}]" : "");
                            $array_options .= ($column_foreign != null ? ", 'foreign-key' => {$column_foreign}" : "");
                            $array_options .= ", 'comment' => '{$column_db->Comment}'";
                            $array_options .= ", 'default' => ''";

                            if ($key === ($size_columns_db - 1)) {
                                $columns .= str->of("->column('{$column_name}', [{$array_options}])")->get();
                            } else {
                                $columns .= str->of("->column('{$column_name}', [{$array_options}])")->ln()->lt()->lt()->lt()->get();
                            }

                            return $columns;
                        };

                        foreach ($columns_db as $key => $column_db) {
                            $info_foreign = isset($foreigns[$column_db->Field]) ? $foreigns[$column_db->Field] : null;

                            if ($column_db->Key === "PRI") {
                                $columns .= str->of("->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])")->ln()->lt()->lt()->lt()->get();
                            } else {
                                $columns = $addColumns($columns, $key, $column_db, $info_foreign);
                            }
                        }

                        $path = "database/Migrations/{$connection['dbname']}/";
                        Store::folder($path);
                        $migration_name = str->of($path)->concat($connection['dbname'])->concat("_")->concat($table_name)->replace("-", "_")->replace(" ", "_")->lower()->trim()->get();

                        ClassPath::new($migration_name, "php");
                        ClassPath::add(str->of(ClassPath::getTemplateCreateTable())->replace('$table = "table"', '$table = "' . $table_name . '"')->replace('env->DB_NAME', "'{$connection['dbname']}'")->replace("\t\t\t->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])\n", "")->replace("->column('name', ['type' => 'varchar', 'null' => true, 'default' => 'unnamed'])", $columns)->get());
                        ClassPath::force();
                        ClassPath::close();

                        $output->write("\033[1;33m");
                        $output->write("\t>>");
                        $output->write("\033[0m");
                        $output->writeln("  <info>Migration '{$migration_name}' has been generated</info>");
                    }
                }
            }
        }

		return Command::SUCCESS;
	}

}
