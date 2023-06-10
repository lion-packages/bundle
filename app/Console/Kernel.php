<?php

namespace App\Console;

use App\Traits\Framework\Singleton;
use Symfony\Component\Console\Application;

class Kernel {

    use Singleton;

    private array $commands;
    private array $socket_commands;

    private Application $application;

    public function initialize(array $commands, array $socket_commands): void {
        $this->commands = $commands;
        $this->socket_commands = $socket_commands;
        $this->application = new Application(env->APP_NAME);
    }

    public function add(): void {
        foreach ($this->commands as $key => $command) {
            $this->application->add(new $command());
        }
    }

    public function run(): void {
        $this->application->run();
    }

    public function getClass(string $class_name): string {
        return isset($this->socket_commands[$class_name]) ? $this->socket_commands[$class_name] : false;
    }

    public function execute(string $command, bool $index = true): array {
        $data = [];

        if (!$index) {
            exec($command, $data);
        } else {
            exec("cd ../ && {$command}", $data);
        }

        return $data;
    }

    public function command(string $command, bool $index = true) {
        $data = [];

        if (!$index) {
            exec($command, $data);
        } else {
            exec("cd ../ && php lion {$command}", $data);
        }

        return $data;
    }

}
