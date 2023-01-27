<?php

namespace App\Console;

use Symfony\Component\Console\Application;

class Kernel {

    private array $commands = [
        \App\Console\Framework\ServerCommand::class,
        \App\Console\Framework\RunTestCommand::class,
        \App\Console\Framework\DB\CapsuleCommand::class,
        \App\Console\Framework\DB\SeedCommand::class,
        \App\Console\Framework\DB\FactoryCommand::class,
        \App\Console\Framework\DB\AllCapsulesCommand::class,
        \App\Console\Framework\Key\RSACommand::class,
        \App\Console\Framework\New\CapsuleCommand::class,
        \App\Console\Framework\New\CommandsCommand::class,
        \App\Console\Framework\New\ControllerCommand::class,
        \App\Console\Framework\New\MiddlewareCommand::class,
        \App\Console\Framework\New\ModelCommand::class,
        \App\Console\Framework\New\RulesCommand::class,
        \App\Console\Framework\New\TestCommand::class,
        \App\Console\Framework\New\TraitCommand::class,
        \App\Console\Framework\Route\RouteListCommand::class,
        \App\Console\Framework\Token\GenerateJWTCommand::class
    ];

    private Application $application;

    public function __construct() {
        $this->application = new Application(env->APP_NAME . ' ' . env->APP_VERSION);
    }

    public function add(): void {
        foreach ($this->commands as $key => $command) {
            $this->application->add(new $command());
        }
    }

    public function push(array $commands): void {
        foreach ($commands as $key => $command) {
            $this->application->add(new $command());
        }
    }

    public function run(): void {
        $this->application->run();
    }

}