<?php

namespace App\Traits\Framework;

use LionHelpers\Arr;
use LionHelpers\Str;

trait ClassPath {

    private static $content;

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