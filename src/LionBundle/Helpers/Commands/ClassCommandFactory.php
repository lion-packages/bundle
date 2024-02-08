<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Closure;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Files\Store;

class ClassCommandFactory
{
    private array $factories;

    public function __construct(array $classFactoryCommands = [])
    {
        $this->addClassFactory($classFactoryCommands);
    }

    private function addClassFactory(array $classFactoryCommands): void
    {
        foreach ($classFactoryCommands as $classFactory) {
            $this->factories[$classFactory] = new ClassFactory();
        }
    }

    public function execute(Closure $process): int
    {
        return $process($this, new Store());
    }

    public function getFactories(): array
    {
        return $this->factories;
    }

    public function setFactories(array $factories): void
    {
        $this->addClassFactory($factories);
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
