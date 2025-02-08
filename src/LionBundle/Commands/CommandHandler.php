<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Command\Command;
use Lion\Dependency\Injection\Container;
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
     * [An Application is the container for a collection of commands]
     *
     * @var Application $application
     */
    private Application $application;

    /**
     * [Dependency Injection Container Wrapper]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * Class constructor
     *
     * @param string $name [Application name]
     */
    public function __construct(string $name = '')
    {
        $this->application = new Application($name);

        $this->application->setName($name);

        $this->container = new Container();

        $this->store = new Store();
    }

    /**
     * Add commands in the application
     *
     * @param array<int, Command> $commands [Command List]
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
     * @param string $pathCommands [Defined route]
     * @param string $namespace [Namespace for Command classes]
     * @param non-empty-string $pathSplit [Route separated]
     *
     * @return array<int, Command>
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function getCommands(string $pathCommands, string $namespace, string $pathSplit): array
    {
        /** @var array<int, Command> $commands */
        $commands = [];

        foreach ($this->store->getFiles($pathCommands) as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $class = $this->store->getNamespaceFromFile($file, $namespace, $pathSplit);

                /** @var Command $command */
                $command = $this->container->resolve($class);

                $commands[] = $command;
            }
        }

        return $commands;
    }

    /**
     * Record commands for a defined route
     *
     * @param string $pathCommands [Defined route]
     * @param string $namespace [Namespace for Command classes]
     * @param non-empty-string $pathSplit [Route separated]
     *
     * @return CommandHandler
     *
     * @throws DependencyException
     * @throws NotFoundException
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
