<?php

return [
    'app' => [
        //
    ],
    'framework' => [
        'console-web' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7000,
            'path' => 'resources/console-web/'
        ],
        'available-urls' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7001,
            'path' => 'resources/available-urls/'
        ],
        'login' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7002,
            'path' => 'resources/login/'
        ],
        'user-registration' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7003,
            'path' => 'resources/user-registration/'
        ],
    ]
];
