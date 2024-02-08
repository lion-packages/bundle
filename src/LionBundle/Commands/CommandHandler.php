<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands;

use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Symfony\Component\Console\Application;

class CommandHandler
{
    private Application $application;
    private Container $container;

    public function __construct(bool $isNpmCommands = false)
    {
        $this->container = new Container();
        $this->application = (new Kernel)->getApplication();

        $this->add(
            !$isNpmCommands
                ? $this->getCommands('./src/LionBundle/Commands/Lion/', 'Lion\\Bundle\\Commands\\Lion\\', 'Lion/')
                : $this->getCommands('./src/LionBundle/Commands/Npm/', 'Lion\\Bundle\\Commands\\Npm\\', 'Npm/')
        );
    }

    private function add(array $commands): void
    {
        foreach ($commands as $command) {
            $this->application->add($command);
        }
    }

    private function getCommands(string $pathCommands, string $namespace, string $pathSplit): array
    {
        $commands = [];

        foreach ($this->container->getFiles($pathCommands) as $file) {
            $class = $this->container->getNamespace($file, $namespace, $pathSplit);
            $commands[] = $this->container->injectDependencies(new $class());
        }

        return $commands;
    }

    public function registerCommands(string $pathCommands, string $namespace, string $pathSplit): void
    {
        $this->add($this->getCommands($pathCommands, $namespace, $pathSplit));
    }

    public function getApplication(): Application
    {
        return $this->application;
    }
}
