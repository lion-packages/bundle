<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Closure;
use DI\Attribute\Inject;
use DI\DependencyException;
use DI\NotFoundException;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use stdClass;

/**
 * Allows adding several ClassFactory type objects for multiple management
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class ClassCommandFactory
{
    /**
     * [Container to generate dependency injection]
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
     * [List of available factories]
     *
     * @var array<string, ClassFactory> $factories;
     */
    private array $factories;

    #[Inject]
    public function setContainer(Container $container): ClassCommandFactory
    {
        $this->container = $container;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): ClassCommandFactory
    {
        $this->store = $store;

        return $this;
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
        /** @var int $return */
        $return = $process($this, $this->store);

        return $return;
    }

    /**
     * Gets the list of defined factories
     *
     * @return array<string, ClassFactory>
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
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function setFactories(array $factories): ClassCommandFactory
    {
        foreach ($factories as $classFactory) {
            /** @var ClassFactory $classFactoryInstance */
            $classFactoryInstance = $this->container->resolve(ClassFactory::class);

            $this->factories[$classFactory] = $classFactoryInstance;
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
     * @param ClassFactory $classFactory [Fabricates the data provided to
     * manipulate information (folder, class, namespace)]
     * @param array{ path: string, class: string } $data [Class data]
     *
     * @return stdClass
     */
    public function getData(ClassFactory $classFactory, array $data): stdClass
    {
        $classFactory->classFactory($data['path'], $data['class']);

        return (object) [
            'folder' => $classFactory->getFolder(),
            'class' => $classFactory->getClass(),
            'namespace' => $classFactory->getNamespace(),
        ];
    }
}
