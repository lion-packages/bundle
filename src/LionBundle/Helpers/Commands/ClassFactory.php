<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Lion\Files\Store;

class ClassFactory
{
    const PUBLIC_PROPERTY = 'public';
    const PRIVATE_PROPERTY = 'private';
    const PROTECTED_PROPERTY = 'protected';

    /**
     * [Object of class Store]
     *
     * @var Store $store
     */
    private Store $store;

    private $content;
    private string $namespace;
    private string $class;

    /**
     * @required
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
    }

    public function create(
        string $fileName,
        string $extension = 'php',
        string $path = '',
        string $filePermissions = 'w+b'
    ): ClassFactory {
        $this->content = fopen($this->store->normalizePath("{$path}{$fileName}.{$extension}"), $filePermissions);

        return $this;
    }

    public function add(string $content): ClassFactory
    {
        fwrite($this->content, $content);

        return $this;
    }

    public function close(): ClassFactory
    {
        fflush($this->content);
        fclose($this->content);

        return $this;
    }

    public function classFactory(string $path, string $fileName): ClassFactory
    {
        $path = $this->store->normalizePath($path);
        $namespace = '';
        $separate = [];

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $separate = explode('\\', $this->store->normalizePath("{$path}{$fileName}"));
        } else {
            $separate = explode('/', $this->store->normalizePath("{$path}{$fileName}"));
        }

        $size = count($separate);

        foreach ($separate as $key => $part) {
            $part = str_replace('-', ' ', $part);
            $part = str_replace('_', ' ', $part);
            $part = str_replace(' ', '', ucwords($part));

            if ($key === ($size - 1)) {
                $this->namespace = $namespace;
                $this->class = $part;
            } elseif ($key === ($size - 2)) {
                $namespace.= $part;
            } else {
                $namespace.= "$part\\";
            }
        }

        return $this;
    }

    public function getProperty(
        string $name,
        string $capsule,
        string $type = 'string',
        ?string $visibility = null
    ): object {
        $availableVisibility = [self::PUBLIC_PROPERTY, self::PRIVATE_PROPERTY, self::PROTECTED_PROPERTY];
        $finalVisibility = in_array($visibility, $availableVisibility, true) ? "{$visibility} " : '';
        $snake = trim(str_replace('-', '_', str_replace(' ', '_', $name)));

        $camel = str_replace('_', ' ', str_replace('-', ' ', $name));
        $camel = lcfirst(str_replace(' ', '', ucwords($camel)));

        return (object) [
            'format' => (object) [
                'camel' => $camel,
                'snake' => $snake
            ],
            'getter' => $this->getGetter($snake, $type),
            'setter' => $this->getSetter($snake, $type, $capsule),
            'variable' => (object) [
                'reference' => '$this->' . "{$camel};",
                'name' => (object) [
                    'camel' => ($finalVisibility . '$' . $camel),
                    'snake' => ($finalVisibility . '$' . $snake)
                ],
                'type' => (object) [
                    'camel' => ($finalVisibility . "?{$type} $" . "{$camel} = null;"),
                    'snake' => ($finalVisibility . "?{$type} $" . "{$snake} = null;")
                ],
                'initialize' => (object) [
                    'camel' => ($finalVisibility . '$' . "{$camel} = null"),
                    'snake' => ($finalVisibility . '$' . "{$snake} = null")
                ]
            ]
        ];
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getFolder(): string
    {
        return $this->store->normalizePath(lcfirst(str_replace("\\", "/", $this->namespace)) . '/');
    }

    private function getGetter(string $name, string $type = 'string'): object
    {
        $newName = str_replace(' ', '_', $name);
        $newName = str_replace('-', '_', $newName);
        $newName = str_replace('_', ' ', $newName);
        $newName = trim(str_replace(' ', '', ucwords($newName)));

        $getter = "\tpublic function get{$newName}(): ?{$type}\n\t{\n\t\treturn " . '$this->' . $name;
        $getter .= ";\n\t}";

        return (object) ['name' => "get{$newName}", 'method' => $getter];
    }

    private function getSetter(string $name, string $type, string $capsule): object
    {
        $newName = str_replace(' ', '_', $name);
        $newName = str_replace('-', '_', $newName);
        $newName = str_replace('_', ' ', $newName);
        $newName = trim(str_replace(' ', '', ucwords($newName)));

        $setter = "\tpublic function set{$newName}(?{$type} $" . $name . "): {$capsule}\n\t{\n";
        $setter .= "\t\t" . '$this->' . "{$name} = $" . $name . ";\n\n\t\treturn " . '$this' . ";\n\t}";

        return (object) ['name' => "set{$newName}", 'method' => $setter];
    }

    public function getCustomMethod(
        string $name,
        string $type = 'object',
        string $params = '',
        string $content = 'return;',
        string $visibility = 'public',
        int $lineBreak = 2
    ): string {
        $method = '';
        $allCount = 16;
        $allCountWithType = 18;

        $countContentFunction = strlen($visibility) + strlen($name) + strlen($params);
        $countContentFunction += '' === $type ? 0 : strlen($type);
        $countContentFunction += '' === $type ? $allCount : $allCountWithType;

        if ($countContentFunction > 120) {
            $splitParams = explode(',', $params);
            $implodeParams = '';

            foreach ($splitParams as $key => $param) {
                $param = trim($param);
                $implodeParams .= $key === (count($splitParams) - 1) ? "\n\t\t{$param}" : "\n\t\t{$param},";
            }

            $method .= "\t{$visibility} function {$name}({$implodeParams}\n\t)". ($type === '' ? '' : ": {$type}");
            $method .=  " {\n\t\t{$content}\n\t}";
            $method .= str_repeat("\n", $lineBreak);
        } else {
            $method .= "\t{$visibility} function {$name}({$params})" . ($type === '' ? '' : ": {$type}");
            $method .= "\n\t{\n\t\t{$content}\n\t}";
            $method .= str_repeat("\n", $lineBreak);
        }

        return $method;
    }

    public function getClassFormat(string $className): string
    {
        $className = str_replace('_', ' ', $className);
        $className = str_replace('-', ' ', $className);
        $className = str_replace(':', ' ', $className);
        $className = str_replace('.', ' ', $className);
        $className = str_replace(',', ' ', $className);

        return trim(str_replace(' ', '', ucwords($className)));
    }

    public static function getDBType(string $type): string
    {
        if (preg_match("/^int|bigint/", $type)) {
            return 'int';
        } elseif (preg_match("/^float/", $type)) {
            return 'float';
        }

        return 'string';
    }
}
