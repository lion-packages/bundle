<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Symfony\Component\Console\Application;

/**
 * Initialize the command application to execute its functions
 *
 * @package Lion\Bundle\Commands
 */
class CommandHandler
{
    /**
     * [Object of class Application]
     *
     * @var Application $application
     */
    private Application $application;

    /**
     * [Object of class Container]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Object of class Store]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->application = (new Kernel)->getApplication();
        $this->container = new Container();
        $this->store = new Store();
    }

    /**
     * Add commands in the application
     *
     * @param array<Command> $commands [Command List]
     */
    private function add(array $commands): void
    {
        foreach ($commands as $command) {
            $this->application->add($command);
        }
    }

    /**
     * Gets the list of routes with all available commands
     *
     * @param  string $pathCommands [Defined route]
     * @param  string $namespace [Namespace for Command classes]
     * @param  string $pathSplit [Route separated]
     *
     * @return array<Command>
     */
    private function getCommands(string $pathCommands, string $namespace, string $pathSplit): array
    {
        /**
         * @var array<Command> $commands
         */
        $commands = [];

        foreach ($this->container->getFiles($pathCommands) as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $class = $this->container->getNamespace($file, $namespace, $this->container->normalizePath($pathSplit));
                $commands[] = $this->container->injectDependencies(new $class());
            }
        }

        return $commands;
    }

    /**
     * Record commands for a defined route
     *
     * @param  string $pathCommands [Defined route]
     * @param  string $namespace [Namespace for Command classes]
     * @param  string $pathSplit [Route separated]
     *
     * @return CommandHandler
     */
    public function registerCommands(string $pathCommands, string $namespace, string $pathSplit): CommandHandler
    {
        $this->add($this->getCommands($pathCommands, $namespace, $pathSplit));

        return $this;
    }

    /**
     * Get the app
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }
}
