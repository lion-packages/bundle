<?php

/**
 * ------------------------------------------------------------------------------
 * Resources for developing your web application
 * ------------------------------------------------------------------------------
 * List of available resources
 * ------------------------------------------------------------------------------
 **/

return [
    'app' => [
        //
    ],
    'framework' => [
        'lion-dev' => [
            'type' => 'vite',
            'host' => '0.0.0.0',
            'path' => 'lion-dev/',
        ],
        'console-web' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7000,
            'path' => 'console-web/',
        ],
        'available-urls' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7001,
            'path' => 'available-urls/',
        ],
        'login' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7002,
            'path' => 'login/',
        ],
        'user-registration' => [
            'type' => 'twig',
            'host' => '0.0.0.0',
            'port' => 7003,
            'path' => 'user-registration/',
        ],
    ],
];
