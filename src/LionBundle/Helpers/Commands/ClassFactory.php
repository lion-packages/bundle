<?php

declare(strict_types=1);

namespace LionBundle\Helpers\Commands;

class ClassFactory
{
    private $content;

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

    public function classFactory(string $path, string $fileName): array
    {
        $namespace = "";
        $separate = explode("/", "{$path}{$fileName}");
        $size = count($separate);
        $list = [];

        foreach ($separate as $key => $part) {
            if ($key === ($size - 1)) {
                $list = [
                    'namespace' => $namespace,
                    'class' => ucwords($part)
                ];
            } elseif ($key === ($size - 2)) {
                $namespace.= ucwords("$part");
            } else {
                $namespace.= ucwords("$part\\");
            }
        }

        return $list;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): ClassFactory
    {
        $this->content = $content;
        return $this;
    }
}
