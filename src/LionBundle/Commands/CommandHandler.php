<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands;

use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Symfony\Component\Console\Application;

class CommandHandler
{
    private Application $application;
    private Container $container;
    private Store $store;

    public function __construct()
    {
        $this->application = (new Kernel)->getApplication();
        $this->container = new Container();
        $this->store = new Store();
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
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $class = $this->container->getNamespace($file, $namespace, $pathSplit);
                $commands[] = $this->container->injectDependencies(new $class());
            }
        }

        return $commands;
    }

    public function registerCommands(string $pathCommands, string $namespace, string $pathSplit): CommandHandler
    {
        $this->add($this->getCommands($pathCommands, $namespace, $pathSplit));

        return $this;
    }

    public function getApplication(): Application
    {
        return $this->application;
    }
}
