<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Symfony\Component\Console\Application;

/**
 * Initialize the command application to execute its functions
 *
 * @property Application $application [Application class object]
 * @property Container $container [Container class object]
 * @property Store $store [Store class object]
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
     * [Container to generate dependency injection]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Store class object]
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
        $this->application = (new Kernel)->getApplication();

        $this->container = new Container();

        $this->store = new Store();

        $this->application->setName($name);
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
     * @param string $pathCommands [Defined route]
     * @param string $namespace [Namespace for Command classes]
     * @param string $pathSplit [Route separated]
     *
     * @return array<Command>
     */
    private function getCommands(string $pathCommands, string $namespace, string $pathSplit): array
    {
        /** @var array<Command> $commands */
        $commands = [];

        foreach ($this->container->getFiles($pathCommands) as $file) {
            if (isSuccess($this->store->validate([$file], ['php']))) {
                $class = $this->container->getNamespace($file, $namespace, $pathSplit);
                $commands[] = $this->container->injectDependencies(new $class());
            }
        }

        return $commands;
    }

    /**
     * Record commands for a defined route
     *
     * @param string $pathCommands [Defined route]
     * @param string $namespace [Namespace for Command classes]
     * @param string $pathSplit [Route separated]
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
