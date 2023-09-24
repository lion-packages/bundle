<?php

declare(strict_types=1);

namespace App\Traits\Framework;

use LionHelpers\Arr;
use LionHelpers\Str;

trait ClassPath
{
    private static $content;

    public static function getTemplateCreateProcedure(): string
    {
        return str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("use App\Traits\Framework\Faker;")->ln()
            ->concat("use LionDatabase\Drivers\MySQL\MySQL as DB;")->ln()
            ->concat("use LionDatabase\Drivers\MySQL\Schema;")->ln()->ln()
            ->concat("return new class\n{")->ln()
            ->lt()->concat("use Faker;")->ln()->ln()
            ->lt()->concat('private string $procedure = "procedure";')->ln()->ln()
            ->lt()->concat("public function getMigration(): array\n\t{")->ln()
            ->lt()->lt()->concat('return ["type" => "PROCEDURE", "procedure" => $this->procedure, "connection" => env->DB_NAME];')->ln()
            ->lt()->concat("}")->ln()->ln()
            ->lt()->concat("public function execute(): object\n\t{")->ln()
            ->lt()->lt()->concat("return Schema::connection(env->DB_NAME)")->ln()
            ->lt()->lt()->lt()->concat('->procedure($this->procedure)')->ln()
            ->lt()->lt()->lt()->concat("->create()")->ln()
            ->lt()->lt()->lt()->concat('->groupQueryParams(function(Schema $schema) {')->ln()
            ->lt()->lt()->lt()->lt()->concat('$schema')->ln()
            ->lt()->lt()->lt()->lt()->lt()->concat("->in()->int('_id', 11)->end(',')")->ln()
            ->lt()->lt()->lt()->lt()->lt()->concat("->in()->varchar('_name', 45);")->ln()
            ->lt()->lt()->lt()->concat("})")->ln()
            ->lt()->lt()->lt()->concat('->groupQueryBegin(function(DB $db) {')->ln()
            ->lt()->lt()->lt()->lt()->concat('$db->table("table")->insert(["name" => "_name"])->end();')->ln()
            ->lt()->lt()->lt()->lt()->concat('// $tb->table("table")->update(["name" => "_name"])->where($db->equalTo("id"), "_id")->end();')->ln()
            ->lt()->lt()->lt()->concat("})")->ln()
            ->lt()->lt()->lt()->concat("->execute();")->ln()
            ->lt()->concat("}")->ln()->ln()
            ->lt()->concat("public function insert(): array\n\t{")->ln()
            ->lt()->lt()->concat('return ["rows" => []];')->ln()
            ->lt()->concat("}")->ln()
            ->concat("};")
            ->get();
    }

    public static function getTemplateCreateView(): string
    {
        return str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("use LionDatabase\Drivers\MySQL\MySQL as DB;")->ln()
            ->concat("use LionDatabase\Drivers\MySQL\Schema;")->ln()->ln()
            ->concat("return new class\n{")->ln()->ln()
            ->lt()->concat("public function getMigration(): array\n\t{")->ln()
            ->lt()->lt()->concat('return ["type" => "VIEW", "connection" => env->DB_NAME];')->ln()
            ->lt()->concat("}")->ln()->ln()
            ->lt()->concat("public function execute(): object\n\t{")->ln()
            ->lt()->lt()->concat("return Schema::connection(env->DB_NAME)")->ln()
            ->lt()->lt()->lt()->concat('->view("view")')->ln()
            ->lt()->lt()->lt()->concat("->create()")->ln()
            ->lt()->lt()->lt()->concat('->groupQuery(function(DB $db) {')->ln()
            ->lt()->lt()->lt()->lt()->concat('$db->table("table")->select();')->ln()
            ->lt()->lt()->lt()->concat("})")->ln()
            ->lt()->lt()->lt()->concat("->execute();")->ln()
            ->lt()->concat("}")->ln()
            ->concat("};")
            ->get();
    }

    public static function getTemplateCreateTable(): string
    {
        return str->of("<?php")->ln()->ln()
            ->concat('declare(strict_types=1);')->ln()->ln()
            ->concat("use App\Traits\Framework\Faker;")->ln()
            ->concat("use LionDatabase\Drivers\MySQL\Schema;")->ln()->ln()
            ->concat("return new class\n{")->ln()
            ->lt()->concat("use Faker;")->ln()->ln()
            ->lt()->concat('private string $table = "table";')->ln()->ln()
            ->lt()->concat("public function getMigration(): array\n\t{")->ln()
            ->lt()->lt()->concat('return ["type" => "TABLE", "table" => $this->table, "connection" => env->DB_NAME, "index" => null];')->ln()
            ->lt()->concat("}")->ln()->ln()
            ->lt()->concat("public function execute(): object\n\t{")->ln()
            ->lt()->lt()->concat("return Schema::connection(env->DB_NAME)")->ln()
            ->lt()->lt()->lt()->concat('->table($this->table, true)')->ln()
            ->lt()->lt()->lt()->concat("->create()")->ln()
            ->lt()->lt()->lt()->concat("->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])")->ln()
            ->lt()->lt()->lt()->concat("->column('name', ['type' => 'varchar', 'null' => true, 'default' => 'unnamed'])")->ln()
            ->lt()->lt()->lt()->concat("->execute();")->ln()
            ->lt()->concat("}")->ln()->ln()
            ->lt()->concat("public function insert(): array\n\t{")->ln()
            ->lt()->lt()->concat('return ["columns" => [], "rows" => []];')->ln()
            ->lt()->concat("}")->ln()
            ->concat("};")
            ->get();
    }

    public static function generateGetters(array $columns): array
    {
        $methods = ['create' => [], 'update' => [], 'delete' => []];

        foreach (['create', 'update', 'delete'] as $keyMethod => $method) {
            foreach ($columns as $keyColumn => $column) {
                $field = self::cleanField($column->Field);
                $getter = "get" . self::normalizeClass($field) . "()";

                if ($method === "create" && $column->Key != "PRI") {
                    $methods[$method][] = $getter;
                }

                if ($method === "update") {
                    $methods[$method][] = $getter;
                }

                if ($method === "delete" && $column->Key === "PRI") {
                    $methods[$method][] = $getter;
                }
            }
        }

        foreach ($columns as $key => $column) {
            if ($column->Key === "PRI") {
                $methods['update'] = [
                    ...arr->of($methods['update'])->where(fn($value, $key) => $key != 0),
                    $methods['update'][0]
                ];
            }
        }

        return $methods;
    }

    private static function increment(array $row, int $increment): int
    {
        if (str->of($row['content'])->contains(["\n"]) !== false) {
            $increment += (substr_count($row['content'], "\n") - 1);
        }

        return $increment;
    }

    private static function replaceContent(array $row, string $modified_line, string $original_line): string
    {
        if ($row['search'] === "--all-elem--") {
            $modified_line = str_pad($row['content'], strlen($original_line));
        } else {
            $new_line = str->of($original_line)->replace($row['search'], $row['content'])->get();
            $modified_line = str_pad($new_line, strlen($original_line));
        }

        return $modified_line;
    }

    public static function readFileRows($path, array $rows): void
    {
        $increment = 0;

        foreach ($rows as $key => $row) {
            $file = fopen($path, "r+");
            $rows_file = file($path);

            if ($key >= 1 && $key <= count($rows_file)) {
                $total = ($key - 1) + $increment;
                $original_line = $rows_file[$total];
                $modified_line = "";

                if ($row['replace'] === false) {
                    $modified_line = str_pad($row['content'], strlen($original_line));
                    $increment = self::increment($row, $increment);
                } else {
                    if (isset($row['multiple'])) {
                        foreach ($row['multiple'] as $key => $content) {
                            $modified_line = self::replaceContent(
                                $content,
                                $modified_line,
                                ($key === 0 ? $original_line : $modified_line)
                            );

                            $increment = self::increment($content, $increment);
                        }
                    } else {
                        $modified_line = self::replaceContent($row, $modified_line, $original_line);
                        $increment = self::increment($row, $increment);
                    }
                }

                fseek($file, 0);
                for ($i = 0; $i < count($rows_file); $i++) {
                    if ($i == $total) {
                        fwrite($file, $modified_line);
                    } else {
                        fwrite($file, $rows_file[$i]);
                    }
                }
            }

            fclose($file);
        }
    }

    public static function generateFunctionsModel(string $method, string $model, bool $last = false): string
    {
        if ($method === "read") {
            return str->of("")->lt()
                ->concat("public function ")
                ->concat($method)
                ->concat($model)
                ->replace("Model", "")
                ->replace("model", "")
                ->concat("DB")
                ->concat("(): array|object\n\t{")->ln()
                ->lt()->lt()->concat("return DB::view('')->select()->getAll();")->ln()
                ->lt()->concat("}")->ln()
                ->concat(!$last ? "\n" : '')
                ->get();
        }

        return str->of("")
            ->lt()->concat("public function ")
            ->concat($method)
            ->concat($model)
            ->replace("Model", "")
            ->replace("model", "")
            ->concat("DB")
            ->concat("(): object\n\t{")->ln()
            ->lt()->lt()->concat("return DB::call('', [])->execute();")->ln()
            ->lt()->concat("}")->ln()
            ->concat(!$last ? "\n" : '')
            ->get();
    }

    public static function generateFunctionsController(
        string $method,
        string $controller,
        bool $last = false,
        ?string $model = null
    ): string
    {
        if ($model === null) {
            return str->of("")
                ->lt()->concat("public function")->spaces(1)
                ->concat($method)
                ->concat($controller)
                ->replace("Controller", "")
                ->replace("controller", "")
                ->concat((in_array($method, ['update', 'delete']) ? '(string $id): ' : '(): '))
                ->concat($method === 'read' ? 'array|object' : 'object')->ln()
                ->lt()->concat('{')->ln()
                ->lt()->lt()->concat("return success();")->ln()
                ->lt()->concat("}")->ln()
                ->concat(!$last ? "\n" : '')
                ->get();
        }

        $model_method = str->of($method)
            ->concat(ucwords($model))
            ->replace("Model", "")
            ->replace("model", "")
            ->concat("DB();")
            ->get();

        if ($method === "read") {
            return str->of("")
                ->lt()->concat("public function")->spaces(1)
                ->concat($method)
                ->concat($controller)
                ->replace("Controller", "")
                ->replace("controller", "")
                ->concat("(): array|object\n\t{")->ln()
                ->lt()->lt()->concat('return $this->' . $model . '->')
                ->concat($model_method)->ln()
                ->lt()->concat("}")->ln()
                ->concat(!$last ? "\n" : '')
                ->get();
        } else {
            return str->of("")
                ->lt()->concat("public function ")
                ->concat($method)
                ->concat($controller)
                ->replace("Controller", "")
                ->replace("controller", "")
                ->concat(
                    in_array($method, ['update', 'delete'])
                        ? ('(string $id): object ' . "\n\t{\n")
                        : ('(): object ' . "\n\t{\n")
                )
                ->lt()->lt()->concat('$res_' . $method . ' = $this->' . $model . "->")
                ->concat($model_method)->ln()->ln()
                ->lt()->lt()->concat('return isError($res_'. $method . ')')->ln()
                ->lt()->lt()->lt()->concat('? error()')->ln()
                ->lt()->lt()->lt()->concat(': success();')->ln()
                ->lt()->concat("}")->ln()
                ->concat(!$last ? "\n" : '')
                ->get();
        }
    }

    public static function addNewObjectClass(string $class): string
    {
        return '$' . str->of(lcfirst($class))->trim()->get() . " = new {$class}();\n\n";
    }

    public static function addPropierty($type, $field): string
    {
        return "\tprivate ?" . self::addType($type) . ' $' . self::normalizeField($field, true) . " = null;\n";
    }

    public static function addSetFunctionIsset(string $class, string $field, string $request_field): string
    {
        return "\t\t" . trim(lcfirst($class)) . "->set" . self::normalizeClass($field) . "(\n\t\t\tisset({$request_field}) ? {$request_field} : null\n\t\t);\n";
    }

    public static function addSetFunction(string $column, string $field, string $class): string
    {
        return "\tpublic function set" . self::normalizeClass($field) . "(?" . self::addType($column) . ' $' . "{$field}): {$class}";
    }

    public static function cleanField(string $field): string
    {
        return str->of($field)->replace(" ", "_")->replace("-", "_")->get();
    }

    public static function addType(string $type): string
    {
        if (Str::of($type)->test("/^int|bigint/")) {
            return "int";
        } elseif (Str::of($type)->test("/^float/")) {
            return "float";
        } else {
            return "string";
        }
    }

    public static function normalizeClass(string $class): string
    {
        return str->of($class)
            ->replace("_", " ")
            ->replace("-", " ")
            ->trim()
            ->headline()
            ->replace(" ", "")
            ->trim()
            ->get();
    }

    public static function normalizeField(string $field): string
    {
        return str->of($field)
            ->replace("_", " ")
            ->trim()
            ->replace("-", " ")
            ->trim()
            ->lower()
            ->replace(" ", "_")
            ->trim()
            ->get();
    }

    public static function export(string $default_path, string $class_name): array
    {
        $namespace = "";
        $separate = explode("/", "{$default_path}{$class_name}");
        $count = Arr::of($separate)->length();
        $list = [];

        foreach ($separate as $key => $part) {
            if ($key === ($count - 1)) {
                $list = [
                    'namespace' => $namespace,
                    'class' => ucwords($part)
                ];
            } elseif ($key === ($count - 2)) {
                $namespace.= ucwords("$part");
            } else {
                $namespace.= ucwords("$part\\");
            }
        }

        return $list;
    }

    public static function new(string $file_name, string $ext): void
    {
        self::$content = fopen("{$file_name}.{$ext}", "w+b");
    }

    public static function create($url_folder, $class): void
    {
        self::$content = fopen("{$url_folder}/{$class}.php", "w+b");
    }

    public static function add(string $line): void
    {
        fwrite(self::$content, $line);
    }

    public static function force(): void
    {
        fflush(self::$content);
    }

    public static function close(): void
    {
        fclose(self::$content);
    }
}
