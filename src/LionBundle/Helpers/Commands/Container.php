<?php

declare(strict_types=1);

namespace LionBundle\Helpers\Commands;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use LionHelpers\Str;
use ReflectionClass;
use ReflectionParameter;

class Container
{
    private DIContainer $container;
    private Str $str;

    public function __construct()
    {
        $this->container = (new ContainerBuilder())->useAutowiring(true)->useAttributes(true)->build();
        $this->str = new Str();
    }

    public function getFiles(string $folder): array
    {
        $files = [];
        $content = scandir($folder);

        foreach ($content as $element) {
            if ($element != '.' && $element != '..') {
                $path = $folder . '/' . $element;

                if (is_dir($path)) {
                    $files = array_merge($files, $this->getFiles($path));
                } else {
                    $files[] = realpath($path);
                }
            }
        }

        return $files;
    }

    public function getNamespace(string $file, string $namespace, string $split): string
    {
        $splitFile = explode($split, $file);

        return $this->str->of("{$namespace}{$splitFile[1]}")->replace("/", "\\")->replace('.php', '')->trim()->get();
    }

    public function injectDependencies(object $object): object
    {
        $reflectionClass = new ReflectionClass($object);

        foreach ($reflectionClass->getMethods() as $method) {
            $docDocument = $method->getDocComment();

            if (is_string($docDocument)) {
                if ((bool) preg_match('/@required/', $docDocument)) {
                    $parameters = $method->getParameters();
                    $listParameters = [];

                    foreach ($parameters as $parameter) {
                        $dependencyName = $this->getParameterClassName($parameter);
                        $listParameters[] = $this->container->get($dependencyName);
                    }

                    $method->invoke($object, ...$listParameters);
                }
            }
        }

        return $object;
    }

    private function getParameterClassName(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();

        return $type ? (string) $type : null;
    }
}
