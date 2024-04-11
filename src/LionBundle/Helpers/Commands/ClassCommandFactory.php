<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Closure;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;

/**
 * Allows adding several ClassFactory type objects for multiple management
 *
 * @property Container $container [Container class object]
 * @property Store $store [Store class object]
 * @property array<ClassFactory> $factories [List of available factories]
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class ClassCommandFactory
{
    /**
     * [Container class object]
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
     * [List of available factories]
     *
     * @var array<ClassFactory> $factories;
     */
    private array $factories;

    /**
     * @required
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * @required
     */
    public function setStore(Store $store): void
    {
        $this->store = $store;
    }

    /**
     * Execute the process injecting the factories
     *
     * @param Closure $process [Function that executes the process]
     *
     * @return int
     */
    public function execute(Closure $process): int
    {
        return $process($this, $this->store);
    }

    /**
     * Gets the list of defined factories
     *
     * @return array<ClassFactory>
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * Injects objects of type ClassFactory
     *
     * @param array<string> $factories [List of available factories]
     *
     * @return ClassCommandFactory
     */
    public function setFactories(array $factories): ClassCommandFactory
    {
        foreach ($factories as $classFactory) {
            $this->factories[$classFactory] = $this->container->injectDependencies(new ClassFactory());
        }

        return $this;
    }

    /**
     * Get a factory by name
     *
     * @param string $factory [Factory name]
     *
     * @return ClassFactory
     */
    public function getFactory(string $factory): ClassFactory
    {
        return $this->factories[$factory];
    }

    /**
     * Gets the data obtained with the ClassFactory(folder, class, namespace)
     *
     * @param ClassFactory $classFactory [description]
     * @param array $data [description]
     *
     * @return object
     */
    public function getData(ClassFactory $classFactory, array $data): object
    {
        $classFactory->classFactory($data['path'], $data['class']);

        return (object) [
            'folder' => $classFactory->getFolder(),
            'class' => $classFactory->getClass(),
            'namespace' => $classFactory->getNamespace()
        ];
    }
}
