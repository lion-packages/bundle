<?php

declare(strict_types=1);

namespace App\Traits\Framework;

use LionHelpers\Arr;
use LionHelpers\Str;

trait ClassPath
{
    private static $content;

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


}
