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
            ]
        ];
    }

    public static function readFileRowsWithMultipleRowsProvider(): array
    {
        return [
            [
                'rows' => [
                    3 => [
                        'replace' => true,
                        'mutliple' => [
                            [
                                'search' => '-test',
                                'content' => ''
                            ],
                            [
                                'search' => 'Example',
                                'content' => 'Example-Test'
                            ]
                        ]

                    ]
                ],
                'return' => <<<JSON
                {
                    "name": "Test",
                    "last_name": "Example-Test"
                }
                JSON
            ],
            [
                'rows' => [
                    2 => [
                        'replace' => true,
                        'mutliple' => [
                            [
                                'search' => 'name',
                                'content' => 'full_name'
                            ],
                            [
                                'search' => 'Test',
                                'content' => 'Bundle'
                            ]
                        ]

                    ]
                ],
                'return' => <<<JSON
                {
                    "full_name": "Bundle",
                    "last_name": "Example-Test"
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
