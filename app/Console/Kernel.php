<?php

namespace App\Console;

use Symfony\Component\Console\Application;

class Kernel {

    private static Application $application;
    private static array $commands = [
        // -- list -- //
        \App\Console\Framework\ServerCommand::class,
        \App\Console\Framework\RunTestCommand::class,
        // -- db -- //
        \App\Console\Framework\DB\CapsuleCommand::class,
        \App\Console\Framework\DB\SeedCommand::class,
        \App\Console\Framework\DB\FactoryCommand::class,
        \App\Console\Framework\DB\AllCapsulesCommand::class,
        // -- key -- //
        \App\Console\Framework\Key\RSACommand::class,
        // -- New -- //
        \App\Console\Framework\New\CapsuleCommand::class,
        \App\Console\Framework\New\CommandsCommand::class,
        \App\Console\Framework\New\ControllerCommand::class,
        \App\Console\Framework\New\MiddlewareCommand::class,
        \App\Console\Framework\New\ModelCommand::class,
        \App\Console\Framework\New\RulesCommand::class,
        \App\Console\Framework\New\TestCommand::class,
        \App\Console\Framework\New\TraitCommand::class,
        // -- Route -- //
        \App\Console\Framework\Route\RouteListCommand::class,
        // -- Token -- //
        \App\Console\Framework\Token\GenerateJWTCommand::class
    ];

    private function __construct() {

    }

    public static function init(): void {
        self::$application = new Application(
            env->APP_NAME . ' ' . env->APP_VERSION
        );
    }

    public static function add(): void {
        foreach (self::$commands as $key => $command) {
            self::$application->add(new $command());
        }
    }

    public static function push(array $cmds): void {
        foreach ($cmds as $key => $command) {
            self::$application->add(new $command());
        }
    }

    public static function run(): void {
        self::$application->run();
    }

}