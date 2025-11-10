<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use DI\Attribute\Inject;
use Exception;
use Lion\Files\Store;
use stdClass;

/**
 * Fabricates the data provided to manipulate information (folder, class,
 * namespace)
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class ClassFactory
{
    /**
     * [.txt file extension]
     *
     * @const TXT_EXTENSION
     */
    public const string TXT_EXTENSION = 'txt';

    /**
     * [.json file extension]
     *
     * @const JSON_EXTENSION
     */
    public const string JSON_EXTENSION = 'json';

    /**
     * [.php file extension]
     *
     * @const PHP_EXTENSION
     */
    public const string PHP_EXTENSION = 'php';

    /**
     * [.log file extension]
     *
     * @const LOG_EXTENSION
     */
    public const string LOG_EXTENSION = 'log';

    /**
     * [.sh file extension]
     *
     * @const SH_EXTENSION
     */
    public const string SH_EXTENSION = 'sh';

    /**
     * [Scope of public method or property]
     *
     * @const PUBLIC_PROPERTY
     */
    public const string PUBLIC_PROPERTY = 'public';

    /**
     * [Scope of private method or property]
     *
     * @const PRIVATE_PROPERTY
     */
    public const string PRIVATE_PROPERTY = 'private';

    /**
     * [Scope of protected method or property]
     *
     * @const PROTECTED_PROPERTY
     */
    public const string PROTECTED_PROPERTY = 'protected';

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    /**
     * [If filename is of the form "scheme://...", it is assumed to be a URL and
     * PHP will search for a protocol handler (also known as a wrapper) for that
     * scheme. If no wrappers for that protocol are registered, PHP will emit a
     * notice to help you track potential problems in your script and then
     * continue as though filename specifies a regular file]
     *
     * [If PHP has decided that filename specifies a local file, then it will
     * try to open a stream on that file. The file must be accessible to PHP, so
     * you need to ensure that the file access permissions allow this access. If
     * you have enabled open_basedir further restrictions may apply]
     *
     * [If PHP has decided that filename specifies a registered protocol, and
     * that protocol is registered as a network URL, PHP will check to make sure
     * that allow_url_fopen is enabled. If it is switched off, PHP will emit a
     * warning and the fopen call will fail]
     *
     * @var resource $content
     */
    private $content;

    /**
     * [Class namespace]
     *
     * @var string $namespace
     */
    private string $namespace;

    /**
     * [Class name]
     *
     * @var string $class
     */
    private string $class;

    #[Inject]
    public function setStore(Store $store): ClassFactory
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Create a new file with its properties and permissions defined
     *
     * @param string $fileName File name
     * @param string $extension File extension
     * @param string $path File path
     * @param string $filePermissions File permissions
     *
     * @return ClassFactory
     *
     * @throws Exception If the file could not be opened
     */
    public function create(
        string $fileName,
        string $extension = self::PHP_EXTENSION,
        string $path = '',
        string $filePermissions = 'w+b'
    ): ClassFactory {
        $content = fopen($this->store->normalizePath("{$path}{$fileName}.{$extension}"), $filePermissions);

        if (false === $content) {
            throw new Exception("Could not open file: {$path}{$fileName}.{$extension}", 1001);
        }

        $this->content = $content;

        return $this;
    }

    /**
     * Add content to the file
     *
     * @param string $content [File content]
     *
     * @return ClassFactory
     */
    public function add(string $content): ClassFactory
    {
        fwrite($this->content, $content);

        return $this;
    }

    /**
     * Close the file with the defined data
     *
     * @return ClassFactory
     */
    public function close(): ClassFactory
    {
        fflush($this->content);

        fclose($this->content);

        return $this;
    }

    /**
     * Generates the information required for the classes (class, namespace,
     * folder).
     *
     * @param string $path File path.
     * @param string $fileName File name.
     *
     * @return ClassFactory
     */
    public function classFactory(string $path, string $fileName): ClassFactory
    {
        $path = $this->store->normalizePath($path);

        $namespace = '';

        $separate = explode('/', $this->store->normalizePath("{$path}{$fileName}"));

        $size = count($separate);

        foreach ($separate as $key => $part) {
            $part = str_replace('-', ' ', $part);

            $part = str_replace('_', ' ', $part);

            $part = str_replace(' ', '', ucwords($part));

            if ($key === ($size - 1)) {
                $this->namespace = $namespace;

                $this->class = $part;
            } elseif ($key === ($size - 2)) {
                $namespace .= $part;
            } else {
                $namespace .= "$part\\";
            }
        }

        return $this;
    }

    /**
     * Generates the content (property name in [camel, pascal, snake] formats) of a
     * property for getter, setter and class property methods
     *
     * @param string $propertyName Property name
     * @param string $className Class name
     * @param string $type Datatype
     * @param string|null $visibility Property Scope
     * @param string|null $customInterface
     *
     * @return stdClass
     */
    public function getProperty(
        string $propertyName,
        string $className,
        string $type = 'string',
        ?string $visibility = null,
        ?string $customInterface = null
    ): stdClass {
        $availableVisibility = [
            self::PUBLIC_PROPERTY,
            self::PRIVATE_PROPERTY,
            self::PROTECTED_PROPERTY,
        ];

        $finalVisibility = in_array($visibility, $availableVisibility, true) ? "{$visibility} " : '';

        $snake = trim(str_replace('-', '_', str_replace(' ', '_', $propertyName)));

        $camel = str_replace('_', ' ', str_replace('-', ' ', $propertyName));

        $camel = lcfirst(str_replace(' ', '', ucwords($camel)));

        $pascal = ucwords($camel);

        return (object) [
            'customInterface' => $customInterface,
            'property' => $propertyName,
            'format' => (object) [
                'camel' => $camel,
                'pascal' => $pascal,
                'snake' => $snake,
            ],
            'getter' => $this->getGetter($snake, $type),
            'setter' => $this->getSetter($snake, $type, $className),
            'abstract' => $this->getAbstractCapsuleMethod($snake),
            'variable' => (object) [
                'data_type' => $type,
                'annotations' => (object) [
                    'class' => (object) [
                        'data_type' => "@property {$type} $" . "{$snake} Property for {$propertyName}",
                        'data_type_with_null' => "@property {$type}|null $" . "{$snake} Property for {$propertyName}",
                    ],
                ],
                'reference' => '$this->' . "{$camel};",
                'name' => (object) [
                    'camel' => ($finalVisibility . '$' . $camel),
                    'snake' => ($finalVisibility . '$' . $snake),
                ],
                'type' => (object) [
                    'camel' => (
                        <<<EOT
                            /**
                             * Property for '{$propertyName}'
                             *
                             * @var {$type}|null \${$camel}
                             */
                            {$finalVisibility}?{$type} \${$camel} = null;


                        EOT
                    ),
                    'snake' => (
                        <<<EOT
                            /**
                             * Property for '{$propertyName}'
                             *
                             * @var {$type}|null \${$snake}
                             */
                            {$finalVisibility}?{$type} \${$snake} = null;


                        EOT
                    ),
                ],
                'initialize' => (object) [
                    'camel' => ($finalVisibility . '$' . "{$camel} = null"),
                    'snake' => ($finalVisibility . '$' . "{$snake} = null"),
                ],
            ],
        ];
    }

    /**
     * Returns the name of the class
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Returns the namespace of the class
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Gets the folder path of the class
     *
     * @return string
     */
    public function getFolder(): string
    {
        return $this->store->normalizePath(lcfirst(str_replace("\\", "/", $this->namespace)) . '/');
    }

    /**
     * Generate a getter method with its definitions
     *
     * @param string $name [Method name]
     * @param string $type [Method type]
     *
     * @return stdClass
     */
    private function getGetter(string $name, string $type = 'string'): stdClass
    {
        $newName = str_replace(' ', '_', $name);

        $newName = str_replace('-', '_', $newName);

        $newName = str_replace('_', ' ', $newName);

        $newName = trim(str_replace(' ', '', ucwords($newName)));

        $getter = <<<EOT
            /**
             * {@inheritDoc}
             */
            public function get{$newName}(): ?{$type}
            {
                return \$this->{$name};
            }
        EOT;

        return (object) [
            'name' => "get{$newName}",
            'type' => $type,
            'method' => $getter
        ];
    }

    /**
     * Generate a setter method with its definitions
     *
     * @param string $name [Method name]
     * @param string $type [Method type]
     * @param string $capsule [Class name]
     *
     * @return stdClass
     */
    private function getSetter(string $name, string $type, string $capsule): stdClass
    {
        $newName = str_replace(' ', '_', $name);

        $newName = str_replace('-', '_', $newName);

        $newName = str_replace('_', ' ', $newName);

        $newName = trim(str_replace(' ', '', ucwords($newName)));

        $setter = <<<PHP
            /**
             * {@inheritDoc}
             */
            public function set{$newName}(?{$type} \${$name} = null): static
            {
                \$this->{$name} = \${$name};

                return \$this;
            }
        PHP;

        return (object) [
            'name' => "set{$newName}",
            'type' => $capsule,
            'method' => $setter
        ];
    }

    /**
     * Generates the abstract methods of the capsule classes
     *
     * @param string $column Column name
     *
     * @return stdClass
     */
    public function getAbstractCapsuleMethod(string $column): stdClass
    {
        $newName = str_replace(' ', '_', $column);

        $newName = str_replace('-', '_', $newName);

        $newName = str_replace('_', ' ', $newName);

        $newName = trim(str_replace(' ', '', ucwords($newName)));

        $setter = <<<PHP
            /**
             * {@inheritDoc}
             */
            public static function get{$newName}Column(): string
            {
                return '{$column}';
            }
        PHP;

        return (object) [
            'name' => "get{$newName}Column",
            'type' => 'string',
            'method' => $setter
        ];
    }

    /**
     * Generate a custom method with its definitions
     *
     * @param string $name [Method name]
     * @param array{
     *     type: string,
     *     annotation: string
     * }|string $type [Method type]
     * @param string $params [Method parameters]
     * @param string $content [Method content]
     * @param string $visibility [Scope of the method]
     * @param int $lineBreak [Number of line breaks after the method]
     *
     * @return string
     */
    public function getCustomMethod(
        string $name,
        array|string $type = '',
        string $params = '',
        string $content = 'return;',
        string $visibility = 'public',
        int $lineBreak = 2
    ): string {
        $methodTypeAnnotation = '';

        $methodType = '';

        if (is_string($type)) {
            $methodType = $type === '' ? ': void' : ": {$type}";

            $splitMethodType = explode(':', $methodType);

            $methodTypeAnnotation = trim(array_pop($splitMethodType));
        }

        if (is_array($type)) {
            $methodType = $type['type'] === '' ? ': void' : ": {$type['type']}";

            $methodTypeAnnotation = $type['annotation'];
        }

        $paramsAnnotation = '';

        $paramsSize = $params === '' ? 0 : count(explode(',', $params));

        if ($paramsSize > 1) {
            foreach (explode(',', $params) as $key => $param) {
                $split = explode('=', $param);

                $param = isset($split[1]) ? trim($split[0]) : trim($param);

                $paramsAnnotation .= $key === ($paramsSize - 1)
                    ? <<<EOT
                         * @param {$param} Parameter Description
                    EOT
                    : <<<EOT
                    * @param {$param} Parameter Description

                    EOT;
            }
        } elseif ($paramsSize === 1) {
            $split = explode('=', $params);

            $params = isset($split[1]) ? trim($split[0]) : trim($params);

            $paramsAnnotation .= <<<EOT
            * @param {$params} Parameter Description
            EOT;
        }

        $method = <<<EOT
            /**
             * Description of '{$name}'
             *

        EOT;

        if ($paramsAnnotation != '') {
            $method .= <<<EOT
                 {$paramsAnnotation}
                 *

            EOT;
        }

        $method .= <<<EOT
             * @return {$methodTypeAnnotation}
             */
            {$visibility} function {$name}({$params}){$methodType}
            {
                $content
            }
        EOT;

        $method .= str_repeat("\n", $lineBreak);

        return $method;
    }

    /**
     * Format Pascal-Case to generate class names
     *
     * @param string $className [Class name]
     *
     * @return string
     */
    public function getClassFormat(string $className): string
    {
        $className = str_replace('_', ' ', $className);

        $className = str_replace('-', ' ', $className);

        $className = str_replace(':', ' ', $className);

        $className = str_replace('.', ' ', $className);

        $className = str_replace(',', ' ', $className);

        return trim(str_replace(' ', '', ucwords($className)));
    }

    /**
     * Gets the data type of property with the data type of the entity
     * property
     *
     * @param string $type [Datatype]
     *
     * @return string
     */
    public function getDBType(string $type): string
    {
        if (preg_match("/int/i", $type)) {
            return 'int';
        } elseif (preg_match("/float/i", $type)) {
            return 'float';
        } elseif (preg_match("/double/i", $type)) {
            return 'float';
        }

        return 'string';
    }

    /**
     * Determines whether a file exists or can be skipped
     *
     * @param string $fileExtension File extension
     *
     * @return bool
     */
    public function omit(string $fileExtension): bool
    {
        return isSuccess($this->store->exist($this->getFolder() . $this->getClass() . ".{$fileExtension}"));
    }
}
