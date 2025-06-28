<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Capsule;

use Closure;
use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use stdClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manages the configuration and structure of a generated capsule class
 *
 * @package Lion\Bundle\Helpers\Commands\Capsule
 */
class CapsuleFactory
{
    /**
     * Fabricates the data provided to manipulate information (folder, class,
     * namespace)
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * Modify and construct strings with different formats
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * Modify and build arrays with different indexes or values
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * @var array{
     *     properties: array<int, string>,
     *     methods: array<int, array{
     *          property: string,
     *          getter: string,
     *          setter: string,
     *          abstract: string,
     *          config: stdClass
     *     }>
     * } $capsuleData
     */
    private array $capsuleData = [
        'properties' => [],
        'methods' => [],
    ];

    /**
     * OutputInterface is the interface implemented by all Output classes
     *
     * @var OutputInterface $output
     */
    private OutputInterface $output;

    /**
     * An Application is the container for a collection of commands
     *
     * @var Application $application
     */
    private Application $application;

    /**
     * Class that allows writing system files
     *
     * @var FileWriter $fileWriter
     */
    private FileWriter $fileWriter;

    /**
     * Class name
     *
     * @var string $class
     */
    private string $class;

    /**
     * Class namespace
     *
     * @var string $namespace
     */
    private string $namespace;

    /**
     * Entity name
     *
     * @var string $entity
     */
    private string $entity;

    /**
     * List of interfaces
     *
     * @var array<int, string> $interfaces
     */
    private array $interfaces = [];

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): CapsuleFactory
    {
        $this->classFactory = $classFactory;

        return $this;
    }

    #[Inject]
    public function setFileWriter(FileWriter $fileWriter): CapsuleFactory
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    #[Inject]
    public function setStr(Str $str): CapsuleFactory
    {
        $this->str = $str;

        return $this;
    }

    #[Inject]
    public function setArr(Arr $arr): CapsuleFactory
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * @param OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output): CapsuleFactory
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Add the app to settings
     *
     * @param Application $application An Application is the container for a
     * collection of commands
     *
     * @return CapsuleFactory
     */
    public function setApplication(Application $application): CapsuleFactory
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Add the name of the class
     *
     * @param string $class Class name
     *
     * @return CapsuleFactory
     */
    public function setClass(string $class): CapsuleFactory
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Add the class namespace
     *
     * @param string $namespace Class namespace
     *
     * @return CapsuleFactory
     */
    public function setNamespace(string $namespace): CapsuleFactory
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Add the name of the entity
     *
     * @param string $entity Entity name
     *
     * @return $this
     */
    public function setEntity(string $entity): CapsuleFactory
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Returns the body of the generated class
     *
     * @return string
     */
    public function getBody(): string
    {
        /** @var string $body */
        $body = $this->str->get();

        return $body;
    }

    /**
     * Gets the methods of the capsule class
     *
     * @return array<int, array{
     *     getter: string,
     *     setter: string,
     *     config: stdClass
     * }>
     */
    public function getCapsuleMethods(): array
    {
        return $this->capsuleData['methods'];
    }

    /**
     * Gets the properties of the capsule class
     *
     * @return array<int, string>
     */
    public function getCapsuleProperties(): array
    {
        return $this->capsuleData['properties'];
    }

    /**
     * Returns the object that constructs the body
     *
     * @return Str
     */
    public function getStr(): Str
    {
        return $this->str;
    }

    /**
     * Defines the properties of the class with its data type
     *
     * @param string $propertyDef Defined property
     *
     * @return array{
     *      property: string,
     *      type: string,
     *      interface: string
     * }
     */
    private function parsePropertyDefinition(string $propertyDef): array
    {
        /**
         * @var string $property
         * @var string|null $type
         * @var string $interface
         */
        [$property, $type, $interface] = array_pad(explode(':', $propertyDef, 3), 3, null);

        return [
            'property' => $property,
            'type' => $type ?? 'string',
            'interface' => $interface,
        ];
    }

    /**
     * Iterates the list of data to generate the Getter and Setter methods
     *
     * @param string $class Class name
     * @param array<int, string> $properties List of class properties
     *
     * @return void
     */
    public function generateMethods(string $class, array $properties): void
    {
        foreach ($properties as $propertyDef) {
            $parsed = $this->parsePropertyDefinition($propertyDef);

            $data = $this->classFactory->getProperty(
                $parsed['property'],
                $class,
                $parsed['type'],
                ClassFactory::PRIVATE_PROPERTY
            );

            /** @var stdClass $config */
            $config = $data;

            // Guárdalo en la configuración para reutilizarlo en generateInterfaces

            $config->customInterface = $parsed['interface'];

            $this->capsuleData['properties'][] = $config->variable->type->snake;

            $this->capsuleData['methods'][] = [
                'property' => $parsed['property'],
                'getter' => $data->getter->method,
                'setter' => $data->setter->method,
                'abstract' => $data->abstract->method,
                'config' => $config,
            ];

            // 🔁 Agrega la interfaz (personalizada o generada) al listado global si no existe
            if (!empty($config->customInterface)) {
                $fqcn = $config->customInterface;
            } else {
                $pascal = $config->format->pascal;
                $fqcn = "App\\Interfaces\\{$this->namespace}\\{$this->class}\\{$pascal}Interface";
            }

            if (!in_array($fqcn, $this->interfaces, true)) {
                $this->interfaces[] = $fqcn;
            }
        }
    }

    /**
     * Add the class namespace to the body
     *
     * @return void
     */
    public function addNamespace(): void
    {
        $this->str
            ->of(
                <<<PHP
                    <?php

                    declare(strict_types=1);

                    namespace {$this->namespace};


                    PHP
            );
    }

    /**
     * Add the class and its implementations to the body
     *
     * @return void
     */
    public function addingClassAndImplementations(): void
    {
        $interfaceImports = [];
        $interfaceNames = [];

        foreach ($this->capsuleData['methods'] as $method) {
            $config = $method['config'];
            $customInterface = $config->customInterface ?? null;

            if ($customInterface) {
                $interfaceImports[] = $customInterface;
                $interfaceNames[] = '\\' . ltrim($customInterface, '\\'); // asegura formato para implements
            } else {
                // Si no hay una interfaz personalizada, generamos la por defecto
                $format = $config->format;
                $pascal = $format->pascal;
                $defaultInterface = "App\\Interfaces\\{$this->namespace}\\{$this->class}\\{$pascal}Interface";
                $interfaceImports[] = $defaultInterface;
                $interfaceNames[] = "\\{$defaultInterface}";
            }
        }

        // Elimina duplicados
        $interfaceImports = array_unique($interfaceImports);
        $interfaceNames = array_unique($interfaceNames);

        // Genera el bloque use
        $useStatements = implode("\n", array_map(function (string $fqcn): string {
            return "use {$fqcn};";
        }, $interfaceImports));

        $implements = implode(",\n    ", array_map(function ($fqcn) {
            return basename(str_replace('\\', '/', $fqcn));
        }, $interfaceNames));

        $this->str->concat(
            <<<PHP
        {$useStatements}
        use Lion\Bundle\Interface\CapsuleInterface;
        use Lion\Bundle\Traits\CapsuleTrait;


        PHP
        );

        // Agrega clase con interfaces
        $this->str->concat(
            <<<PHP
        /**
         * Capsule for the '{$this->entity}' entity
         */
        class {$this->class} implements
            CapsuleInterface,
            {$implements}
        {
            use CapsuleTrait;

            /**
             * Entity name
             *
             * @var string \$entity
             *
             * @phpstan-ignore-next-line
             */
            private static string \$entity = '{$this->entity}';


        PHP
        );
    }

    /**
     * Add the interfaces for each property
     *
     * @return void
     *
     * @throws ExceptionInterface
     */
    public function generateInterfaces(): void
    {
        $command = $this->application->find('new:interface');

        $generatedInterfaces = [];

        foreach ($this->capsuleData['methods'] as $method) {
            /** @var stdClass $config */
            $config = $method['config'];

            if (!empty($config->customInterface)) {
                // Solo registrar para usar más adelante (use, implements...)
                $interfaceNamespace = $config->customInterface;

                // Opcional: verificar si realmente existe el archivo
                $relativePath = str_replace('App\\Interfaces\\', '', $interfaceNamespace);
                $interfacePath = 'app/Interfaces/' . str_replace('\\', '/', $relativePath) . '.php';

                if (!file_exists($interfacePath)) {
                    $this->output->writeln("<comment>⚠️ Interfaz personalizada '{$interfaceNamespace}' no existe en '{$interfacePath}'</comment>");
                }

                // Nunca la generes aquí
                continue;
            }

            // Desde aquí se generan SOLO las interfaces locales a esta cápsula

            $property = $config->property;
            $format = $config->format;
            $getter = $config->getter;
            $setter = $config->setter;
            $abstract = $config->abstract;

            $pascal = $format->pascal;
            $interface = "{$pascal}Interface";
            $interfaceNamespace = "{$this->namespace}\\{$this->class}\\{$interface}";
            $interfacePath = "app/Interfaces/{$this->namespace}/{$this->class}/{$interface}.php";
            $interfacePath = str_replace('\\', '/', $interfacePath);

            // Evita duplicados
            if (in_array($interfaceNamespace, $generatedInterfaces)) {
                continue;
            }

            $generatedInterfaces[] = $interfaceNamespace;

            // Generar interfaz normalmente
            $command->run(new ArrayInput([
                'interface' => str_replace('\\', '/', $interfaceNamespace),
            ]), $this->output);

            // Inserta contenido personalizado
            $this->fileWriter->readFileRows($interfacePath, [
                6 => [
                    'replace' => false,
                    'content' => <<<PHP

                use {$this->namespace}\\{$this->class};


                PHP
                ],
                8 => [
                    'replace' => true,
                    'search' => "Description of the '{$interface}' interface",
                    'content' => "Interface of the '{$property}' property",
                ],
                11 => [
                    'replace' => false,
                    'content' => <<<PHP
                {
                    /**
                     * Gets the name of the column '{$property}'
                     *
                     * @return string
                     */
                    public static function {$abstract->name}(): string;

                    /**
                     * Getter method for '{$property}'
                     *
                     * @return {$getter->type}|null
                     */
                    public function {$getter->name}(): ?{$getter->type};

                    /**
                     * Setter method for '{$property}'
                     *
                     * @param {$getter->type}|null \${$property} Property for '{$property}'
                     *
                     * @return static
                     */
                    public function {$setter->name}(?{$getter->type} \${$property}): static;

                PHP,
                ],
            ]);

            $this->fileWriter->readFileRows($interfacePath, [
                11 => [
                    'remove' => true,
                ],
            ]);

            $this->fileWriter->readFileRows($interfacePath, [
                11 => [
                    'replace' => true,
                    'search' => 'interface',
                    'content' => <<<PHP
                     */
                    interface
                    PHP,
                ],
            ]);
        }
    }

    /**
     * Adds the class properties to the body
     *
     * @return void
     */
    public function addProperties(): void
    {
        if (count($this->capsuleData['properties']) > 0) {
            $properties = trim(
                $this->arr
                    ->of($this->capsuleData['properties'])
                    ->join('')
            );

            $this->str
                ->concat(
                    <<<EOT
                        {$properties}


                    EOT
                );
        }
    }

    /**
     * Add the abstract methods to the body
     *
     * @return void
     */
    public function addAbstractMethods(): void
    {
        $countCapsuleProperties = count($this->capsuleData['properties']);

        if ($countCapsuleProperties > 0) {
            $countCapsuleMethods = count($this->capsuleData['methods']);

            $this->str
                ->concat(
                    <<<EOT
                        /**
                         * {@inheritDoc}
                         */
                        public function capsule(): {$this->class}
                        {

                    EOT
                );

            $iterateMethods = function (Closure $callback): void {
                foreach ($this->capsuleData['methods'] as $key => $method) {
                    /** @var stdClass $config */
                    $config = $method['config'];

                    /** @var stdClass $setter */
                    $setter = $config->setter;

                    /** @var string $setterMethod */
                    $setterMethod = $setter->name;

                    /** @var stdClass $format */
                    $format = $config->format;

                    /** @var string $snake */
                    $snake = $format->snake;

                    $callback($key, $config, $setterMethod, $snake);
                }
            };

            $iterateMethods(function (int $key, stdClass $config, string $setterMethod, string $snake): void {
                /** @var stdClass $variable */
                $variable = $config->variable;

                /** @var string $dataType */
                $dataType = $variable->data_type;

                $this->str
                    ->concat(
                        <<<EOT
                                /** @var $dataType|null \${$snake} */
                                \${$snake} = request('{$snake}');


                        EOT
                    );
            });

            $this->str
                ->concat(
                    <<<EOT
                            return \$this

                    EOT
                );

            $iterateMethods(function (
                int $key,
                stdClass $config,
                string $setterMethod,
                string $snake
            ) use ($countCapsuleMethods): void {
                $isFinal = $key === ($countCapsuleMethods - 1);

                $finalLine = $isFinal ? ';' : '';

                $this->str
                    ->concat(
                        <<<EOT
                                    ->{$setterMethod}(\${$snake}){$finalLine}

                        EOT
                    );
            });

            $this->str
                ->concat(
                    <<<EOT
                        }
                    EOT
                );
        } else {
            $this->str
                ->concat(
                    <<<EOT
                        /**
                         * {@inheritDoc}
                         */
                        public function capsule(): {$this->class}
                        {
                            return \$this;
                        }
                    EOT
                );
        }
    }

    /**
     * Add getters and setters to the body
     *
     * @return void
     */
    public function addMethods(): void
    {
        $countCapsuleMethods = count($this->getCapsuleMethods()) - 1;

        foreach ($this->capsuleData['methods'] as $key => $method) {
            $this->str
                ->concat($method['abstract'])->ln()->ln()
                ->concat($method['getter'])->ln()->ln();

            if ($key === $countCapsuleMethods) {
                $this->str->concat($method['setter'])->ln();
            } else {
                $this->str->concat($method['setter'])->ln()->ln();
            }
        }

        $this->interfaces = [];
    }
}
