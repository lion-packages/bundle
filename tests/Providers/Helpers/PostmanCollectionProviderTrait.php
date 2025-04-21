<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

use Lion\Bundle\Helpers\Commands\PostmanCollection;
use Lion\Route\Route;

trait PostmanCollectionProviderTrait
{
    public static function addValuesParamProvider(): array
    {
        return [
            [
                'params' => '',
                'value' => 'lion',
                'key' => 'root',
                'index' => true,
                'return' => '?root=lion'
            ],
            [
                'params' => '',
                'value' => 'root',
                'key' => 'name',
                'index' => true,
                'return' => '?name=root'
            ],
            [
                'params' => '',
                'value' => 'lion',
                'key' => 'root',
                'index' => false,
                'return' => '&root=lion'
            ],
            [
                'params' => '',
                'value' => 'root',
                'key' => 'name',
                'index' => false,
                'return' => '&name=root'
            ],
            [
                'params' => '',
                'value' => '',
                'key' => 'root',
                'index' => true,
                'return' => '?root'
            ],
            [
                'params' => '',
                'value' => '',
                'key' => 'name',
                'index' => true,
                'return' => '?name'
            ],
            [
                'params' => '',
                'value' => '',
                'key' => 'root',
                'index' => false,
                'return' => '&root'
            ],
            [
                'params' => '',
                'value' => '',
                'key' => 'name',
                'index' => false,
                'return' => '&name'
            ],
        ];
    }

    public static function createQueryParamsProvider(): array
    {
        return [
            [
                'jsonParams' => '{"name":"root","last_name":"lion"}',
                'return' => [
                    'raw' => '?name=root&last_name=lion',
                    'query' => [
                        [
                            'key' => 'name',
                            'value' => 'root',
                        ],
                        [
                            'key' => 'last_name',
                            'value' => 'lion'
                        ]
                    ]
                ]
            ],
            [
                'jsonParams' => '{"name":"root","last_name":"lion","rol":"administrator"}',
                'return' => [
                    'raw' => '?name=root&last_name=lion&rol=administrator',
                    'query' => [
                        [
                            'key' => 'name',
                            'value' => 'root',
                        ],
                        [
                            'key' => 'last_name',
                            'value' => 'lion'
                        ],
                        [
                            'key' => 'rol',
                            'value' => 'administrator'
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function addParamsProvider(): array
    {
        return [
            [
                'rules' => [],
                'return' => '',
            ],
            [
                'rules' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                    new class () {
                        public string $field = 'last_name';

                        public string $value = 'lion';
                    },
                ],
                'return' => '{"name":"root","last_name":"lion"}',
            ],
            [
                'rules' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                    new class () {
                        public string $field = '';

                        public string $value = '';
                    },
                    new class () {
                        public string $field = 'rol';

                        public string $value = '';
                    },
                ],
                'return' => '{"name":"root","rol":""}',
            ],
        ];
    }

    public static function addPatchProvider(): array
    {
        return [
            [
                'name' => 'example',
                'route' => 'example',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::PATCH,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'users',
                'route' => 'users',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'lion';
                    },
                ],
                'return' => [
                    'name' => 'users',
                    'response' => [],
                    'request' => [
                        'method' => Route::PATCH,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"lion"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/users',
                            'path' => [...explode('/', 'users')]
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function addGetProvider(): array
    {
        return [
            [
                'name' => 'example',
                'route' => 'example',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::GET,
                        'header' => [PostmanCollection::HEADERS],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example?name=root',
                            'path' => '/' === 'example' ? [''] : explode('/', 'example'),
                            'query' => [
                                [
                                    'key' => 'name',
                                    'value' => 'root'
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            [
                'name' => 'users',
                'route' => 'users',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'users',
                    'response' => [],
                    'request' => [
                        'method' => Route::GET,
                        'header' => [PostmanCollection::HEADERS],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/users?name=root',
                            'path' => '/' === 'users' ? [''] : explode('/', 'users'),
                            'query' => [
                                [
                                    'key' => 'name',
                                    'value' => 'root'
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    public static function addDeleteProvider(): array
    {
        return [
            [
                'name' => 'example',
                'route' => 'example',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::DELETE,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'users',
                'route' => 'users',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'users',
                    'response' => [],
                    'request' => [
                        'method' => Route::DELETE,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/users',
                            'path' => [...explode('/', 'users')]
                        ]
                    ]
                ]
            ],
        ];
    }

    public static function addPostProvider(): array
    {
        return [
            [
                'name' => 'example',
                'route' => 'example',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::POST,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'users',
                'route' => 'users',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'users',
                    'response' => [],
                    'request' => [
                        'method' => Route::POST,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/users',
                            'path' => [...explode('/', 'users')]
                        ]
                    ]
                ]
            ],
        ];
    }

    public static function addPutProvider(): array
    {
        return [
            [
                'name' => 'example',
                'route' => 'example',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::PUT,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'users',
                'route' => 'users',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'users',
                    'response' => [],
                    'request' => [
                        'method' => Route::PUT,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/users',
                            'path' => [...explode('/', 'users')]
                        ]
                    ]
                ]
            ],
        ];
    }

    public static function addRequestProvider(): array
    {
        return [
            [
                'name' => 'example',
                'route' => 'example',
                'method' => Route::POST,
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::POST,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'example',
                'route' => 'example',
                'method' => Route::GET,
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::GET,
                        'header' => [PostmanCollection::HEADERS],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example?name=root',
                            'path' => '/' === 'example' ? [''] : explode('/', 'example'),
                            'query' => [
                                [
                                    'key' => 'name',
                                    'value' => 'root'
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            [
                'name' => 'example',
                'route' => 'example',
                'method' => Route::PUT,
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::PUT,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'example',
                'route' => 'example',
                'method' => Route::DELETE,
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::DELETE,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'example',
                'route' => 'example',
                'method' => Route::PATCH,
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::PATCH,
                        'header' => [PostmanCollection::HEADERS],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => '{"name":"root"}',
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example',
                            'path' => [...explode('/', 'example')]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'example',
                'route' => 'example',
                'method' => 'ERROR-TEST',
                'params' => [
                    new class () {
                        public string $field = 'name';

                        public string $value = 'root';
                    },
                ],
                'return' => [
                    'name' => 'example',
                    'response' => [],
                    'request' => [
                        'method' => Route::GET,
                        'header' => [PostmanCollection::HEADERS],
                        'url' => [
                            'host' => ['{{base_url}}'],
                            'raw' => '{{base_url}}/example?name=root',
                            'path' => '/' === 'example' ? [''] : explode('/', 'example'),
                            'query' => [
                                [
                                    'key' => 'name',
                                    'value' => 'root'
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    public static function reverseArrayProvider(): array
    {
        return [
            [
                'items' => ['root', 'lion'],
                'return' => ['lion', 'root']
            ],
            [
                'items' => ['root', 'lion', 'dev'],
                'return' => ['dev', 'lion', 'root']
            ]
        ];
    }
}
