<?php

namespace App\Traits\Framework;

use LionHelpers\Arr;
use LionHelpers\Str;

trait PostmanCollector {

    private static array $postman_functions = [];

    public static function init(string $host) {
        self::$postman_functions['params'] = [
            'host' => [
                'url' => $host,
                'params' => [
                    // 'protocol' => "",
                    'host' => ["{{base_url}}"]
                ]
            ],
            'items' => [],
        ];

        // self::createHost();
        // self::createProtocol();
        // self::createPort();
    }

    private static function createPort(): void {
        $after_host = Str::of(self::$postman_functions['params']['host']['url'])->after("://");
        $after_host = Str::of($after_host)->after(":");

        if (preg_match("/^([0-9]+)(\s[0-9]+)*$/", $after_host)) {
            self::$postman_functions['params']['host']['params']['port'] = $after_host;
        }
    }

    private static function createHost(): void {
        $after_host = Str::of(self::$postman_functions['params']['host']['url'])->after("://");
        $before_host = Str::of($after_host)->before(":");
        self::$postman_functions['params']['host']['params']['host'] = explode(".", $before_host);
    }

    private static function createProtocol(): void {
        self::$postman_functions['params']['host']['params']['protocol'] = Str::of(
            self::$postman_functions['params']['host']['url']
        )->before("://");
    }

    private static function addGet(string $name, string $route): array {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "GET",
                'header' => [
                    ["key" => "Content-Type", "value" => "application\/json"]
                ],
                'url' => [
                    ...self::$postman_functions['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    private static function addDelete(string $name, string $route): array {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "DELETE",
                'header' => [
                    [
                        'key' => "Content-Type",
                        'value' => "application/json",
                        'type' => "text"
                    ]
                ],
                'body' => [
                    'mode' => "raw",
                    'raw' => '{"example":"example_value"}',
                    'options' => [
                        'raw' => [
                            'language' => "json"
                        ]
                    ]
                ],
                'url' => [
                    ...self::$postman_functions['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    private static function addPost(string $name, string $route): array {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "POST",
                'header' => [
                    [
                        'key' => "Content-Type",
                        'value' => "multipart/form-data"
                    ]
                ],
                'body' => [
                    'mode' => "formdata",
                    'formdata' => [
                        [
                            'key' => "example",
                            'value' => "example_value"
                        ]
                    ]
                ],
                'url' => [
                    ...self::$postman_functions['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    private static function addPut(string $name, string $route): array {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "PUT",
                'header' => [
                    [
                        'key' => "Content-Type",
                        'value' => "application/json",
                        'type' => "text"
                    ]
                ],
                'body' => [
                    'mode' => "raw",
                    'raw' => '{"example":"example_value"}',
                    'options' => [
                        'raw' => [
                            'language' => "json"
                        ]
                    ]
                ],
                'url' => [
                    ...self::$postman_functions['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    public static function add(string $name, string $route, string $method) {
        $method = Str::of($method)->upper();
        $name = Arr::of(explode("/", $name))->join("-");

        if ($method === "POST") {
            array_push(self::$postman_functions['params']['items'], self::addPost($name, $route));
        } elseif ($method === "GET") {
            array_push(self::$postman_functions['params']['items'], self::addGet($name, $route));
        } elseif ($method === "PUT") {
            array_push(self::$postman_functions['params']['items'], self::addPut($name, $route));
        } elseif ($method === "DELETE") {
            array_push(self::$postman_functions['params']['items'], self::addDelete($name, $route));
        } else {
            array_push(self::$postman_functions['params']['items'], self::addGet($name, $route));
        }
    }

    public static function get(): array {
        return self::$postman_functions['params']['items'];
    }

    public static function reverseArray(array $items): array {
        $new_items = [];

        foreach ($items as $key => $route) {
            $new_items = Arr::of($new_items)->prepend($route);
        }

        return $new_items;
    }

}