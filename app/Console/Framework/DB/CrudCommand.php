<?php

namespace App\Console\Framework\DB;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CrudCommand extends Command
{
    use ClassPath, ConsoleOutput;

	protected static $defaultName = "db:crud";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("command to generate controller and model of an entity with their respective CRUD functions")
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity name')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Do you want to use a specific connection?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getArgument("entity");
        $connection = $input->getOption("connection");

        $entity_pascal = str->of($entity)->replace("_", " ")->pascal()->get();
        $connections = DB::getConnections();
        $main_conn = $connection === null ? $connections['default'] : $connection;
        $main_conn_pascal = str->of($main_conn)->replace("_", " ")->pascal()->get();
        $namespace_class = "Database\\Class\\{$main_conn_pascal}\\{$entity_pascal}";
        $list = $this->export("database/class/", "{$main_conn_pascal}/{$entity_pascal}");

        // generate capsule class
        $this->getApplication()->find('db:capsule')->run(
            new ArrayInput(['entity' => $entity, '-c' => $main_conn]),
            $output
        );

        // generate all rules
        $this->getApplication()->find('db:rules')->run(
            new ArrayInput(['entity' => $entity, '-c' => $main_conn]),
            $output
        );

        // generate controller and model
        $this->getApplication()->find('new:controller')->run(
            new ArrayInput([
                'controller' => "{$main_conn_pascal}/{$entity_pascal}Controller",
                '--model' => "{$main_conn_pascal}/{$entity_pascal}Model"
            ]),
            $output
        );

        // modify controllers
        $file_c = "{$entity_pascal}Controller";
        $path_c = "app/Http/Controllers/{$main_conn_pascal}/{$file_c}.php";

        $this->readFileRows($path_c, [
            8 => ['replace' => false, 'content' => "use {$namespace_class};\n\n"],
            20 => ['replace' => true, 'content' => "({$list['class']}::capsule())", 'search' => "()"],
            34 => ['replace' => true, 'content' => "({$list['class']}::capsule())", 'search' => "()"],
            43 => ['replace' => true, 'content' => "({$list['class']}::capsule())", 'search' => "()"],
        ]);

        // modify models
        $columns = DB::connection($main_conn)->show()->columns()->from($entity)->getAll();
        $path_m = "app/Models/{$main_conn_pascal}/{$entity_pascal}Model.php";
        $list_methods = ['create' => "", 'update' => "", 'delete' => ""];
        $methods = $this->generateGetters($columns);

        foreach ($methods as $key => $method) {
            foreach ($method as $keyMethod => $name) {
                $list_methods[$key] .= str->of("\t")
                    ->lt()->lt()->concat('$')
                    ->concat(lcfirst($entity_pascal))
                    ->concat("->")
                    ->concat($name)
                    ->concat(",")->ln()
                    ->get();
            }
        }

        $this->readFileRows($path_m, [
            6 => [ // namespace
                'replace' => false,
                'content' => "\nuse {$namespace_class};\n"
            ],
            11 => [ // parameter create entity
                'replace' => true,
                'content' => "({$entity_pascal} $" . lcfirst($entity_pascal) . ")", 'search' => '()'
            ],
            13 => [ // values create entity
                'replace' => true,
                'multiple' => [
                    ['content' => "'create_{$entity}'", 'search' => "''"],
                    ['content' => "[\n{$list_methods['create']}\t\t]", 'search' => '[]']
                ]
            ],
            19 => [ // read entity
                'replace' => true,
                'multiple' => [
                    // ['content' => "table", 'search' => "view"],
                    ['content' => "'read_{$entity}'", 'search' => "''"]
                ]
            ],
            22 => [ // parameter update entity
                'replace' => true,
                'content' => "({$entity_pascal} $" . lcfirst($entity_pascal) . ")", 'search' => '()'
            ],
            24 => [ // values update entity
                'replace' => true,
                'multiple' => [
                    ['content' => "'update_{$entity}'", 'search' => "''"],
                    ['content' => "[\n{$list_methods['update']}\t\t]", 'search' => '[]']
                ]
            ],
            28 => [ // parameter delete entity
                'replace' => true,
                'content' => "({$entity_pascal} $" . lcfirst($entity_pascal) . ")", 'search' => '()'
            ],
            30 => [ // values update entity
                'replace' => true,
                'multiple' => [
                    ['content' => "'delete_{$entity}'", 'search' => "''"],
                    ['content' => "[\n{$list_methods['delete']}\t\t]", 'search' => '[]']
                ]
            ],
        ]);

        // modify database
        $generate_params = function(array $columns, string $method): array {
            $items = ['values' => [], 'params' => [], 'columns' => []];

            foreach ($columns as $key => $column) {
                if ($method === "create" && $column->Key != "PRI") {
                    $items['params'][] = "IN _{$column->Field} " . strtoupper($column->Type);
                    $items['values'][] = "_{$column->Field}";
                    $items['columns'][] = $column->Field;
                }

                if ($method === "update") {
                    if ($key === (count($columns) - 1)) {
                        $items['params'][] = "IN _{$column->Field} " . strtoupper($column->Type);
                        $items['columns'][] = $column->Field;
                        $items['values'][] = "_{$column->Field}";

                        $items = [
                            'params' => [
                                ...arr->of($items['params'])->where(fn($value, $key) => $key != 0),
                                $items['params'][0]
                            ],
                            'values' => [
                                ...arr->of($items['values'])->where(fn($value, $key) => $key != 0),
                                $items['values'][0]
                            ],
                            'columns' => [
                                ...arr->of($items['columns'])->where(fn($value, $key) => $key != 0),
                                $items['columns'][0]
                            ]
                        ];
                    } else {
                        $items['params'][] = "IN _{$column->Field} " . strtoupper($column->Type);
                        $items['values'][] = "_{$column->Field}";
                        $items['columns'][] = $column->Field;
                    }
                }

                if ($method === "delete" && $column->Key === "PRI") {
                    $items['params'][] = "IN _{$column->Field} " . strtoupper($column->Type);
                    $items['values'][] = "_{$column->Field}";
                    $items['columns'][] = $column->Field;
                }
            }

            return $items;
        };

        $generate_list = function(array $columns, string $method): array {
            $items = [];

            foreach ($columns as $key => $column) {
                if ($method === "create" && $column->Key != "PRI") {
                    $items[$column->Field] = "";
                }

                if ($method === "update") {
                    $items[$column->Field] = "";
                }

                if ($method === "delete" && $column->Key === "PRI") {
                    $items[$column->Field] = "";
                }
            }

            return $items;
        };

        foreach (["create", "read", "update", "delete"] as $key => $method) {
            $file = "";
            $sql = null;

            if ($method === "read") {
                $str_file = Store::get(storage_path("framework/templates/SQL/create_view.sql", false));
                $sql = DB::connection($main_conn)->table($entity)->select()->getQueryString();

                $file = str->of($str_file)
                    ->replace("--DATABASE--", $main_conn)
                    ->replace("--VIEW--", "{$method}_{$entity}")
                    ->replace("--SQL--", $sql->data->sql['query'])
                    ->get();

                DB::connection($main_conn)->query($file)->execute();
            } elseif ($method === "create") {
                $str_file = Store::get(storage_path("framework/templates/SQL/create_procedure.sql", false));
                $values = $generate_params($columns, $method);
                $sql = "INSERT INTO {$entity} (" . arr->of($generate_list($columns, $method))->keys()->join(",") . ") VALUES (" . arr->of($values['values'])->join(",") . ")";

                $file = str->of($str_file)
                    ->replace("--DATABASE--", $main_conn)
                    ->replace("--PROCEDURE--", "{$method}_{$entity}")
                    ->replace("--PARAMS--", arr->of($values['params'])->join(","))
                    ->replace("--SQL--", $sql)
                    ->get();

                DB::connection($main_conn)->query($file)->execute();
            } elseif ($method === "update") {
                $str_file = Store::get(storage_path("framework/templates/SQL/create_procedure.sql", false));
                $values = $generate_params($columns, $method);
                $new_values = "";

                $combine = array_combine($values['columns'], $values['values']);
                $i = 0;
                foreach ($combine as $key => $value) {
                    if ($i === (count($combine) - 1)) {
                        $new_values .= " WHERE {$key}=$value";
                    } else {
                        if ($i === (count($combine) - 2)) {
                            $new_values .= "{$key}=$value";
                        } else {
                            $new_values .= "{$key}=$value,";
                        }
                    }

                    $i++;
                }

                $file = str->of($str_file)
                    ->replace("--DATABASE--", $main_conn)
                    ->replace("--PROCEDURE--", "{$method}_{$entity}")
                    ->replace("--PARAMS--", arr->of($values['params'])->join(","))
                    ->replace("--SQL--", "UPDATE {$entity} SET {$new_values}")
                    ->get();

                DB::connection($main_conn)->query($file)->execute();
            } elseif ($method === "delete") {
                $file = "";
                $str_file = Store::get(storage_path("framework/templates/SQL/create_procedure.sql", false));
                $values = $generate_params($columns, $method);

                if (arr->of($values['values'])->length() > 0) {
                    $file = str->of($str_file)
                        ->replace("--DATABASE--", $main_conn)
                        ->replace("--PROCEDURE--", "{$method}_{$entity}")
                        ->replace("--PARAMS--", arr->of($values['params'])->join(","))
                        ->replace("--SQL--", "DELETE FROM {$entity} WHERE {$values['columns'][0]}={$values['values'][0]}")
                        ->get();

                    DB::connection($main_conn)->query($file)->execute();
                } else {
                    $i = 0;
                    $new_values = "";

                    foreach ($columns as $key => $column) {
                        if ($i === 0) {
                            $new_values .= " WHERE {$column->Field}=_{$column->Field}";
                        } else {
                            $new_values .= " AND {$column->Field}=_{$column->Field}";
                        }

                        $i++;
                    }

                    DB::connection($main_conn)->query("DELETE FROM {$entity} " . $new_values)->execute();
                }
            }
        }

        $output->writeln($this->infoOutput("\t>>  CRUD: crud has been generated for the '{$entity}' entity"));
        return Command::SUCCESS;
    }
}
