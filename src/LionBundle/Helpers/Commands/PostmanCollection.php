<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Route\Route;

class PostmanCollection
{
    const HEADERS = [
        'key' => 'Content-Type',
        'value' => 'application/json',
        'type' => 'text'
    ];

    private Arr $arr;
    private Str $str;
    private array $postman = [];

    public function __construct()
    {
        $this->arr = new Arr();
        $this->str = new Str();
    }

    public function init(string $host)
    {
        $this->postman['params'] = [
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

    private function addValuesParam(string $params, string $data, string $key, bool $index = false): string
    {
        if ('' === $data) {
            $params .= !$index ? "&{$key}" : "?{$key}";
        } else {
            $params .= !$index ? "&{$key}={$data}" : "?{$key}={$data}";
        }

        return $params;
    }

    private function createQueryParams(array $arrayParams): array
    {
        $query = [];
        $params = '';
        $cont = 0;

        foreach ($arrayParams as $key => $data) {
            if ($cont === 0) {
                $params = $this->addValuesParam($params, $data, $key, true);
                $cont++;
            } else {
                $params = $this->addValuesParam($params, $data, $key);
            }

            $query[] = ['key' => $key, 'value' => ('' === $data ? null : $data)];
        }

        return ['raw' => $params, 'query' => $query];
    }

    private function addParams(string $method, array $params): array
    {
        $newParams = [];

        foreach ($params as $param) {
            if ('POST' === $method) {
                $newParams[] = [
                    'key' => $param::$field,
                    'value' => $param::$value,
                    'description' => $param::$desc,
                    'type' => 'text',
                    'disabled' => $param::$disabled
                ];
            } else {
                $newParams[$param::$field] = $param::$value;
            }
        }

        return $newParams;
    }

    private function addPatch(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::PATCH,
                'header' => [self::HEADERS],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode((object) $this->addParams(Route::PATCH, $params)),
                    'options' => [
                        'raw' => [
                            'language' => 'json'
                        ]
                    ]
                ],
                'url' => [
                    ...$this->postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode('/', $route)]
                ]
            ]
        ];
    }

    private function addGet(string $name, string $route, array $params): array
    {
        $arrayParams = json_decode(json_encode((object) $this->addParams(Route::GET, $params)), true);
        $createParams = self::createQueryParams($arrayParams);
        $newRoute = $this->str->of("{{base_url}}/{$route}{$createParams['raw']}")->replace("//", '/')->get();

        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::GET,
                'header' => [self::HEADERS],
                'url' => [
                    ...$this->postman['params']['host']['params'],
                    'raw' => $newRoute,
                    'path' => '/' === $route ? [''] : explode('/', $route),
                    'query' => $createParams['query']
                ],
            ]
        ];
    }

    private function addDelete(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::DELETE,
                'header' => [self::HEADERS],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode((object) $this->addParams(Route::DELETE, $params)),
                    'options' => [
                        'raw' => [
                            'language' => 'json'
                        ]
                    ]
                ],
                'url' => [
                    ...$this->postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode('/', $route)]
                ]
            ]
        ];
    }

    private function addPost(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::POST,
                'header' => [self::HEADERS],
                'body' => [
                    'mode' => "formdata",
                    'formdata' => $this->addParams(Route::POST, $params)
                ],
                'url' => [
                    ...$this->postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode('/', $route)]
                ]
            ]
        ];
    }

    private function addPut(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::PUT,
                'header' => [self::HEADERS],
                'body' => [
                    'mode' => "raw",
                    'raw' => json_encode((object) $this->addParams(Route::PUT, $params)),
                    'options' => [
                        'raw' => [
                            'language' => 'json'
                        ]
                    ]
                ],
                'url' => [
                    ...$this->postman['params']['host']['params'],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [...explode('/', $route)]
                ]
            ]
        ];
    }

    private function addRequest(string $name, string $route, string $method, array $params): array
    {
        if (Route::POST === $method) {
            return self::addPost($name, $route, $params);
        } elseif (Route::GET === $method) {
            return self::addGet($name, $route, $params);
        } elseif (Route::PUT === $method) {
            return self::addPut($name, $route, $params);
        } elseif (Route::DELETE === $method) {
            return self::addDelete($name, $route, $params);
        } elseif (Route::PATCH === $method) {
            return self::addPatch($name, $route, $params);
        }

        return self::addGet($name, $route, $params);
    }

    public function addRoutes(array $routes, array $rules): void
    {
        foreach($routes as $routeUrl => $allRoutes) {
            foreach ($allRoutes as $routeMethod => $routeInfo) {
                $params = [];
                $pathRoute = '/' === $routeUrl ? '/' : "/{$routeUrl}";

                if (isset($rules[$routeMethod][$pathRoute])) {
                    $params = $rules[$routeMethod][$pathRoute];
                }

                $this->postman['params']['routes'][] = [
                    'url' => $routeUrl,
                    'method' => $routeMethod,
                    'params' => $params
                ];
            }
        }

        $this->generateItems();
    }

    private function generateItems(): void
    {
        foreach ($this->postman['params']['routes'] as $route) {
            $splitAll = null;

            if ('/' === $route['url']) {
                $splitAll = [$this->str->of("index-{$route['method']}")->lower()->get()];
            } else {
                $splitAll = explode('/', $route['url']);
            }

            $reverse = $this->reverseArray($splitAll);
            $size = count($reverse) - 1;
            $request = null;
            $initial = true;
            $lastArray = [];

            foreach ($reverse as $key_split => $split) {
                if ($key_split === 0) {
                    $request = $this->addRequest(
                        $split,
                        $route['url'],
                        (Route::ANY === $route['method'] ? Route::GET : $route['method']),
                        $route['params']
                    );

                    if ($key_split === $size) {
                        $this->postman['params']['items'][] = $request;
                    }
                } else {
                    if ($initial) {
                        $initial = false;
                        $lastArray = ['name' => $split, 'item' => [$request]];
                    } else {
                        $lastArray = ['name' => $split, 'item' => [$lastArray]];
                    }

                    if ($key_split === $size) {
                        $this->postman['params']['items'][] = $lastArray;
                    }
                }
            }

            $initial = true;
            $lastArray = [];
        }
    }

    public function createCollection(array $items): array
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
                $item['item'] = $this->createCollection($item['item']);
            }
        }

        return array_values($result);
    }

    public function getRoutes(): array
    {
        return $this->postman['params']['routes'];
    }

    public function getItems(): array
    {
        return $this->postman['params']['items'];
    }

    public function reverseArray(array $items): array
    {
        $newItems = [];

        foreach ($items as $route) {
            $newItems = $this->arr->of($newItems)->prepend($route)->get();
        }

        return $newItems;
    }
}