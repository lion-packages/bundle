<?php

/**
 * ------------------------------------------------------------------------------
 * Start commands for Lion-Framework function
 * ------------------------------------------------------------------------------
 * List of available commands
 * ------------------------------------------------------------------------------
 **/

return [
    'app' => [
        //
    ],
    'framework' => [
        \App\Console\Framework\DB\AllCapsulesCommand::class,
        \App\Console\Framework\DB\AllCrudCommand::class,
        \App\Console\Framework\DB\CapsuleCommand::class,
        \App\Console\Framework\DB\CrudCommand::class,
        \App\Console\Framework\DB\FactoryCommand::class,
        \App\Console\Framework\DB\SeedCommand::class,
        \App\Console\Framework\DB\ShowDatabasesCommand::class,
        // -----------------------------------------------------------------------------------------
        \App\Console\Framework\Key\RSACommand::class,
        \App\Console\Framework\Key\AESCommand::class,
        // -----------------------------------------------------------------------------------------
        \App\Console\Framework\New\CapsuleCommand::class,
        \App\Console\Framework\New\CommandsCommand::class,
        \App\Console\Framework\New\ControllerCommand::class,
        \App\Console\Framework\New\MiddlewareCommand::class,
        \App\Console\Framework\New\ModelCommand::class,
        \App\Console\Framework\New\RulesCommand::class,
        \App\Console\Framework\New\TestCommand::class,
        \App\Console\Framework\New\TraitCommand::class,
        \App\Console\Framework\New\EnumsCommand::class,
        // -----------------------------------------------------------------------------------------
        \App\Console\Framework\Route\RouteListCommand::class,
        \App\Console\Framework\Route\PostmanCollectionCommand::class,
        // -----------------------------------------------------------------------------------------
        \App\Console\Framework\Sockets\WebSocketsCommand::class,
        \App\Console\Framework\Sockets\RunWebSocketsCommand::class,
        // -----------------------------------------------------------------------------------------
        \App\Console\Framework\SSH\SSHFileCommand::class,
        // -----------------------------------------------------------------------------------------
        \App\Console\Framework\Token\GenerateJWTCommand::class,
        // -----------------------------------------------------------------------------------------
        \App\Console\Framework\InfoCommand::class,
        \App\Console\Framework\InitCommand::class,
        \App\Console\Framework\RunTestCommand::class,
        \App\Console\Framework\ServerCommand::class
    ]
];
