<?php

namespace App\Console;

use Symfony\Component\Console\Application;

class Kernel {

    private array $commands;
    private array $socket_commands;

    private Application $application;

    public function __construct(array $commands, array $socket_commands) {
        $this->commands = $commands;
        $this->socket_commands = $socket_commands;
        $this->application = new Application(env->APP_NAME);
    }

    public function add(): Kernel {
        foreach ($this->commands as $key => $command) {
            $this->application->add(new $command());
        }

        return $this;
    }

    public function run(): Kernel {
        $this->application->run();
        return $this;
    }

    public function getClass(string $class_name): string {
        return $this->socket_commands[$class_name];
    }

}
