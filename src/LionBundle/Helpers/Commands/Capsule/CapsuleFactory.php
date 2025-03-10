<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Capsule;

use Closure;
use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use stdClass;

/**
 * Manages the configuration and structure of a generated capsule class
 *
 * @package Lion\Bundle\Helpers\Commands\Capsule
 */
class CapsuleFactory
{
    /**
     * [Fabricates the data provided to manipulate information (folder, class,
     * namespace)]
     *
     * @var ClassFactory $classFactory
     */
    private ClassFactory $classFactory;

    /**
     * [Modify and construct strings with different formats]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [Modify and build arrays with different indexes or values]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * @var array{
     *     properties: array<int, string>,
     *     methods: array<int, array{
     *          getter: string,
     *          setter: string,
     *          config: stdClass
     *     }>
     * } $capsuleData
     */
    private array $capsuleData = [
        'properties' => [],
        'methods' => [],
    ];

    /**
     * [Class name]
     *
     * @var string $class
     */
    private string $class;

    /**
     * [Class namespace]
     *
     * @var string $namespace
     */
    private string $namespace;

    /**
     * [Entity name]
     *
     * @var string $entity
     */
    private string $entity;

    #[Inject]
    public function setClassFactory(ClassFactory $classFactory): CapsuleFactory
    {
        $this->classFactory = $classFactory;

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
     * Add the name of the class
     *
     * @param string $class [Class name]
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
     * @param string $namespace [Class namespace]
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
     * @param string $entity [Entity name]
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
     * Iterates the list of data to generate the Getter and Setter methods
     *
     * @param string $class [Class name]
     * @param array<int, string> $properties [List of class properties]
     *
     * @return void
     */
    public function generateGettersAndSetters(string $class, array $properties): void
    {
        foreach ($properties as $property) {
            $split = explode(':', $property);

            $data = $this->classFactory->getProperty(
                $split[0],
                $class,
                $split[1] ?? 'string',
                ClassFactory::PRIVATE_PROPERTY
            );

            /** @var stdClass $variable */
            $variable = $data->variable;

            /** @var stdClass $type */
            $type = $variable->type;

            /** @var string $snakeCase */
            $snakeCase = $type->snake;

            /** @var stdClass $getter */
            $getter = $data->getter;

            /** @var stdClass $setter */
            $setter = $data->setter;

            /** @var string $getterMethod */
            $getterMethod = $getter->method;

            /** @var string $setterMethod */
            $setterMethod = $setter->method;

            $this->capsuleData['properties'][] = $snakeCase;

            $this->capsuleData['methods'][] = [
                'getter' => $getterMethod,
                'setter' => $setterMethod,
                'config' => $data,
            ];
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
                <<<EOT
                <?php

                declare(strict_types=1);

                namespace {$this->namespace};

                use Lion\Bundle\Interface\CapsuleInterface;
                use Lion\Bundle\Traits\CapsuleTrait;

                /**
                 * Capsule for the '{$this->entity}' entity
                 *
                 * @property string \$entity [Entity name]
                EOT
            );
    }

    /**
     * Add the property annotations
     *
     * @return void
     */
    public function addingPropertyAnnotations(): void
    {
        foreach ($this->capsuleData['methods'] as $method) {
            /** @var stdClass $config */
            $config = $method['config'];

            /** @var stdClass $variable */
            $variable = $config->variable;

            /** @var stdClass $annotations */
            $annotations = $variable->annotations;

            /** @var stdClass $classAnnotations */
            $classAnnotations = $annotations->class;

            /** @var string $dataType */
            $dataType = $classAnnotations->data_type_with_null;

            $this->str->concat(" * {$dataType}\n");
        }
    }

    /**
     * Add the class and its implementations to the body
     *
     * @return void
     */
    public function addingClassAndImplementations(): void
    {
        $this->str
            ->concat(
                <<<EOT
                 *
                 * @package {$this->namespace}
                 */
                class {$this->class} implements CapsuleInterface
                {
                    use CapsuleTrait;

                    /**
                     * [Entity name]
                     *
                     * @var string \$entity
                     */
                    private static string \$entity = '{$this->entity}';


                EOT
            );
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
                                /** @var $dataType \${$snake} */
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
    public function addGettersAndSetters(): void
    {
        $countCapsuleMethods = count($this->getCapsuleMethods()) - 1;

        foreach ($this->capsuleData['methods'] as $key => $method) {
            $this->str->concat($method['getter'])->ln()->ln();

            if ($key === $countCapsuleMethods) {
                $this->str->concat($method['setter'])->ln();
            } else {
                $this->str->concat($method['setter'])->ln()->ln();
            }
        }
    }
}
