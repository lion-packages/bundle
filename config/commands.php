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
        // AVAILABLE COMMANDS ----------------------------------------------------------------------
        \App\Console\Framework\InfoCommand::class,
        \App\Console\Framework\InitCommand::class,
        \App\Console\Framework\RunTestCommand::class,
        \App\Console\Framework\ServerCommand::class,
        // AES -------------------------------------------------------------------------------------
        \App\Console\Framework\AES\NewAESCommand::class,
        // DB --------------------------------------------------------------------------------------
        \App\Console\Framework\DB\AllCapsulesCommand::class,
        \App\Console\Framework\DB\AllCrudCommand::class,
        \App\Console\Framework\DB\AllRulesDBCommand::class,
        \App\Console\Framework\DB\CapsuleCommand::class,
        \App\Console\Framework\DB\SelectColumnsCommand::class,
        \App\Console\Framework\DB\CrudCommand::class,
        \App\Console\Framework\DB\ExportDatabaseCommand::class,
        \App\Console\Framework\DB\FactoryCommand::class,
        \App\Console\Framework\DB\RulesDBCommand::class,
        \App\Console\Framework\DB\SeedCommand::class,
        \App\Console\Framework\DB\SelectTableCommand::class,
        \App\Console\Framework\DB\ShowDatabasesCommand::class,
        // EMAIL -----------------------------------------------------------------------------------
        \App\Console\Framework\Email\ShowAccountsCommand::class,
        // MIGRATIONS ------------------------------------------------------------------------------
        \App\Console\Framework\Migrations\FreshMigrationsCommand::class,
        \App\Console\Framework\Migrations\GenerateMigrationsCommand::class,
        \App\Console\Framework\Migrations\NewMigrateCommand::class,
        // NEW -------------------------------------------------------------------------------------
        \App\Console\Framework\New\CapsuleCommand::class,
        \App\Console\Framework\New\CommandsCommand::class,
        \App\Console\Framework\New\ControllerCommand::class,
        \App\Console\Framework\New\EnumsCommand::class,
        \App\Console\Framework\New\InterfaceCommand::class,
        \App\Console\Framework\New\MiddlewareCommand::class,
        \App\Console\Framework\New\ModelCommand::class,
        \App\Console\Framework\New\RulesCommand::class,
        \App\Console\Framework\New\TestCommand::class,
        \App\Console\Framework\New\TraitCommand::class,
        // NPM -------------------------------------------------------------------------------------
        \App\Console\Framework\Npm\NpmInitCommand::class,
        \App\Console\Framework\Npm\NpmRunBuildCommand::class,
        \App\Console\Framework\Npm\NpmInstallCommand::class,
        \App\Console\Framework\Npm\NpmLogsCommand::class,
        \App\Console\Framework\Npm\NpmUninstallCommand::class,
        \App\Console\Framework\Npm\NpmUpdateCommand::class,
        // ROUTE -----------------------------------------------------------------------------------
        \App\Console\Framework\Route\RouteListCommand::class,
        \App\Console\Framework\Route\PostmanCollectionCommand::class,
        // RSA -------------------------------------------------------------------------------------
        \App\Console\Framework\RSA\NewRSACommand::class,
        // SOCKETS ---------------------------------------------------------------------------------
        \App\Console\Framework\Sockets\LogsSocket::class,
        \App\Console\Framework\Sockets\NewSocketCommand::class,
        \App\Console\Framework\Sockets\ServerSocketCommand::class,
        // SSH -------------------------------------------------------------------------------------
        \App\Console\Framework\SH\SHFileCommand::class,
        // TOKEN -----------------------------------------------------------------------------------
        \App\Console\Framework\Token\GenerateJWTCommand::class,
    ]
];
