<?php

namespace App\Traits\Framework;

use LionHelpers\Arr;
use LionHelpers\Str;

trait PostmanCollector {

    private static array $postman = [];

    public static function init(string $host) {
        self::$postman['params'] = [
            'routes' => [],
            'items' => [],
            'host' => [
                'url' => $host,
                'params' => [
                    // 'protocol' => "",
                    'host' => ["{{base_url}}"]
                ]
            ]
        ];
    }

    private static function createPort(): void {
        $after_host = Str::of(self::$postman['params']['host']['url'])->after("://");
        $after_host = Str::of($after_host)->after(":");

        if (preg_match("/^([0-9]+)(\s[0-9]+)*$/", $after_host)) {
            self::$postman['params']['host']['params']['port'] = $after_host;
        }
    }

    private static function createHost(): void {
        $after_host = Str::of(self::$postman['params']['host']['url'])->after("://");
        $before_host = Str::of($after_host)->before(":");
        self::$postman['params']['host']['params']['host'] = explode(".", $before_host);
    }

    private static function createProtocol(): void {
        self::$postman['params']['host']['params']['protocol'] = Str::of(
            self::$postman['params']['host']['url']
        )->before("://");
    }

    private static function addParams(string $method, array $params): array {
        $new_params = [];

        foreach ($params as $key => $param) {
            if ($method === "POST") {
                $new_params[] = [
                    'key' => $param::$field,
                    'value' => '',
                    'type' => "text"
                ];
            } else {
                $new_params[$param::$field] = "";
            }
        }

        return $new_params;
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
                    ...self::$postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    private static function addDelete(string $name, string $route, array $params): array {
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
                    'raw' => json->encode(self::addParams("DELETE", $params)),
                    'options' => [
                        'raw' => [
                            'language' => "json"
                        ]
                    ]
                ],
                'url' => [
                    ...self::$postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    private static function addPost(string $name, string $route, array $params): array {
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
                    'formdata' => self::addParams("POST", $params)
                ],
                'url' => [
                    ...self::$postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    private static function addPut(string $name, string $route, array $params): array {
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
                    'raw' => json->encode(self::addParams("PUT", $params)),
                    'options' => [
                        'raw' => [
                            'language' => "json"
                        ]
                    ]
                ],
                'url' => [
                    ...self::$postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode("/", $route)]
                ]
            ]
        ];
    }

    private static function addRequest($name, $route, $method, $params) {
        if ($method === "POST") {
            return self::addPost($name, $route, $params);
        } elseif ($method === "GET") {
            return self::addGet($name, $route);
        } elseif ($method === "PUT") {
            return self::addPut($name, $route, $params);
        } elseif ($method === "DELETE") {
            return self::addDelete($name, $route, $params);
        } else {
            return self::addGet($name, $route);
        }
    }

    public static function addRoutes(array $routes) {
        foreach($routes as $key_items => $route) {
            foreach ($route as $key_route => $item) {
                self::$postman['params']['routes'][] = [
                    'url' => $key_items === "" ? "/" : $key_items,
                    'method' => $key_route,
                    'params' => isset(rules["/{$key_items}"]) ? rules["/{$key_items}"] : []
                ];
            }
        }
    }

    public static function generateItems() {
        foreach (self::$postman['params']['routes'] as $key_route => $route) {
            $split_all = null;

            if ($route['url'] === "/") {
                $split_all = ["index"];
            } else {
                $split_all = explode('/', $route['url']);
            }

            $reverse = self::reverseArray($split_all);
            $size = count($reverse) - 1;
            $request = null;
            $initial = true;
            $last_array = [];

            foreach ($reverse as $key_split => $split) {
                $name = $split === "" ? "index" : $split;

                if ($key_split === 0) {
                    $request = self::addRequest(
                        $name,
                        $route['url'],
                        $route['method'],
                        $route['params']
                    );

                    if ($key_split === $size) {
                        self::$postman['params']['items'][] = $request;
                    }
                } else {
                    if ($initial) {
                        $initial = false;

                        $last_array = [
                            'name' => $name,
                            'item' => [$request]
                        ];
                    } else {
                        $last_array = [
                            'name' => $name,
                            'item' => [$last_array]
                        ];
                    }

                    if ($key_split === $size) {
                        self::$postman['params']['items'][] = $last_array;
                    }
                }
            }

            $initial = true;
            $last_array = [];
        }
    }

    public static function createCollection(array $items): array {
        $result = [];

        foreach ($items as $json) {
            if (isset($result[$json['name']])) {
                if (isset($json['item'])) {
                    $result[$json['name']]['item'] = array_merge(
                        $result[$json['name']]['item'],
                        $json['item']
                    );
                } else {
                    $result[$json['name']] = array_merge_recursive(
                        $result[$json['name']],
                        $json
                    );
                }
            } else {
                $result[$json['name']] = $json;
            }
        }

        foreach ($result as &$item) {
            if (isset($item['item'])) {
                $item['item'] = self::createCollection($item['item']);
            }
        }

        return array_values($result);
    }

    public static function getRoutes(): array {
        return self::$postman['params']['routes'];
    }

    public static function getItems(): array {
        return self::$postman['params']['items'];
    }

    public static function reverseArray(array $items): array {
        $new_items = [];

        foreach ($items as $key => $route) {
            $new_items = Arr::of($new_items)->prepend($route);
        }

        return $new_items;
    }

}