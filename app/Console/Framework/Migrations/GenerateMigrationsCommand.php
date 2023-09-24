<?php

namespace App\Console\Framework\Migrations;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMigrationsCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "migrate:generate";
    private array $connections;
    private array $list = [];

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->connections = DB::getConnections();

        foreach ($this->connections['connections'] as $nameConnection => $connection) {
            // delete migrations
            $db_pascal = str->of($connection['dbname'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
            $path = "database/Migrations/{$db_pascal}/Tables/";

            if (isSuccess(Store::exist($path))) {
                foreach (Store::view($path) as $key => $file) {
                    if (isSuccess(Store::validate([$file], ["php"]))) {
                        Store::remove($file);
                    }
                }
            }

            // add tables
            $tables = DB::connection($nameConnection)->show()->full()->tables()->where(DB::equalTo("Table_Type"), 'BASE TABLE')->getAll();
            if (!isset($tables->status)) {
                $this->list[$nameConnection]['tables'] = is_object($tables) ? [$tables] : $tables;
            }
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("Command required to generate migrations of an existing database")
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Do you want to set a query limit?', 1200);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cont = 0;
        $limit = $input->getOption("limit");
        $size = arr->of($this->connections['connections'])->length();

        $setQuotes = function($str) {
            $str = str_replace("'", "`", $str);
            return str_replace('"', "`", $str);
        };

        $addRow = function(array $columns_db, array $row) use ($setQuotes): string  {
            $rows_insert = "";

            foreach ($columns_db as $key => $column) {
                if ($row[$column->Field] === null) {
                    $rows_insert .= "null,";
                } else {
                    $type = str->of($column->Type)->lower()->get();
                    $value = $setQuotes($row[$column->Field]);

                    if (str->of($type)->test('/varbinary/i')) {
                        $rows_insert .=  "'0x" . bin2hex($value) . "',";
                    } elseif (str->of($type)->test('/blob/i')) {
                        $rows_insert .=  "'0x" . bin2hex($value) . "',";
                    } elseif (str->of($type)->test("/^int|bigint/")) {
                        $rows_insert .= "{$value},";
                    } else {
                        $rows_insert .= "'{$value}',";
                    }
                }
            }

            return $rows_insert;
        };

        foreach ($this->connections['connections'] as $nameConnection => $connection) {
            $output->writeln($this->warningOutput("\t>>  DATABASE: {$connection['dbname']}"));

            if (isset($this->list[$nameConnection])) {
                if (isset($this->list[$nameConnection]['tables'])) {
                    foreach ($this->list[$nameConnection]['tables'] as $key => $table) {
                        $tbl = $table->{"Tables_in_{$connection['dbname']}"};
                        $table_name = str->of($tbl)->test("/-/") ? "`{$tbl}`" : $tbl;
                        $new_table_name = str->of($table_name)->replace("-", "_")->replace("`", "")->lower()->get();
                        $columns_db = DB::connection($connection['dbname'])->show()->full()->columns()->from($table_name)->getAll();

                        $foreigns = DB::connection($connection['dbname'])
                            ->table("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", true)
                            ->select("COLUMN_NAME", "REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME")
                            ->where(DB::equalTo("TABLE_SCHEMA"), $connection['dbname'])
                            ->and(DB::equalTo("TABLE_NAME"), $table_name)
                            ->and("REFERENCED_TABLE_NAME")
                            ->isNotNull()
                            ->fetchMode(\PDO::FETCH_ASSOC)
                            ->getAll();
                        $foreigns = !isset($foreigns->status) ? arr->of($foreigns)->keyBy('COLUMN_NAME') : [];
                        $size_columns_db = arr->of($columns_db)->length();
                        $columns = "";

                        $addColumns = function(string $columns, int $key, object $column_db, ?array $info_foreign) use ($setQuotes, $new_table_name, $size_columns_db): string {
                            $column_name = str_replace("{$new_table_name}_", "", str->of($column_db->Field)->lower()->get());
                            $type = explode("(", $column_db->Type);

                            $column_type = $type[0];
                            $column_length = (isset($type[1]) ? (int) explode(")", $type[1])[0] : 0);
                            $column_null = ($column_db->Null === "NO" ? false : true);
                            $column_unique = ($column_db->Key === "UNI" ? true : false);
                            $column_options = (isset($type[1]) ? explode(")", $type[1])[0] : "");

                            $column_foreign = null;
                            if ($info_foreign != null) {
                                $column_foreign = "['table' => '{$info_foreign['REFERENCED_TABLE_NAME']}', 'column' => '{$info_foreign['REFERENCED_COLUMN_NAME']}']";
                            }

                            $array_options = "'type' => '{$column_type}'";
                            $array_options .= ($column_length > 0 ? ", 'length' => {$column_length}" : "");
                            $array_options .= (!$column_null ? ", 'null' => false" : ", 'null' => true");
                            $array_options .= (!$column_unique ? "" : ", 'unique' => true");
                            $array_options .= ($column_type === "enum" ? ", 'options' => [{$column_options}]" : "");
                            $array_options .= ($column_foreign != null ? ", 'foreign-key' => {$column_foreign}" : "");
                            $array_options .= ", 'comment' => '{$setQuotes($column_db->Comment)}'";
                            $array_options .= ", 'default' => ''";

                            if ($key === ($size_columns_db - 1)) {
                                $columns .= str->of("->column('{$column_name}', [{$array_options}])")->get();
                            } else {
                                $columns .= str->of("->column('{$column_name}', [{$array_options}])")->ln()->lt()->lt()->lt()->get();
                            }

                            return $columns;
                        };

                        $columns_insert = "";
                        foreach ($columns_db as $key => $column_db) {
                            $info_foreign = isset($foreigns[$column_db->Field]) ? $foreigns[$column_db->Field] : null;

                            if ($column_db->Key === "PRI") {
                                $columns_insert .= "\n\t\t\t\t'{$column_db->Field}',\n";
                                $columns .= str->of("->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])")->ln()->lt()->lt()->lt()->get();
                            } else {
                                $columns_insert .= "\t\t\t\t'{$column_db->Field}',\n";
                                $columns = $addColumns($columns, $key, $column_db, $info_foreign);
                            }
                        }

                        $db_pascal = str->of($connection['dbname'])->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                        $tbl_pascal = str->of($new_table_name)->replace("-", " ")->replace("_", " ")->pascal()->trim()->get();
                        $path = "database/Migrations/{$db_pascal}/Tables/";
                        Store::folder($path);
                        $migration_name = str->of($path)->concat("Table")->concat($tbl_pascal)->get();
                        $info_table = DB::connection($connection['dbname'])->table($table_name)->select()->limit(0, $limit)->fetchMode(\PDO::FETCH_ASSOC)->getAll();

                        $rows_insert = "";
                        if (!isset($info_table->status)) {
                            foreach ($info_table as $key => $row) {
                                $rows_insert .= ("\t\t\t\t[{$addRow($columns_db, (array) $row)}],\n");
                            }
                        }

                        $env_var = array_search($connection['dbname'], (array) env);
                        $this->new($migration_name, "php");
                        $this->add(
                            str->of($this->getTemplateCreateTable())
                                ->replace('$table = "table"', '$table = "' . $new_table_name . '"')
                                ->replace('env->DB_NAME', "env->{$env_var}")
                                ->replace("\t\t\t->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])\n", "")
                                ->replace("->column('name', ['type' => 'varchar', 'null' => true, 'default' => 'unnamed'])", $columns)
                                ->replace('"columns" => [],', "\n\t\t\t'columns' => [{$columns_insert}\t\t\t],\n")
                                ->replace('"rows" => []', "\t\t\t'rows' => [" . ($rows_insert === "" ? "" : "\n" . str_replace(",],", "],", $rows_insert) . "\t\t\t") . "]\n\t\t")
                                ->get()
                        );
                        $this->force();
                        $this->close();
                        $output->writeln($this->successOutput("\t>>  TABLE: Migration '{$migration_name}' has been generated"));
                    }
                }
            } else {
                $output->writeln($this->warningOutput("\t>> NO DATA AVAILABLE"));
            }

            if ($cont < ($size - 1)) {
                $output->writeln("");
            }

            $cont++;
        }

        return Command::SUCCESS;
    }
}
