<?php

namespace App\Traits\Framework;

use LionHelpers\Arr;
use LionHelpers\Str;

trait ClassPath {

    private static $content;

    private static function reverseArray(array $items): array {
        $new_items = [];

        foreach ($items as $key => $route) {
            $new_items = Arr::of($new_items)->prepend($route);
        }

        return $new_items;
    }

    public static function addPostmanJsonItems(array $items, string $routes, string $method, bool $initial = false): array {
        $split_routes = explode("/", $routes);
        $size = Arr::of($split_routes)->length();

        $push_items = function(array $items, string $route, string $type = "item", array $value = []) use ($initial, $routes, $method) : array {
            $body = [];

            if ($method === "POST") {
                $body = [
                    'mode' => 'formdata',
                    'formdata' => []
                ];
            } elseif ($method === "PUT") {
                $body = [
                    'mode' => 'raw',
                    'raw' => [],
                    "options" => [
                        "raw" => [
                            "language" => "json"
                        ]
                    ]
                ];
            }

            if ($type === "item") {
                $items = Arr::of($items)->push([
                    'name' => $route,
                    'item' => $value
                ])->get();
            } else {
                $items = Arr::of($items)->push([
                    'name' => $route,
                    'response' => [],
                    'request' => [
                        'method' => $method,
                        'header' => [],
                        'body' => $body,
                        'url' => [
                            'raw' => "{{base_url}}/{$routes}"
                        ]
                    ],
                ])->get();
            }

            return $items;
        };

        $validateIndexInitial = function(array $items, int $key, string $route) use ($initial, $push_items): array {
            if ($key === 0) {
                $items = $push_items(
                    $items,
                    $route,
                    !$initial ? "item" : "request"
                );
            } else {
                $items = $push_items(
                    !$initial ? $items : [],
                    $route,
                    !$initial ? "request" : "item",
                    $items
                );
            }

            return $items;
        };

        $validateIndex = function(array $items_group, int $key, string $route) use ($initial, $size, $push_items): void {
            $push_items(
                $items_group,
                $route,
                $key === ($size - 1) ? "request" : "item",
                $key === ($size - 1) ? [] : $items_group
            );
        };

        if ($initial) {
            foreach (self::reverseArray($split_routes) as $key => $route) {
                $items = $validateIndexInitial($items, $key, $route);
            }
        } else {
            foreach ($items as $key => $routes_group) {
                foreach ($split_routes as $keyRoute => $route) {
                    $validateIndex($routes_group, $keyRoute, $route);
                }
            }
        }

        return $items;
    }

    public static function addNewObjectClass(string $class): string {
        return '$' . Str::of($class)->lower() . " = new {$class}();\n\n";
    }

    public static function addPropierty($type, $field): string {
        return "\tprivate ?" . self::addType($type) . ' $' . $field . " = null;\n";
    }

    public static function addSetFunctionIsset(string $class, string $field, string $request_field): string {
        return "\t\t{$class}->set" . self::normalize($field) . "(\n\t\t\tisset({$request_field}) ? {$request_field} : null\n\t\t);\n";
    }

    public static function addSetFunction(string $column, string $field, string $class) {
        return "\tpublic function set" . self::normalize($field) . "(?" . self::addType($column) . ' $' . "{$field}): {$class}";
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

    public static function normalize(string $class): string {
        $class = Str::of($class)->replace("_", " ")->trim();
        $class = ucwords($class);
        return Str::of($class)->replace(" ", "")->trim();
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