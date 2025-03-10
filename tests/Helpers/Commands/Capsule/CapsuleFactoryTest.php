<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Capsule;

use Lion\Bundle\Helpers\Commands\Capsule\CapsuleFactory;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Test\Test;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use stdClass;

class CapsuleFactoryTest extends Test
{
    private CapsuleFactory $capsuleFactory;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->capsuleFactory = new CapsuleFactory()
            ->setClassFactory(new ClassFactory())
            ->setStr(new Str())
            ->setArr(new Arr());

        $this->initReflection($this->capsuleFactory);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClassFactory(): void
    {
        $this->assertInstanceOf(CapsuleFactory::class, $this->capsuleFactory->setClassFactory(new ClassFactory()));
        $this->assertInstanceOf(ClassFactory::class, $this->getPrivateProperty('classFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStr(): void
    {
        $this->assertInstanceOf(CapsuleFactory::class, $this->capsuleFactory->setStr(new Str()));
        $this->assertInstanceOf(Str::class, $this->getPrivateProperty('str'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setArr(): void
    {
        $this->assertInstanceOf(CapsuleFactory::class, $this->capsuleFactory->setArr(new Arr()));
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setClass(): void
    {
        $this->assertInstanceOf(CapsuleFactory::class, $this->capsuleFactory->setClass(self::class));
        $this->assertSame(self::class, $this->getPrivateProperty('class'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setNamespace(): void
    {
        $this->assertInstanceOf(CapsuleFactory::class, $this->capsuleFactory->setNamespace('namespace'));
        $this->assertSame('namespace', $this->getPrivateProperty('namespace'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setEntity(): void
    {
        $this->assertInstanceOf(CapsuleFactory::class, $this->capsuleFactory->setEntity('entity'));
        $this->assertSame('entity', $this->getPrivateProperty('entity'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getCapsuleMethods(): void
    {
        $this->setPrivateProperty('capsuleData', [
            'properties' => [],
            'methods' => [
                'test',
            ],
        ]);

        $capsuleMethods = $this->capsuleFactory->getCapsuleMethods();

        $method = $capsuleMethods[0];

        $this->assertSame('test', $method);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getCapsuleProperties(): void
    {
        $this->setPrivateProperty('capsuleData', [
            'properties' => [
                'test',
            ],
            'methods' => [],
        ]);

        $capsuleProperties = $this->capsuleFactory->getCapsuleProperties();

        $property = $capsuleProperties[0];

        $this->assertSame('test', $property);
    }

    #[Testing]
    public function getStr(): void
    {
        $this->capsuleFactory->setStr(new Str());

        $this->assertInstanceOf(Str::class, $this->capsuleFactory->getStr());
    }

    /**
     * @param string $class
     * @param array<int, string> $properties
     * @param int $count
     *
     * @return void
     *
     * @throws ReflectionException
     */
    #[Testing]
    #[TestWith(['class' => 'Test', 'properties' => ['id:int'], 'count' => 1])]
    #[TestWith(['class' => 'Test', 'properties' => ['id:int', 'name:string'], 'count' => 2])]
    #[TestWith(['class' => 'Test', 'properties' => ['id:int', 'name:string', 'email'], 'count' => 3])]
    public function generateGettersAndSetters(string $class, array $properties, int $count): void
    {
        $this->capsuleFactory->generateGettersAndSetters($class, $properties);

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
        $capsuleData = $this->getPrivateProperty('capsuleData');

        $this->assertArrayHasKey('properties', $capsuleData);
        $this->assertArrayHasKey('methods', $capsuleData);
        $this->assertNotEmpty($capsuleData['properties']);
        $this->assertNotEmpty($capsuleData['methods']);
        $this->assertSame($count, count($capsuleData['properties']));
        $this->assertSame($count, count($capsuleData['methods']));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addNamespace(): void
    {
        $this->setPrivateProperty('entity', 'test');

        $this->setPrivateProperty('namespace', 'Database\\Class');

        $this->capsuleFactory->addNamespace();

        /** @var Str $str */
        $str = $this->getPrivateProperty('str');

        $body = <<<EOT
        <?php

        declare(strict_types=1);

        namespace Database\Class;

        use Lion\Bundle\Interface\CapsuleInterface;
        use Lion\Bundle\Traits\CapsuleTrait;

        /**
         * Capsule for the 'test' entity
         *
         * @property string \$entity [Entity name]
        EOT;

        $this->assertSame($body, $str->get());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addingPropertyAnnotations(): void
    {
        $method = [
            'config' => (object) [
                'variable' => (object) [
                    'annotations' => (object) [
                        'class' => (object) [
                            'data_type' => '@property id $id [Property for id]',
                            'data_type_with_null' => '@property int|null $id [Property for id]',
                        ]
                    ],
                ],
            ],
        ];

        $this->setPrivateProperty('capsuleData', [
            'properties' => [],
            'methods' => [
                $method,
            ],
        ]);

        $this->capsuleFactory->addingPropertyAnnotations();

        /** @var Str $str */
        $str = $this->getPrivateProperty('str');

        $body = <<<EOT
         * @property int|null \$id [Property for id]

        EOT;

        $this->assertSame($body, $str->get());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addingClassAndImplementations(): void
    {
        $this->setPrivateProperty('class', 'Test');

        $this->setPrivateProperty('namespace', 'Database\\Class');

        $this->setPrivateProperty('entity', 'test');

        $this->capsuleFactory->addingClassAndImplementations();

        /** @var Str $str */
        $str = $this->getPrivateProperty('str');

        $body = <<<EOT
         *
         * @package Database\\Class
         */
        class Test implements CapsuleInterface
        {
            use CapsuleTrait;

            /**
             * [Entity name]
             *
             * @var string \$entity
             */
            private static string \$entity = 'test';


        EOT;

        $this->assertSame($body, $str->get());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addProperties(): void
    {
        $this->setPrivateProperty('capsuleData', [
            'properties' => [
                <<<EOT
                /**
                 * [Property for id]
                 *
                 * @var int|null \$id
                 */
                 private ?int \$id = null;
                EOT
            ],
            'methods' => [],
        ]);

        $this->capsuleFactory->addProperties();

        /** @var Str $str */
        $str = $this->getPrivateProperty('str');

        $body = <<<EOT
            /**
         * [Property for id]
         *
         * @var int|null \$id
         */
         private ?int \$id = null;


        EOT;

        $this->assertSame($body, $str->get());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addAbstractMethodsWithoutProperties(): void
    {
        $this->setPrivateProperty('class', 'Test');

        $this->setPrivateProperty('namespace', 'Database\\Class');

        $this->setPrivateProperty('entity', 'test');

        $this->setPrivateProperty('capsuleData', [
            'properties' => [],
            'methods' => [],
        ]);

        $this->capsuleFactory->addAbstractMethods();

        /** @var Str $str */
        $str = $this->getPrivateProperty('str');

        $body = <<<EOT
            /**
             * {@inheritDoc}
             */
            public function capsule(): Test
            {
                return \$this;
            }
        EOT;

        $this->assertSame($body, $str->get());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addAbstractMethods(): void
    {
        $this->setPrivateProperty('class', 'Test');

        $this->setPrivateProperty('namespace', 'Database\\Class');

        $this->setPrivateProperty('entity', 'test');

        $this->setPrivateProperty('capsuleData', [
            'properties' => [
                <<<EOT
                /**
                 * [Property for id]
                 *
                 * @var int|null \$id
                 */
                 private ?int \$id = null;
                EOT,
            ],
            'methods' => [
                [
                    'config' => (object) [
                        'format' => (object) [
                            'snake' => 'id',
                        ],
                        'setter' => (object) [
                            'name' => 'setId',
                            'method' => <<<EOT
                                /**
                                 * Setter method for 'id'
                                 *
                                 * @param int|null \$id
                                 *
                                 * @return Test
                                 */
                                public function setId(?int \$id = null): Test
                                {
                                    \$this->id = \$id;

                                    return \$this;
                                }
                            EOT,
                        ],
                        'variable' => (object) [
                            'data_type' => 'int',
                            'annotations' => (object) [
                                'class' => (object) [
                                    'data_type' => '@property id $id [Property for id]',
                                    'data_type_with_null' => '@property int|null $id [Property for id]',
                                ],
                            ],
                        ],
                    ],
                ]
            ],
        ]);

        $this->capsuleFactory->addAbstractMethods();

        /** @var Str $str */
        $str = $this->getPrivateProperty('str');

        $body = <<<EOT
            /**
             * {@inheritDoc}
             */
            public function capsule(): Test
            {
                /** @var int \$id */
                \$id = request('id');

                return \$this
                    ->setId(\$id);
            }
        EOT;

        $this->assertSame($body, $str->get());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addGettersAndSetters(): void
    {
        $this->capsuleFactory
            ->getStr()
            ->of('');

        $this->capsuleFactory->generateGettersAndSetters('Test', [
            'id:int',
        ]);

        $this->capsuleFactory->addGettersAndSetters();

        /** @var Str $str */
        $str = $this->getPrivateProperty('str');

        $body = <<<EOT
            /**
             * Getter method for 'id'
             *
             * @return int|null
             */
            public function getId(): ?int
            {
                return \$this->id;
            }

            /**
             * Setter method for 'id'
             *
             * @param int|null \$id [Description for 'id']
             *
             * @return Test
             */
            public function setId(?int \$id = null): Test
            {
                \$this->id = \$id;

                return \$this;
            }

        EOT;

        $this->assertSame($body, $str->get());
    }
}
