<?php

declare(strict_types=1);

namespace App\Traits\Framework;

trait PostmanCollector
{
    private static array $postman = [];

    public static function init(string $host)
    {
        self::$postman['params'] = [
            'routes' => [],
            'items' => [],
            'host' => [
                'url' => $host,
                'params' => [
                    'host' => ["{{base_url}}"]
                ]
            ]
        ];
    }

    private static function createQueryParams(array $array_params): array
    {
        $query = [];
        $params = "";
        $cont = 0;

        $addvalues = function(string $params, string $data, string $key, bool $index = false): string
        {
            if ($data === "") {
                $params .= !$index ? "&{$key}" : "?{$key}";
            } else {
                $params .= !$index ? "&{$key}={$data}" : "?{$key}={$data}";
            }

            return $params;
        };

        foreach ($array_params as $key => $data) {
            if ($cont === 0) {
                $params = $addvalues($params, $data, $key, true);
                $cont++;
            } else {
                $params = $addvalues($params, $data, $key);
            }

            $query[] = [
                'key' => $key,
                'value' => $data === "" ? null : $data
            ];
        }

        return ['raw' => $params, 'query' => $query];
    }

    private static function addParams(string $method, array $params): array
    {
        $new_params = [];

        foreach ($params as $key => $param) {
            if ($method === "POST") {
                $new_params[] = [
                    'key' => $param::$field,
                    'value' => $param::$value,
                    'description' => $param::$desc,
                    'type' => "text",
                    'disabled' => $param::$disabled
                ];
            } else {
                $new_params[$param::$field] = $param::$value;
            }
        }

        return $new_params;
    }

    private static function addPatch(string $name, string $route, $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "PATCH",
                'header' => [
                    [
                        "key" => "Content-Type",
                        "value" => "application/json",
                        'type' => "text"
                    ]
                ],
                'body' => [
                    'mode' => "raw",
                    'raw' => json->encode((object) self::addParams("PATCH", $params)),
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

    private static function addGet(string $name, string $route, array $params): array
    {
        $array_params = json->decode(json->encode((object) self::addParams("GET", $params)));
        $create_params = self::createQueryParams($array_params);
        $new_route = str->of("{{base_url}}/{$route}{$create_params['raw']}")->replace("//", "/")->get();

        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "GET",
                'header' => [
                    [
                        "key" => "Content-Type",
                        "value" => "application/json"
                    ]
                ],
                'url' => [
                    ...self::$postman['params']['host']['params'],
                    'raw' => $new_route,
                    'path' => $route === "/" ? [""] : explode("/", $route),
                    'query' => $create_params['query']
                ],
            ]
        ];
    }

    private static function addDelete(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "DELETE",
                'header' => [
                    [
                        'key' => "Content-Type",
                        'value' => "application/json"
                    ]
                ],
                'body' => [
                    'mode' => "raw",
                    'raw' => json->encode((object) self::addParams("DELETE", $params)),
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

    private static function addPost(string $name, string $route, array $params): array
    {
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

    private static function addPut(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => "PUT",
                'header' => [
                    [
                        'key' => "Content-Type",
                        'value' => "application/json"
                    ]
                ],
                'body' => [
                    'mode' => "raw",
                    'raw' => json->encode((object) self::addParams("PUT", $params)),
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

    private static function addRequest($name, $route, $method, $params)
    {
        if ($method === "POST") {
            return self::addPost($name, $route, $params);
        } elseif ($method === "GET") {
            return self::addGet($name, $route, $params);
        } elseif ($method === "PUT") {
            return self::addPut($name, $route, $params);
        } elseif ($method === "DELETE") {
            return self::addDelete($name, $route, $params);
        } elseif ($method === "PATCH") {
            return self::addPatch($name, $route, $params);
        } else {
            return self::addGet($name, $route, $params);
        }
    }

    public static function addRoutes(array $routes, array $rules)
    {
        foreach($routes as $route_url => $all_routes) {
            foreach ($all_routes as $route_method => $route_info) {
                $params = [];
                $path_route = $route_url === "/" ? "/" : "/{$route_url}";

                if (isset($rules[$route_method][$path_route])) {
                    $params = $rules[$route_method][$path_route];
                }

                self::$postman['params']['routes'][] = [
                    'url' => $route_url,
                    'method' => $route_method,
                    'params' => $params
                ];
            }
        }

        self::generateItems();
    }

    private static function generateItems()
    {
        foreach (self::$postman['params']['routes'] as $key_route => $route) {
            $split_all = null;

            if ($route['url'] === "/") {
                $split_all = [str->of("index-{$route['method']}")->lower()->get()];
            } else {
                $split_all = explode('/', $route['url']);
            }

            $reverse = self::reverseArray($split_all);
            $size = count($reverse) - 1;
            $request = null;
            $initial = true;
            $last_array = [];

            foreach ($reverse as $key_split => $split) {
                if ($key_split === 0) {
                    $request = self::addRequest(
                        $split,
                        $route['url'],
                        $route['method'] === "ANY" ? "GET" : $route['method'],
                        $route['params']
                    );

                    if ($key_split === $size) {
                        self::$postman['params']['items'][] = $request;
                    }
                } else {
                    if ($initial) {
                        $initial = false;

                        $last_array = [
                            'name' => $split,
                            'item' => [$request]
                        ];
                    } else {
                        $last_array = [
                            'name' => $split,
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

    public static function createCollection(array $items): array
    {
        $result = [];

        foreach ($items as $json) {
            if (isset($result[$json['name']])) {
                if (isset($json['item'])) {
                    if (isset($result[$json['name']]['item'])) {
                        $result[$json['name']]['item'] = array_merge(
                            $result[$json['name']]['item'],
                            $json['item']
                        );
                    }
                } else {
                    $result[] = $json;
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

    public static function getRoutes(): array
    {
        return self::$postman['params']['routes'];
    }

    public static function getItems(): array
    {
        return self::$postman['params']['items'];
    }

    public static function reverseArray(array $items): array
    {
        $new_items = [];

        foreach ($items as $key => $route) {
            $new_items = arr->of($new_items)->prepend($route);
        }

        return $new_items;
    }
}
