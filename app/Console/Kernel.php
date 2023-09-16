<?php

namespace App\Console;

use App\Traits\Framework\Singleton;
use Symfony\Component\Console\Application;

class Kernel
{
    use Singleton;

    private Application $application;
    private array $commands;
    private array $sockets;
    private array $vite_projects;

    public function initialize(array $commands, array $sockets, array $vite_projects): void
    {
        $this->application = new Application(env->APP_NAME);
        $this->commands = $commands;
        $this->sockets = $sockets;
        $this->vite_projects = $vite_projects;
    }

    public function add(): void
    {
        foreach ($this->commands as $key => $command) {
            $this->application->add(new $command());
        }
    }

    public function run(): void
    {
        $this->application->run();
    }

    public function getClass(string $class_name): string
    {
        return isset($this->sockets[$class_name]) ? $this->sockets[$class_name] : false;
    }

    public function getSockets(): array
    {
        return $this->sockets;
    }

    public function getViteProjects(): array
    {
        return $this->vite_projects;
    }

    public function execute(string $command, bool $index = true): array
    {
        $data = [];

        if (!$index) {
            exec($command, $data);
        } else {
            exec("cd ../ && {$command}", $data);
        }

        return $data;
    }

    public function command(string $command, bool $index = true): array
    {
        $data = [];

        if (!$index) {
            exec($command, $data);
        } else {
            exec("cd ../ && php lion {$command}", $data);
        }

        return $data;
    }
}
