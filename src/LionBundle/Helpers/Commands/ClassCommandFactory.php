<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Closure;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;

/**
 * Allows adding several ClassFactory type objects for multiple management
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

    private function addClassFactory(array $classFactoryCommands): void
    {
        foreach ($classFactoryCommands as $classFactory) {
            $this->factories[$classFactory] = $this->container->injectDependencies(new ClassFactory());
        }
    }

    public function execute(Closure $process): int
    {
        return $process($this, $this->store);
    }

    public function getFactories(): array
    {
        return $this->factories;
    }

    public function setFactories(array $factories): ClassCommandFactory
    {
        $this->addClassFactory($factories);

        return $this;
    }

    public function getFactory(string $factory): ClassFactory
    {
        return $this->factories[$factory];
    }

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
