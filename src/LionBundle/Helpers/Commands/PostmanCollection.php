<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use DI\Attribute\Inject;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Route\Helpers\Rules;
use Lion\Route\Route;

/**
 * Generate structures to create Postman collections
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
    public const array HEADERS = [
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
     * @var array{
     *     params?: array{
     *         routes?: array<int, array{
     *              url: string,
     *              method: string
     *         }>,
     *         host?: array{
     *             url: string,
     *             params: array{
     *                 host: array<int, string>
     *             }
     *         },
     *         items?: array<int, array{
     *             name: string,
     *             response?: array<int|string, mixed>,
     *             request?: array{
     *                  method: string,
     *                  header: array<int, array{
     *                      key: string,
     *                      value: string,
     *                      type: string
     *                  }>,
     *                  body?: array{
     *                      mode: string,
     *                      raw: string,
     *                      options: array{
     *                          raw: array{
     *                              language: string
     *                          }
     *                      }
     *                  },
     *                  url: array{
     *                      host: array<int, string>,
     *                      raw: string,
     *                      path: array<int, string>,
     *                      query?: array<int|string, mixed>
     *                  }
     *             },
     *             items?: array<int, array{
     *                 name: string,
     *                 response: array<int|string, mixed>,
     *                 request: array{
     *                     method: string,
     *                     header: array<int, array{
     *                         key: string,
     *                         value: string,
     *                         type: string
     *                     }>,
     *                     body?: array{
     *                         mode: string,
     *                         raw: string,
     *                         options: array{
     *                             raw: array{
     *                                 language: string
     *                             }
     *                         }
     *                     },
     *                     url: array{
     *                         host: array<int, string>,
     *                         raw: string,
     *                         path: array<int, string>,
     *                         query?: array<int|string, mixed>
     *                     }
     *                 }
     *             }>
     *         }>
     *     }
     * } $postman
     */
    private array $postman;

    #[Inject]
    public function setArr(Arr $arr): PostmanCollection
    {
        $this->arr = $arr;

        return $this;
    }

    #[Inject]
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
            'host' => [
                'url' => $host,
                'params' => [
                    'host' => [
                        "{{base_url}}",
                    ],
                ],
            ],
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
     * @return array{
     *     raw: string,
     *     query: array<int, array{
     *          key: string,
     *          value: string|null
     *     }>
     * }
     */
    private function createQueryParams(string $jsonParams): array
    {
        /** @var array<int, array{
         *     key: string,
         *     value: string|null
         * }> $query
         */
        $query = [];

        $params = '';

        $cont = 0;

        if (!empty($jsonParams)) {
            /** @var array<string, string> $jsonParamsDecode */
            $jsonParamsDecode = json_decode($jsonParams, true);

            foreach ($jsonParamsDecode as $key => $value) {
                if ($cont === 0) {
                    $params = $this->addValuesParam($params, $value, $key, true);

                    $cont++;
                } else {
                    $params = $this->addValuesParam($params, $value, $key);
                }

                $query[] = [
                    'key' => $key,
                    'value' => ('' === $value ? null : $value)
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
     * @param array<string, string|null> $rules [List of defined rules]
     *
     * @return string
     */
    private function addParams(array $rules): string
    {
        $newParams = [];

        foreach ($rules as $rule) {
            /** @var Rules $objectRuleClass */
            $objectRuleClass = new $rule();

            if (!empty($objectRuleClass->field) && isset($objectRuleClass->value)) {
                $newParams[$objectRuleClass->field] = $objectRuleClass->value;
            }
        }

        if (count($newParams) > 0) {
            /** @var non-empty-string $jsonParams */
            $jsonParams = json_encode($newParams);

            return $jsonParams;
        }

        return '';
    }

    /**
     * Generate the structure of an HTTP PATCH request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array<string, string|null> $params [Parameters defined for routes]
     *
     * @return array{
     *      name: string,
     *      response: array<int|string, mixed>,
     *      request: array{
     *           method: string,
     *           header: array<int, array{
     *               key: string,
     *               value: string,
     *               type: string
     *           }>,
     *           body: array{
     *               mode: string,
     *               raw: string,
     *               options: array{
     *                   raw: array{
     *                       language: string
     *                   }
     *               }
     *           },
     *           url: array{
     *               host: array<int, string>,
     *               raw: string,
     *               path: array<int, string>
     *           }
     *      }
     *  }
     */
    private function addPatch(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::PATCH,
                'header' => [
                    self::HEADERS,
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => $this->addParams($params),
                    'options' => [
                        'raw' => [
                            'language' => 'json',
                        ],
                    ],
                ],
                'url' => [
                    'host' => [
                        '{{base_url}}',
                    ],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [
                        ...explode('/', $route),
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate the structure of an HTTP GET request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array<string, string|null> $params [Parameters defined for routes]
     *
     * @return array{
     *      name: string,
     *      response: array<int|string, mixed>,
     *      request: array{
     *          method: string,
     *          header: array<int, array{
     *              key: string,
     *              value: string,
     *              type: string
     *          }>,
     *          url: array{
     *              host: array<int, string>,
     *              raw: string,
     *              path: array<int, string>,
     *              query: array<int, array{
     *                  key: string,
     *                  value: string|null
     *              }>
     *          }
     *      }
     *  }
     */
    private function addGet(string $name, string $route, array $params): array
    {
        $createParams = self::createQueryParams($this->addParams($params));

        /** @var string $newRoute */
        $newRoute = $this->str
            ->of("{{base_url}}/{$route}{$createParams['raw']}")
            ->replace("//", '/')
            ->get();

        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::GET,
                'header' => [
                    self::HEADERS,
                ],
                'url' => [
                    'host' => [
                        '{{base_url}}',
                    ],
                    'raw' => $newRoute,
                    'path' => '/' === $route ? [''] : explode('/', $route),
                    'query' => $createParams['query'],
                ],
            ]
        ];
    }

    /**
     * Generate the structure of an HTTP DELETE request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array<string, string|null> $params [Parameters defined for routes]
     *
     * @return array{
     *      name: string,
     *      response: array<int|string, mixed>,
     *      request: array{
     *           method: string,
     *           header: array<int, array{
     *               key: string,
     *               value: string,
     *               type: string
     *           }>,
     *           body: array{
     *               mode: string,
     *               raw: string,
     *               options: array{
     *                   raw: array{
     *                       language: string
     *                   }
     *               }
     *           },
     *           url: array{
     *               host: array<int, string>,
     *               raw: string,
     *               path: array<int, string>
     *           }
     *      }
     *  }
     */
    private function addDelete(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::DELETE,
                'header' => [
                    self::HEADERS,
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => $this->addParams($params),
                    'options' => [
                        'raw' => [
                            'language' => 'json',
                        ],
                    ],
                ],
                'url' => [
                    'host' => [
                        '{{base_url}}',
                    ],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [
                        ...explode('/', $route),
                    ]
                ],
            ],
        ];
    }

    /**
     * Generate the structure of an HTTP POST request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array<string, string|null> $params [Parameters defined for routes]
     *
     * @return array{
     *      name: string,
     *      response: array<int|string, mixed>,
     *      request: array{
     *           method: string,
     *           header: array<int, array{
     *               key: string,
     *               value: string,
     *               type: string
     *           }>,
     *           body: array{
     *               mode: string,
     *               raw: string,
     *               options: array{
     *                   raw: array{
     *                       language: string
     *                   }
     *               }
     *           },
     *           url: array{
     *               host: array<int, string>,
     *               raw: string,
     *               path: array<int, string>
     *           }
     *      }
     *  }
     */
    private function addPost(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::POST,
                'header' => [
                    self::HEADERS,
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => $this->addParams($params),
                    'options' => [
                        'raw' => [
                            'language' => 'json',
                        ],
                    ],
                ],
                'url' => [
                    'host' => [
                        '{{base_url}}',
                    ],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [
                        ...explode('/', $route),
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate the structure of an HTTP PUT request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param array<string, string|null> $params [Parameters defined for routes]
     *
     * @return array{
     *      name: string,
     *      response: array<int|string, mixed>,
     *      request: array{
     *           method: string,
     *           header: array<int, array{
     *               key: string,
     *               value: string,
     *               type: string
     *           }>,
     *           body: array{
     *               mode: string,
     *               raw: string,
     *               options: array{
     *                   raw: array{
     *                       language: string
     *                   }
     *               }
     *           },
     *           url: array{
     *               host: array<int, string>,
     *               raw: string,
     *               path: array<int, string>
     *           }
     *      }
     *  }
     */
    private function addPut(string $name, string $route, array $params): array
    {
        return [
            'name' => $name,
            'response' => [],
            'request' => [
                'method' => Route::PUT,
                'header' => [
                    self::HEADERS,
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => $this->addParams($params),
                    'options' => [
                        'raw' => [
                            'language' => 'json',
                        ],
                    ],
                ],
                'url' => [
                    'host' => [
                        '{{base_url}}',
                    ],
                    'raw' => '{{base_url}}/' . $route,
                    'path' => [
                        ...explode('/', $route),
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate the structure of an HTTP request
     *
     * @param string $name [Request name]
     * @param string $route [Route name]
     * @param string $method [Defined HTTP method]
     * @param array<string, string|null> $params [Parameters defined for routes]
     *
     * @return array{
     *     name: string,
     *     response: array<int|string, mixed>,
     *     request: array{
     *         method: string,
     *         header: array<int, array{
     *             key: string,
     *             value: string,
     *             type: string
     *         }>,
     *         body?: array{
     *             mode: string,
     *             raw: string,
     *             options: array{
     *                 raw: array{
     *                     language: string
     *                 }
     *             }
     *         },
     *         url: array{
     *             host: array<int, string>,
     *             raw: string,
     *             path: array<int, string>,
     *             query?: array<int, array{
     *                  key: string,
     *                  value: string|null
     *             }>
     *         }
     *     }
     * }
     */
    protected function addRequest(string $name, string $route, string $method, array $params = []): array
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
     * @param array<string, array<string, array{
     *     filters: array<int, string>,
     *     handler: array{
     *          controller: bool|array{
     *              name: string,
     *              function: string
     *          },
     *          callback: bool
     *     }
     * }>> $routes [List of defined web routes]
     *
     * @return void
     */
    public function addRoutes(array $routes): void
    {
        foreach ($routes as $routeUrl => $allRoutes) {
            foreach ($allRoutes as $routeMethod => $routeInfo) {
                $this->postman['params']['routes'][] = [
                    'url' => $routeUrl,
                    'method' => $routeMethod,
                    'params' => [],
                ];
            }
        }
    }

    /**
     * Generate the structures of each HTTP request for the collection
     *
     * @return void
     */
    public function generateItems(): void
    {
        if (isset($this->postman['params'], $this->postman['params']['routes'])) {
            foreach ($this->postman['params']['routes'] as $route) {
                $splitAll = null;

                if ('/' === $route['url']) {
                    /** @var array<int, string> $splitAll */
                    $splitAll = [
                        $this->str
                            ->of("index-{$route['method']}")
                            ->lower()
                            ->get(),
                    ];
                } else {
                    $splitAll = explode('/', $route['url']);
                }

                /** @var array<int, string> $reverse */
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
                            (Route::ANY === $route['method'] ? Route::GET : $route['method'])
                        );

                        if ($keySplit === $size) {
                            $this->postman['params']['items'][] = $request;
                        }
                    } else {
                        if ($initial) {
                            $initial = false;

                            $lastArray = [
                                'name' => $split,
                                'item' => [
                                    $request,
                                ],
                            ];
                        } else {
                            $lastArray = [
                                'name' => $split,
                                'item' => [
                                    $lastArray,
                                ],
                            ];
                        }

                        if ($keySplit === $size) {
                            $this->postman['params']['items'][] = $lastArray;
                        }
                    }
                }
            }
        }
    }

    /**
     * Organizes HTTP request structures for collections
     *
     * @param array{
     *     name: string,
     *     response?: array<int|string, mixed>,
     *     request?: array{
     *          method: string,
     *          header: array<int, array{
     *              key: string,
     *              value: string,
     *              type: string
     *          }>,
     *          body?: array{
     *              mode: string,
     *              raw: string,
     *              options: array{
     *                  raw: array{
     *                      language: string
     *                  }
     *              }
     *          },
     *          url: array{
     *              host: array<int, string>,
     *              raw: string,
     *              path: array<int, string>,
     *              query?: array<int|string, mixed>
     *          }
     *     },
     *     items?: array<int, array{
     *         name: string,
     *         response: array<int|string, mixed>,
     *         request: array{
     *             method: string,
     *             header: array<int, array{
     *                 key: string,
     *                 value: string,
     *                 type: string
     *             }>,
     *             body?: array{
     *                 mode: string,
     *                 raw: string,
     *                 options: array{
     *                     raw: array{
     *                         language: string
     *                     }
     *                 }
     *             },
     *             url: array{
     *                 host: array<int, string>,
     *                 raw: string,
     *                 path: array<int, string>,
     *                 query?: array<int|string, mixed>
     *             }
     *         }
     *     }>
     * } $items [Data structure]
     *
     * @param array<int, array{
     *     name: string,
     *     response?: array<int|string, mixed>,
     *     request?: array{
     *          method: string,
     *          header: array<int, array{
     *              key: string,
     *              value: string,
     *              type: string
     *          }>,
     *          body?: array{
     *              mode: string,
     *              raw: string,
     *              options: array{
     *                  raw: array{
     *                      language: string
     *                  }
     *              }
     *          },
     *          url: array{
     *              host: array<int, string>,
     *              raw: string,
     *              path: array<int, string>,
     *              query?: array<int|string, mixed>
     *          }
     *     },
     *     items?: array<int, array{
     *         name: string,
     *         response?: array<int|string, mixed>,
     *         request?: array{
     *             method: string,
     *             header: array<int, array{
     *                 key: string,
     *                 value: string,
     *                 type: string
     *             }>,
     *             body?: array{
     *                 mode: string,
     *                 raw: string,
     *                 options: array{
     *                     raw: array{
     *                         language: string
     *                     }
     *                 }
     *             },
     *             url: array{
     *                 host: array<int, string>,
     *                 raw: string,
     *                 path: array<int, string>,
     *                 query?: array<int|string, mixed>
     *             }
     *         },
     *         item?: array<int, mixed>
     *     }>
     * }> &$result [List the data structure organized as a result]
     *
     * @return array<int, array{
     *     name: string,
     *     response?: array<int|string, mixed>,
     *     request?: array{
     *          method: string,
     *          header: array<int, array{
     *              key: string,
     *              value: string,
     *              type: string
     *          }>,
     *          body?: array{
     *              mode: string,
     *              raw: string,
     *              options: array{
     *                  raw: array{
     *                      language: string
     *                  }
     *              }
     *          },
     *          url: array{
     *              host: array<int, string>,
     *              raw: string,
     *              path: array<int, string>,
     *              query?: array<int|string, mixed>
     *          }
     *     },
     *     items?: array<int, array{
     *         name: string,
     *         response?: array<int|string, mixed>,
     *         request?: array{
     *             method: string,
     *             header: array<int, array{
     *                 key: string,
     *                 value: string,
     *                 type: string
     *             }>,
     *             body?: array{
     *                 mode: string,
     *                 raw: string,
     *                 options: array{
     *                     raw: array{
     *                         language: string
     *                     }
     *                 }
     *             },
     *             url: array{
     *                 host: array<int, string>,
     *                 raw: string,
     *                 path: array<int, string>,
     *                 query?: array<int|string, mixed>
     *             }
     *         },
     *         item?: array<int, mixed>
     *     }>
     * }>
     *
     * @phpstan-ignore-next-line
     */
    public function createCollection(array $items, ?array &$result = null): array
    {
        if ($result === null) {
            $result = [];
        }

        foreach ($items as $json) {
            if (!isset($json['name'])) {
                continue;
            }

            $name = $json['name'];

            $lastIndex = count($result) - 1;

            while ($lastIndex >= 0 && $result[$lastIndex]['name'] !== $name) {
                $lastIndex--;
            }

            if ($lastIndex >= 0 && isset($result[$lastIndex]['item'])) {
                if (isset($json['item'])) {
                    /** @phpstan-ignore-next-line */
                    $this->createCollection($json['item'], $result[$lastIndex]['item']);
                }

                // else {
                //     $result[$lastIndex]['item'][] = $json;
                // }
            } else {
                /** @phpstan-ignore-next-line */
                $result[] = $json;
            }
        }

        /** @phpstan-ignore-next-line */
        return $result;
    }

    /**
     * Returns the routes available for the collection
     *
     * @return array<int, array{
     *     url: string,
     *     method: string
     * }>
     */
    public function getRoutes(): array
    {
        return $this->postman['params']['routes'] ?? [];
    }

    /**
     * You get the data structures constructed from HTTP requests
     *
     * @return array<int, array{
     *     name: string,
     *     response?: array<int|string, mixed>,
     *     request?: array{
     *          method: string,
     *          header: array<int, array{
     *              key: string,
     *              value: string,
     *              type: string
     *          }>,
     *          body?: array{
     *              mode: string,
     *              raw: string,
     *              options: array{
     *                  raw: array{
     *                      language: string
     *                  }
     *              }
     *          },
     *          url: array{
     *              host: array<int, string>,
     *              raw: string,
     *              path: array<int, string>,
     *              query?: array<int|string, mixed>
     *          }
     *     },
     *     items?: array<int, array{
     *         name: string,
     *         response: array<int|string, mixed>,
     *         request: array{
     *             method: string,
     *             header: array<int, array{
     *                 key: string,
     *                 value: string,
     *                 type: string
     *             }>,
     *             body?: array{
     *                 mode: string,
     *                 raw: string,
     *                 options: array{
     *                     raw: array{
     *                         language: string
     *                     }
     *                 }
     *             },
     *             url: array{
     *                 host: array<int, string>,
     *                 raw: string,
     *                 path: array<int, string>,
     *                 query?: array<int|string, mixed>
     *             }
     *         }
     *     }>
     * }>
     */
    public function getItems(): array
    {
        return $this->postman['params']['items'] ?? [];
    }

    /**
     * Create an array with the data in reverse order
     *
     * @param array<int, string> $items [List of defined elements]
     *
     * @return array<int, string>
     */
    private function reverseArray(array $items): array
    {
        $newItems = [];

        foreach ($items as $route) {
            /** @var array<int, string> $newItems */
            $newItems = $this->arr
                ->of($newItems)
                ->prepend($route)
                ->get();
        }

        return $newItems;
    }
}
