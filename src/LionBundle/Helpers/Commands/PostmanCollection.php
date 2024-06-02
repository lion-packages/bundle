<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Route\Route;

/**
 * Generate structures to create Postman collections
 *
 * @property Arr $arr [Arr class object]
 * @property Str $str [Str class object]
 * @property array $postman [List of configuration data for postman collection]
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class PostmanCollection
{
    /**
     * [Headers available for requests]
     *
     * @const HEADERS
     */
    const HEADERS = [
        'key' => 'Content-Type',
        'value' => 'application/json',
        'type' => 'text'
    ];

    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [List of configuration data for postman collection]
     *
     * @var array $postman
     */
    private array $postman = [];

    /**
     * @required
     */
    public function setArr(Arr $arr): PostmanCollection
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * @required
     */
    public function setStr(Str $str): PostmanCollection
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Defines the initial configuration of the collection
     *
     * @param string $host [Defines the host of HTTP requests]
     *
     * @return void
     */
    public function init(string $host): void
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

    /**
     * Convert 2 values with GET format
     *
     * @param string $params [String that concatenates the parameters]
     * @param string $value [Parameter value]
     * @param string $key [Parameter name]
     * @param bool $index [Confirm if the GET parameter is the initial one]
     *
     * @return string
     */
    private function addValuesParam(string $params, string $value, string $key, bool $index = false): string
    {
        if ('' === $value) {
            $params .= !$index ? "&{$key}" : "?{$key}";
        } else {
            $params .= !$index ? "&{$key}={$value}" : "?{$key}={$value}";
        }

        return $params;
    }

    /**
     * Convert a list of parameters to GET parameters
     *
     * @param string $jsonParams [JSON object with parameters]
     *
     * @return array<string>
     */
    private function createQueryParams(string $jsonParams): array
    {
        $query = [];

        $params = '';

        $cont = 0;

        if (!empty($jsonParams)) {
            foreach (json_decode($jsonParams, true) as $key => $data) {
                if ($cont === 0) {
                    $params = $this->addValuesParam($params, $data, $key, true);

                    $cont++;
                } else {
                    $params = $this->addValuesParam($params, $data, $key);
                }

                $query[] = [
                    'key' => $key,
                    'value' => ('' === $data ? null : $data)
                ];
            }
        }

        return [
            'raw' => $params,
            'query' => $query
        ];
    }

    /**
     * Gets the structure of the body with its respective keys and values
     *
     * @param array $rules [List of defined rules]
     *
     * @return array<string, string>|string
     */
    private function addParams(array $rules): array|string
    {
        $newParams = [];

        foreach ($rules as $rule) {
            $objectRuleClass = new $rule();

            if (!empty($objectRuleClass->field)) {
                $newParams[$objectRuleClass->field] = $objectRuleClass->value;
            }
        }

        if (count($newParams) > 0) {
            return json_encode($newParams);
        }

        return '';
    }

    /**
     * Generate the structure of an HTTP PATCH request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array $params [Parameters defined for routes]
     *
     * @return array
     */
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
                    'raw' => $this->addParams($params),
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

    /**
     * Generate the structure of an HTTP GET request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array $params [Parameters defined for routes]
     *
     * @return array
     */
    private function addGet(string $name, string $route, array $params): array
    {
        $paramsJson = $this->addParams($params);

        $createParams = self::createQueryParams($paramsJson);

        $newRoute = $this->str
            ->of("{{base_url}}/{$route}{$createParams['raw']}")
            ->replace("//", '/')
            ->get();

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

    /**
     * Generate the structure of an HTTP DELETE request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array $params [Parameters defined for routes]
     *
     * @return array
     */
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
                    'raw' => $this->addParams($params),
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

    /**
     * Generate the structure of an HTTP POST request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array $params [Parameters defined for routes]
     *
     * @return array
     */
    private function addPost(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::POST,
                'header' => [self::HEADERS],
                'body' => [
                    'mode' => 'raw',
                    'raw' => $this->addParams($params),
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

    /**
     * Generate the structure of an HTTP PUT request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array $params [Parameters defined for routes]
     *
     * @return array
     */
    private function addPut(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::PUT,
                'header' => [self::HEADERS],
                'body' => [
                    'mode' => 'raw',
                    'raw' => $this->addParams($params),
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

    /**
     * Generate the structure of an HTTP request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param string $method [Defined HTTP method]
     * @param array $params [Parameters defined for routes]
     *
     * @return array
     */
    private function addRequest(string $name, string $route, string $method, array $params): array
    {
        $name = '' === $name ? 'index' : $name;

        if (Route::POST === $method) {
            return $this->addPost($name, $route, $params);
        } elseif (Route::GET === $method) {
            return $this->addGet($name, $route, $params);
        } elseif (Route::PUT === $method) {
            return $this->addPut($name, $route, $params);
        } elseif (Route::DELETE === $method) {
            return $this->addDelete($name, $route, $params);
        } elseif (Route::PATCH === $method) {
            return $this->addPatch($name, $route, $params);
        } else {
            return $this->addGet($name, $route, $params);
        }
    }

    /**
     * Add the available web routes to create a new data structure to generate
     * collections
     *
     * @param array $routes [List of defined web routes]
     * @param array $rules [List of defined web rules]
     *
     * @return void
     */
    public function addRoutes(array $routes, array $rules = []): void
    {
        foreach ($routes as $routeUrl => $allRoutes) {
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

    /**
     * Generate the structures of each HTTP request for the collection
     *
     * @return void
     */
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

            foreach ($reverse as $keySplit => $split) {
                if ($keySplit === 0) {
                    $request = $this->addRequest(
                        $split,
                        $route['url'],
                        (Route::ANY === $route['method'] ? Route::GET : $route['method']),
                        $route['params']
                    );

                    if ($keySplit === $size) {
                        $this->postman['params']['items'][] = $request;
                    }
                } else {
                    if ($initial) {
                        $initial = false;

                        $lastArray = ['name' => $split, 'item' => [$request]];
                    } else {
                        $lastArray = ['name' => $split, 'item' => [$lastArray]];
                    }

                    if ($keySplit === $size) {
                        $this->postman['params']['items'][] = $lastArray;
                    }
                }
            }

            $initial = true;

            $lastArray = [];
        }
    }

    /**
     * Organizes HTTP request structures for collections
     *
     * @param array $items [Data structure]
     * @param &$result [List the data structure organized as a result]
     *
     * @return array
     */
    public function createCollection(array $items, &$result = null): array
    {
        if ($result === null) {
            $result = [];
        }

        foreach ($items as $json) {
            $name = $json['name'];

            $lastIndex = count($result) - 1;

            while ($lastIndex >= 0 && $result[$lastIndex]['name'] !== $name) {
                $lastIndex--;
            }

            if ($lastIndex >= 0 && isset($result[$lastIndex]['item'])) {
                if (isset($json['item'])) {
                    $this->createCollection($json['item'], $result[$lastIndex]['item']);
                }

                // else {
                //     $result[$lastIndex]['item'][] = $json;
                // }
            } else {
                $result[] = $json;
            }
        }

        return $result;
    }

    /**
     * Returns the routes available for the collection
     *
     * @return array<array<string, string>>
     */
    public function getRoutes(): array
    {
        return $this->postman['params']['routes'];
    }

    /**
     * You get the data structures constructed from HTTP requests
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->postman['params']['items'];
    }

    /**
     * Create an array with the data in reverse order
     *
     * @param array $items [List of defined elements]
     *
     * @return array
     */
    private function reverseArray(array $items): array
    {
        $newItems = [];

        foreach ($items as $route) {
            $newItems = $this->arr->of($newItems)->prepend($route)->get();
        }

        return $newItems;
    }
}
