<?php

namespace App\Traits\Framework;

use LionHelpers\Arr;
use LionHelpers\Str;

trait ClassPath {

    private static $content;

    public static function addNewObjectClass(string $class): string {
        return '$' . Str::of($class)->lower() . " = new {$class}();\n\n";
    }

    public static function addPropierty($type, $field): string {
        return "\tprivate ?" . self::addType($type) . ' $' . self::normalizeField($field, true) . " = null;\n";
    }

    public static function addSetFunctionIsset(string $class, string $field, string $request_field): string {
        return "\t\t{$class}->set" . self::normalizeClass($field) . "(\n\t\t\tisset({$request_field}) ? {$request_field} : null\n\t\t);\n";
    }

    public static function addSetFunction(string $column, string $field, string $class) {
        return "\tpublic function set" . self::normalizeClass($field) . "(?" . self::addType($column) . ' $' . "{$field}): {$class}";
    }

    public static function cleanField(string $field): string {
        $field = Str::of($field)->replace(" ", "_")->get();
        return Str::of($field)->replace("-", "_")->get();
    }

    public static function addType(string $type): string {
        if (Str::of($type)->test("/^int|bigint/")) {
            return "int";
        } elseif (Str::of($type)->test("/^float/")) {
            return "float";
        } else {
            return "string";
        }
    }

    public static function normalizeClass(string $class): string {
        $class = Str::of($class)->replace("_", " ")->trim();
        $class = Str::of($class)->headline();
        return Str::of($class)->replace(" ", "")->trim();
    }

    public static function normalizeField(string $field): string {
        $field = Str::of($field)->replace("_", " ")->trim();
        $field = Str::of($field)->replace("-", " ")->trim();
        $field = Str::of($field)->lower();
        return Str::of($field)->replace(" ", "_")->trim();
    }

    public static function export(string $default_path, string $class_name): array {
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

    public static function new(string $file_name, string $ext): void {
        self::$content = fopen("{$file_name}.{$ext}", "w+b");
    }

    public static function create($url_folder, $class): void {
        self::$content = fopen("{$url_folder}/{$class}.php", "w+b");
    }

    public static function add(string $line): void {
        fwrite(self::$content, $line);
    }

    public static function force(): void {
        fflush(self::$content);
    }

    public static function close(): void {
        fclose(self::$content);
    }

}