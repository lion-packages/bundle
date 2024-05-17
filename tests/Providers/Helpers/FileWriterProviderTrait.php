<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

trait FileWriterProviderTrait
{
    public static function replaceContentProvider(): array
    {
        return [
            [
                'row' => [
                    'search' => 'Search',
                    'content' => 'Replacement'
                ],
                'modifiedLine' => 'Original Replacement Line',
                'originalLine' => 'Original Search Line',
            ],
        ];
    }

    public static function readFileRowsProvider(): array
    {
        return [
            [
                'rows' => [
                    3 => [
                        'replace' => true,
                        'search' => '-test',
                        'content' => ''
                    ]
                ],
                'return' => <<<JSON
                {
                    "name": "Test",
                    "last_name": "Example"
                }
                JSON
            ],
            [
                'rows' => [
                    2 => [
                        'replace' => true,
                        'search' => 'Test',
                        'content' => 'Root'
                    ]
                ],
                'return' => <<<JSON
                {
                    "name": "Root",
                    "last_name-test": "Example"
                }
                JSON
            ],
            [
                'rows' => [
                    2 => [
                        'replace' => false,
                        'content' => <<<JSON
                            "name": "Root Test",

                        JSON
                    ]
                ],
                'return' => <<<JSON
                {
                    "name": "Root Test",
                    "last_name-test": "Example"
                }
                JSON
            ]
        ];
    }

    public static function readFileRowsWithMultipleRowsProvider(): array
    {
        return [
            [
                'rows' => [
                    2 => [
                        'replace' => true,
                        'multiple' => [
                            [
                                'search' => 'name',
                                'content' => 'first_name'
                            ],
                            [
                                'search' => 'Test',
                                'content' => 'Testing'
                            ]
                        ]

                    ]
                ],
                'return' => <<<JSON
                {
                    "first_name": "Testing",
                    "last_name-test": "Example"
                }
                JSON
            ],
            [
                'rows' => [
                    3 => [
                        'replace' => true,
                        'multiple' => [
                            [
                                'search' => '-test',
                                'content' => ''
                            ],
                            [
                                'search' => 'Example',
                                'content' => 'Testing'
                            ]
                        ]

                    ]
                ],
                'return' => <<<JSON
                {
                    "name": "Test",
                    "last_name": "Testing"
                }
                JSON
            ],
        ];
    }

    public static function readFileRowsRemoveRowsProvider(): array
    {
        return [
            [
                'rows' => [
                    2 => [
                        'replace' => true,
                        'search' => ',',
                        'content' => ''
                    ],
                    3 => [
                        'remove' => true
                    ]
                ],
                'return' => <<<JSON
                {
                    "name": "Test"
                }
                JSON
            ],
            [
                'rows' => [
                    2 => [
                        'remove' => true
                    ],
                    3 => [
                        'replace' => true,
                        'search' => '-test',
                        'content' => ''
                    ],
                ],
                'return' => <<<JSON
                {
                    "last_name": "Example"
                }
                JSON
            ]
        ];
    }
}
