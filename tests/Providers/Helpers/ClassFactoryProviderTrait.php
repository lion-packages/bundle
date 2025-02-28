<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

use Lion\Bundle\Helpers\Commands\ClassFactory;

trait ClassFactoryProviderTrait
{
    public static function createProvider(): array
    {
        return [
            [
                'extension' => ClassFactory::LOG_EXTENSION
            ],
            [
                'extension' => ClassFactory::PHP_EXTENSION
            ],
            [
                'extension' => ClassFactory::SH_EXTENSION
            ],
        ];
    }

    public static function addProvider(): array
    {
        return [
            [
                'extension' => ClassFactory::LOG_EXTENSION,
                'content' => 'example',
            ],
            [
                'extension' => ClassFactory::PHP_EXTENSION,
                'content' => 'example',
            ],
            [
                'extension' => ClassFactory::SH_EXTENSION,
                'content' => 'example',
            ],
        ];
    }

    public static function classFactoryProvider(): array
    {
        return [
            [
                'path' => 'app/Http/Controllers/',
                'fileName' => 'UsersController',
                'namespace' => 'App\\Http\\Controllers',
                'class' => 'UsersController'
            ],
            [
                'path' => 'app/Http/Controllers/LionDatabase/MySQL/',
                'fileName' => 'UsersController',
                'namespace' => 'App\\Http\\Controllers\\LionDatabase\\MySQL',
                'class' => 'UsersController'
            ]
        ];
    }

    public static function getPropertyProvider(): array
    {
        return [
            [
                'propertyName' => 'idusers',
                'className' => 'Users',
                'type' => 'int',
                'visibility' => null,
                'return' => (object) [
                    'format' => (object) [
                        'camel' => 'idusers',
                        'snake' => 'idusers'
                    ],
                    'getter' => (object) [
                        'name' => 'getIdusers',
                        'method' => <<<PHP
                            /**
                             * Getter method for 'idusers'
                             *
                             * @return int|null
                             */
                            public function getIdusers(): ?int
                            {
                                return \$this->idusers;
                            }
                        PHP
                    ],
                    'setter' => (object) [
                        'name' => 'setIdusers',
                        'method' => <<<PHP
                            /**
                             * Setter method for 'idusers'
                             *
                             * @param int|null \$idusers
                             *
                             * @return Users
                             */
                            public function setIdusers(?int \$idusers = null): Users
                            {
                                \$this->idusers = \$idusers;

                                return \$this;
                            }
                        PHP
                    ],
                    'variable' => (object) [
                        'annotations' => (object) [
                            'class' => (object) [
                                'data_type' => "@property int \$idusers [Property for idusers]",
                                'data_type_with_null' => "@property int|null \$idusers [Property for idusers]",
                            ],
                        ],
                        'reference' => "\$this->idusers;",
                        'name' => (object) [
                            'camel' => ("\$idusers"),
                            'snake' => ("\$idusers")
                        ],
                        'type' => (object) [
                            'camel' => ("?int \$idusers = null;"),
                            'snake' => (
                                "/**\n\t * [Property for idusers]\n\t *\n\t * @var int|null \$idusers\n\t */\n" .
                                "\t?int \$idusers = null;\n"
                            )
                        ],
                        'initialize' => (object) [
                            'camel' => ("\$idusers = null"),
                            'snake' => ("\$idusers = null")
                        ]
                    ]
                ]
            ],
            [
                'propertyName' => 'idusers',
                'className' => 'Users',
                'type' => 'int',
                'visibility' => ClassFactory::PUBLIC_PROPERTY,
                'return' => (object) [
                    'format' => (object) [
                        'camel' => 'idusers',
                        'snake' => 'idusers'
                    ],
                    'getter' => (object) [
                        'name' => 'getIdusers',
                        'method' => <<<PHP
                            /**
                             * Getter method for 'idusers'
                             *
                             * @return int|null
                             */
                            public function getIdusers(): ?int
                            {
                                return \$this->idusers;
                            }
                        PHP
                    ],
                    'setter' => (object) [
                        'name' => 'setIdusers',
                        'method' => <<<PHP
                            /**
                             * Setter method for 'idusers'
                             *
                             * @param int|null \$idusers
                             *
                             * @return Users
                             */
                            public function setIdusers(?int \$idusers = null): Users
                            {
                                \$this->idusers = \$idusers;

                                return \$this;
                            }
                        PHP
                    ],
                    'variable' => (object) [
                        'annotations' => (object) [
                            'class' => (object) [
                                'data_type' => "@property int \$idusers [Property for idusers]",
                                'data_type_with_null' => "@property int|null \$idusers [Property for idusers]",
                            ],
                        ],
                        'reference' => "\$this->idusers;",
                        'name' => (object) [
                            'camel' => ("public \$idusers"),
                            'snake' => ("public \$idusers")
                        ],
                        'type' => (object) [
                            'camel' => ("public ?int \$idusers = null;"),
                            'snake' => (
                                "/**\n\t * [Property for idusers]\n\t *\n\t * @var int|null \$idusers\n\t */\n" .
                                "\tpublic ?int \$idusers = null;\n"
                            )
                        ],
                        'initialize' => (object) [
                            'camel' => ("public \$idusers = null"),
                            'snake' => ("public \$idusers = null")
                        ]
                    ]
                ]
            ],
            [
                'propertyName' => 'idusers',
                'className' => 'Users',
                'type' => 'int',
                'visibility' => ClassFactory::PRIVATE_PROPERTY,
                'return' => (object) [
                    'format' => (object) [
                        'camel' => 'idusers',
                        'snake' => 'idusers'
                    ],
                    'getter' => (object) [
                        'name' => 'getIdusers',
                        'method' => <<<PHP
                            /**
                             * Getter method for 'idusers'
                             *
                             * @return int|null
                             */
                            public function getIdusers(): ?int
                            {
                                return \$this->idusers;
                            }
                        PHP
                    ],
                    'setter' => (object) [
                        'name' => 'setIdusers',
                        'method' => <<<PHP
                            /**
                             * Setter method for 'idusers'
                             *
                             * @param int|null \$idusers
                             *
                             * @return Users
                             */
                            public function setIdusers(?int \$idusers = null): Users
                            {
                                \$this->idusers = \$idusers;

                                return \$this;
                            }
                        PHP
                    ],
                    'variable' => (object) [
                        'annotations' => (object) [
                            'class' => (object) [
                                'data_type' => "@property int \$idusers [Property for idusers]",
                                'data_type_with_null' => "@property int|null \$idusers [Property for idusers]",
                            ],
                        ],
                        'reference' => "\$this->idusers;",
                        'name' => (object) [
                            'camel' => ("private \$idusers"),
                            'snake' => ("private \$idusers")
                        ],
                        'type' => (object) [
                            'camel' => ("private ?int \$idusers = null;"),
                            'snake' => (
                                "/**\n\t * [Property for idusers]\n\t *\n\t * @var int|null \$idusers\n\t */\n" .
                                "\tprivate ?int \$idusers = null;\n"
                            )
                        ],
                        'initialize' => (object) [
                            'camel' => ("private \$idusers = null"),
                            'snake' => ("private \$idusers = null")
                        ]
                    ]
                ]
            ],
            [
                'propertyName' => 'idusers',
                'className' => 'Users',
                'type' => 'int',
                'visibility' => ClassFactory::PROTECTED_PROPERTY,
                'return' => (object) [
                    'format' => (object) [
                        'camel' => 'idusers',
                        'snake' => 'idusers'
                    ],
                    'getter' => (object) [
                        'name' => 'getIdusers',
                        'method' => <<<PHP
                            /**
                             * Getter method for 'idusers'
                             *
                             * @return int|null
                             */
                            public function getIdusers(): ?int
                            {
                                return \$this->idusers;
                            }
                        PHP
                    ],
                    'setter' => (object) [
                        'name' => 'setIdusers',
                        'method' => <<<PHP
                            /**
                             * Setter method for 'idusers'
                             *
                             * @param int|null \$idusers
                             *
                             * @return Users
                             */
                            public function setIdusers(?int \$idusers = null): Users
                            {
                                \$this->idusers = \$idusers;

                                return \$this;
                            }
                        PHP
                    ],
                    'variable' => (object) [
                        'annotations' => (object) [
                            'class' => (object) [
                                'data_type' => "@property int \$idusers [Property for idusers]",
                                'data_type_with_null' => "@property int|null \$idusers [Property for idusers]",
                            ],
                        ],
                        'reference' => "\$this->idusers;",
                        'name' => (object) [
                            'camel' => ("protected \$idusers"),
                            'snake' => ("protected \$idusers")
                        ],
                        'type' => (object) [
                            'camel' => ("protected ?int \$idusers = null;"),
                            'snake' => (
                                "/**\n\t * [Property for idusers]\n\t *\n\t * @var int|null \$idusers\n\t */\n" .
                                "\tprotected ?int \$idusers = null;\n"
                            )
                        ],
                        'initialize' => (object) [
                            'camel' => ("protected \$idusers = null"),
                            'snake' => ("protected \$idusers = null")
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function getGetterProvider(): array
    {
        return [
            [
                'name' => 'idusers',
                'type' => 'int',
                'return' => (object) [
                    'name' => 'getIdusers',
                    'method' => <<<EOT
                        /**
                         * Getter method for 'idusers'
                         *
                         * @return int|null
                         */
                        public function getIdusers(): ?int
                        {
                            return \$this->idusers;
                        }
                    EOT
                ]
            ],
            [
                'name' => 'idusers',
                'type' => 'string',
                'return' => (object) [
                    'name' => 'getIdusers',
                    'method' => <<<EOT
                        /**
                         * Getter method for 'idusers'
                         *
                         * @return string|null
                         */
                        public function getIdusers(): ?string
                        {
                            return \$this->idusers;
                        }
                    EOT
                ]
            ]
        ];
    }

    public static function getSetterProvider(): array
    {
        return [
            [
                'name' => 'idusers',
                'type' => 'int',
                'capsule' => 'Users',
                'return' => (object) [
                    'name' => 'setIdusers',
                    'method' => <<<EOT
                        /**
                         * Setter method for 'idusers'
                         *
                         * @param int|null \$idusers
                         *
                         * @return Users
                         */
                        public function setIdusers(?int \$idusers = null): Users
                        {
                            \$this->idusers = \$idusers;

                            return \$this;
                        }
                    EOT
                ]
            ],
            [
                'name' => 'idusers',
                'type' => 'string',
                'capsule' => 'Users',
                'return' => (object) [
                    'name' => 'setIdusers',
                    'method' => <<<EOT
                        /**
                         * Setter method for 'idusers'
                         *
                         * @param string|null \$idusers
                         *
                         * @return Users
                         */
                        public function setIdusers(?string \$idusers = null): Users
                        {
                            \$this->idusers = \$idusers;

                            return \$this;
                        }
                    EOT
                ]
            ]
        ];
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     type: array{
     *          type: string,
     *          annotation: string
     *     }|string,
     *     params: string,
     *     content: string,
     *     visibility: string,
     *     lineBreak: int,
     *     return: string
     * }>
     */
    public static function getCustomMethodProvider(): array
    {
        return [
            [
                'name' => 'example',
                'type' => [
                    'type' => 'null',
                    'annotation' => 'null',
                ],
                'params' => '',
                'content' => 'return null;',
                'visibility' => 'public',
                'lineBreak' => 2,
                'return' => <<<PHP
                \t/**
                \t * Description of 'example'
                \t *
                \t * @return null
                \t */
                \tpublic function example(): null
                \t{
                \t\treturn null;
                \t}\n\n
                PHP
            ],
            [
                'name' => 'example',
                'type' => 'null',
                'params' => '',
                'content' => 'return null;',
                'visibility' => 'public',
                'lineBreak' => 2,
                'return' => <<<PHP
                \t/**
                \t * Description of 'example'
                \t *
                \t * @return null
                \t */
                \tpublic function example(): null
                \t{
                \t\treturn null;
                \t}\n\n
                PHP
            ],
            [
                'name' => 'example',
                'type' => 'null',
                'params' => '',
                'content' => 'return null;',
                'visibility' => 'public',
                'lineBreak' => 0,
                'return' => <<<PHP
                \t/**
                \t * Description of 'example'
                \t *
                \t * @return null
                \t */
                \tpublic function example(): null
                \t{
                \t\treturn null;
                \t}
                PHP
            ],
            [
                'name' => 'example',
                'type' => 'null',
                'params' => 'string $param1, int $param2',
                'content' => 'return null;',
                'visibility' => 'public',
                'lineBreak' => 2,
                'return' => <<<PHP
                \t/**
                \t * Description of 'example'
                \t *
                \t * @param string \$param1 [Parameter Description]
                \t * @param int \$param2 [Parameter Description]
                \t *
                \t * @return null
                \t */
                \tpublic function example(string \$param1, int \$param2): null
                \t{
                \t\treturn null;
                \t}\n\n
                PHP
            ],
            [
                'name' => 'example',
                'type' => 'string',
                'params' => 'string $param1',
                'content' => 'return $param1;',
                'visibility' => 'public',
                'lineBreak' => 2,
                'return' => <<<PHP
                \t/**
                \t * Description of 'example'
                \t *
                \t * @param string \$param1 [Parameter Description]
                \t *
                \t * @return string
                \t */
                \tpublic function example(string \$param1): string
                \t{
                \t\treturn \$param1;
                \t}\n\n
                PHP
            ],
            [
                'name' => 'example',
                'type' => 'array',
                'params' => 'array $param1',
                'content' => 'return [...$param1];',
                'visibility' => 'protected',
                'lineBreak' => 2,
                'return' => <<<PHP
                \t/**
                \t * Description of 'example'
                \t *
                \t * @param array \$param1 [Parameter Description]
                \t *
                \t * @return array
                \t */
                \tprotected function example(array \$param1): array
                \t{
                \t\treturn [...\$param1];
                \t}\n\n
                PHP
            ],
            [
                'name' => 'example',
                'type' => [
                    'type' => 'array',
                    'annotation' => 'array<int, string>',
                ],
                'params' => 'array $param1',
                'content' => 'return [...$param1];',
                'visibility' => 'protected',
                'lineBreak' => 2,
                'return' => <<<PHP
                \t/**
                \t * Description of 'example'
                \t *
                \t * @param array \$param1 [Parameter Description]
                \t *
                \t * @return array<int, string>
                \t */
                \tprotected function example(array \$param1): array
                \t{
                \t\treturn [...\$param1];
                \t}\n\n
                PHP
            ],
            [
                'name' => 'exampleLooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooongMethod',
                'type' => 'array',
                'params' => 'array $param1',
                'content' => 'return [...$param1];',
                'visibility' => 'protected',
                'lineBreak' => 2,
                'return' => <<<PHP
                \t/**
                \t * Description of 'exampleLooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooongMethod'
                \t *
                \t * @param array \$param1 [Parameter Description]
                \t *
                \t * @return array
                \t */
                \tprotected function exampleLooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooongMethod(array \$param1): array
                \t{
                \t\treturn [...\$param1];
                \t}\n\n
                PHP
            ],
        ];
    }

    public static function getClassFormatProvider(): array
    {
        return [
            [
                'className' => 'users-controller',
                'return' => 'UsersController'
            ],
            [
                'className' => 'users controller',
                'return' => 'UsersController'
            ],
            [
                'className' => 'users_model',
                'return' => 'UsersModel'
            ],
            [
                'className' => 'users model',
                'return' => 'UsersModel'
            ]
        ];
    }

    public static function getDBTypeProvider(): array
    {
        return [
            [
                'type' => 'varchar(100)',
                'return' => 'string',
            ],
            [
                'type' => 'BLOB',
                'return' => 'string',
            ],
            [
                'type' => 'ENUM("ONLINE", "OFFLINE")',
                'return' => 'string',
            ],
            [
                'type' => 'float',
                'return' => 'float',
            ],
            [
                'type' => 'FLOAT',
                'return' => 'float',
            ],
            [
                'type' => 'floAT',
                'return' => 'float',
            ],
            [
                'type' => 'FLOat',
                'return' => 'float',
            ],
            [
                'type' => 'int',
                'return' => 'int',
            ],
            [
                'type' => 'int(11)',
                'return' => 'int',
            ],
            [
                'type' => 'bigint(11)',
                'return' => 'int',
            ],
            [
                'type' => 'tinyint(1)',
                'return' => 'int',
            ],
            [
                'type' => 'TINYINT(1)',
                'return' => 'int',
            ],
            [
                'type' => 'TINYInt(1)',
                'return' => 'int',
            ],
            [
                'type' => 'double',
                'return' => 'float',
            ],
        ];
    }
}
