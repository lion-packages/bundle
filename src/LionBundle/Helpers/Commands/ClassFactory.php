<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

class ClassFactory
{
    private $content;
    private string $namespace;
    private string $class;

    public function create(
        string $fileName,
        string $extension = 'php',
        string $path = '',
        string $filePermissions = 'w+b'
    ): ClassFactory
    {
        $this->content = fopen("{$path}{$fileName}.{$extension}", $filePermissions);

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
        $namespace = '';
        $separate = explode('/', "{$path}{$fileName}");
        $size = count($separate);

        foreach ($separate as $key => $part) {
            if ($key === ($size - 1)) {
                $this->namespace = $namespace;
                $this->class = ucwords($part);
            } elseif ($key === ($size - 2)) {
                $namespace.= ucwords("$part");
            } else {
                $namespace.= ucwords("$part\\");
            }
        }

        return $this;
    }

    public function getPropierty(string $name, string $capsule, string $type = 'string'): object
    {
        $newName = str_replace(' ', '_', $name);
        $newName = str_replace('-', '_', $newName);
        $newName = str_replace('_', ' ', $newName);
        $newName = str_replace(' ', '', ucwords($newName));
        $newName = lcfirst($newName);

        return (object) [
            'format' => $newName,
            'name' => '$' . "{$newName} = null;",
            'type' => "?{$type} $" . "{$newName} = null;",
            'reference' => '$this->' . "{$newName};",
            'getter' => $this->getGetter($name, $type),
            'setter' => $this->getSetter($name, $type, $capsule)
        ];
    }

    public function getPublicPropierty(string $name, string $capsule, string $type = 'string'): object
    {
        $propierty = $this->getPropierty($name, $capsule, $type);

        return (object) [
            'format' => $propierty->format,
            'name' => "public {$propierty->name}",
            'type' => "public {$propierty->type}",
            'reference' => $propierty->reference,
            'getter' => $propierty->getter,
            'setter' => $propierty->setter
        ];
    }

    public function getPrivatePropierty(string $name, string $capsule, string $type = 'string'): object
    {
        $propierty = $this->getPropierty($name, $capsule, $type);

        return (object) [
            'format' => $propierty->format,
            'name' => "private {$propierty->name}",
            'type' => "private {$propierty->type}",
            'reference' => $propierty->reference,
            'getter' => $propierty->getter,
            'setter' => $propierty->setter
        ];
    }

    public function getProtectedPropierty(string $name, string $capsule, string $type = 'string'): object
    {
        $propierty = $this->getPropierty($name, $capsule, $type);

        return (object) [
            'format' => $propierty->format,
            'name' => "protected {$propierty->name}",
            'type' => "protected {$propierty->type}",
            'reference' => $propierty->reference,
            'getter' => $propierty->getter,
            'setter' => $propierty->setter
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
        return lcfirst(str_replace("\\", "/", $this->namespace)) . '/';
    }

    private function getGetter(string $name, string $type = 'string'): object
    {
        $newName = str_replace(' ', '_', $name);
        $newName = str_replace('-', '_', $newName);
        $newName = str_replace('_', ' ', $newName);
        $newName = trim(str_replace(' ', '', ucwords($newName)));

        $getter = "\tpublic function get{$newName}(): ?{$type}\n\t{\n\t\treturn " . '$this->' . lcfirst($newName);
        $getter .= ";\n\t}";

        return (object) ['name' => "get{$newName}", 'method' => $getter];
    }

    private function getSetter(string $name, string $type, string $capsule): object
    {
        $newName = str_replace(' ', '_', $name);
        $newName = str_replace('-', '_', $newName);
        $newName = str_replace('_', ' ', $newName);
        $newName = trim(str_replace(' ', '', ucwords($newName)));
        $camelName = lcfirst($newName);

        $setter = "\tpublic function set{$newName}(?{$type} $" . $camelName . "): {$capsule}\n\t{\n";
        $setter .= "\t\t" . '$this->' . "{$camelName} = $" . $camelName . ";\n\n\t\treturn " . '$this' . ";\n\t}";

        return (object) ['name' => "set{$newName}", 'method' => $setter];
    }

    public function getCustomMethod(
        string $name,
        string $type = 'object',
        string $params = '',
        string $content = 'return;',
        string $visibility = 'public',
        int $lineBreak = 2
    ): string
    {
        $method = "\t{$visibility} function {$name}({$params})" . ($type === '' ? '' : ": {$type}");
        $method .= "\n\t{\n\t\t{$content}\n\t}";
        $method .= str_repeat("\n", $lineBreak);

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
            return "int";
        } elseif (preg_match("/^float/", $type)) {
            return "float";
        } else {
            return "string";
        }
    }
}
