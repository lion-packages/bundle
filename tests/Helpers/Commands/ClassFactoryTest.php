<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Exception;
use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use stdClass;
use Tests\Providers\Helpers\ClassFactoryProviderTrait;

class ClassFactoryTest extends Test
{
    use ClassFactoryProviderTrait;

    private const string FILE_NAME = 'example';
    private const string PATH_FILE = './storage-test/helpers/';

    private ClassFactory $classFactory;
    private Store $store;

    protected function setUp(): void
    {
        $this->store = new Store();

        $this->classFactory = new ClassFactory()
            ->setStore($this->store);

        $this->initReflection($this->classFactory);

        $this->createDirectory(self::PATH_FILE);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./storage-test/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setStore(): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->setStore(new Store()));
        $this->assertInstanceOf(Store::class, $this->getPrivateProperty('store'));
    }

    /**
     * @throws Exception
     */
    #[Testing]
    #[DataProvider('createProvider')]
    public function createAndClose(string $extension): void
    {
        $this->assertInstanceOf(
            ClassFactory::class,
            $this->classFactory
                ->create(self::FILE_NAME, $extension, self::PATH_FILE)
                ->close()
        );

        $this->assertFileExists(self::PATH_FILE . self::FILE_NAME . '.' . $extension);
    }

    /**
     * @throws Exception
     */
    #[Testing]
    #[DataProvider('addProvider')]
    public function add(string $extension, string $content): void
    {
        $this->assertInstanceOf(
            ClassFactory::class,
            $this->classFactory
                ->create(self::FILE_NAME, $extension, self::PATH_FILE)
                ->add($content)
                ->close()
        );

        $this->assertFileExists(self::PATH_FILE . self::FILE_NAME . '.' . $extension);

        $fileContent = $this->store->get(self::PATH_FILE . self::FILE_NAME . '.' . $extension);

        $this->assertSame($content, $fileContent);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('classFactoryProvider')]
    public function classFactory(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($namespace, $this->getPrivateProperty('namespace'));
        $this->assertSame($class, $this->getPrivateProperty('class'));
    }

    #[Testing]
    #[DataProvider('getPropertyProvider')]
    public function getProperty(
        string $propertyName,
        string $className,
        string $type,
        ?string $visibility,
        stdClass $return
    ): void {
        $property = $this->classFactory->getProperty($propertyName, $className, $type, $visibility);

        $this->assertObjectHasProperty('format', $property);
        $this->assertObjectHasProperty('getter', $property);
        $this->assertObjectHasProperty('setter', $property);
        $this->assertObjectHasProperty('variable', $property);

        $this->assertIsObject($property->format);
        $this->assertInstanceOf(stdClass::class, $property->format);
        $this->assertObjectHasProperty('camel', $property->format);
        $this->assertObjectHasProperty('snake', $property->format);
        $this->assertIsString($property->format->camel);
        $this->assertIsString($property->format->snake);

        $this->assertIsObject($property->getter);
        $this->assertInstanceOf(stdClass::class, $property->getter);
        $this->assertObjectHasProperty('name', $property->getter);
        $this->assertObjectHasProperty('method', $property->getter);
        $this->assertIsString($property->getter->name);
        $this->assertIsString($property->getter->method);

        $this->assertIsObject($property->setter);
        $this->assertInstanceOf(stdClass::class, $property->setter);
        $this->assertObjectHasProperty('name', $property->setter);
        $this->assertObjectHasProperty('method', $property->setter);
        $this->assertIsString($property->setter->name);
        $this->assertIsString($property->setter->method);

        $this->assertIsObject($property->variable);
        $this->assertInstanceOf(stdClass::class, $property->variable);
        $this->assertObjectHasProperty('annotations', $property->variable);
        $this->assertObjectHasProperty('reference', $property->variable);
        $this->assertObjectHasProperty('name', $property->variable);
        $this->assertObjectHasProperty('type', $property->variable);
        $this->assertObjectHasProperty('initialize', $property->variable);
        $this->assertIsString($property->variable->reference);

        $this->assertIsObject($property->variable->annotations);
        $this->assertInstanceOf(stdClass::class, $property->variable->annotations);
        $this->assertObjectHasProperty('class', $property->variable->annotations);

        $this->assertIsObject($property->variable->name);
        $this->assertInstanceOf(stdClass::class, $property->variable->name);
        $this->assertObjectHasProperty('camel', $property->variable->name);
        $this->assertObjectHasProperty('snake', $property->variable->name);
        $this->assertIsString($property->variable->name->camel);
        $this->assertIsString($property->variable->name->snake);

        $this->assertIsObject($property->variable->type);
        $this->assertInstanceOf(stdClass::class, $property->variable->type);
        $this->assertObjectHasProperty('camel', $property->variable->type);
        $this->assertObjectHasProperty('snake', $property->variable->type);
        $this->assertIsString($property->variable->type->camel);
        $this->assertIsString($property->variable->type->snake);

        $this->assertIsObject($property->variable->initialize);
        $this->assertInstanceOf(stdClass::class, $property->variable->initialize);
        $this->assertObjectHasProperty('camel', $property->variable->initialize);
        $this->assertObjectHasProperty('snake', $property->variable->initialize);
        $this->assertIsString($property->variable->initialize->camel);
        $this->assertIsString($property->variable->initialize->snake);

        $this->assertIsObject($property->variable->annotations->class);
        $this->assertInstanceOf(stdClass::class, $property->variable->annotations->class);
        $this->assertObjectHasProperty('data_type', $property->variable->annotations->class);
        $this->assertObjectHasProperty('data_type_with_null', $property->variable->annotations->class);
        $this->assertIsString($property->variable->annotations->class->data_type);
        $this->assertIsString($property->variable->annotations->class->data_type_with_null);

        $this->assertSame($return->format->camel, $property->format->camel);
        $this->assertSame($return->format->snake, $property->format->snake);
        $this->assertSame($return->getter->name, $property->getter->name);
        $this->assertSame($return->getter->method, $property->getter->method);
        $this->assertSame($return->setter->name, $property->setter->name);
        $this->assertSame($return->setter->method, $property->setter->method);

        $this->assertSame(
            $return->variable->annotations->class->data_type,
            $property->variable->annotations->class->data_type
        );

        $this->assertSame(
            $return->variable->annotations->class->data_type_with_null,
            $property->variable->annotations->class->data_type_with_null
        );

        $this->assertSame($return->variable->reference, $property->variable->reference);
        $this->assertSame($return->variable->name->camel, $property->variable->name->camel);
        $this->assertSame($return->variable->name->snake, $property->variable->name->snake);
        $this->assertSame($return->variable->type->camel, $property->variable->type->camel);
        $this->assertSame($return->variable->type->snake, $property->variable->type->snake);
        $this->assertSame($return->variable->initialize->camel, $property->variable->initialize->camel);
        $this->assertSame($return->variable->initialize->snake, $property->variable->initialize->snake);
    }

    #[Testing]
    #[DataProvider('classFactoryProvider')]
    public function getClass(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($namespace, $this->classFactory->getNamespace());
        $this->assertSame($class, $this->classFactory->getClass());
    }

    #[Testing]
    #[DataProvider('classFactoryProvider')]
    public function getNamespace(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($namespace, $this->classFactory->getNamespace());
    }

    #[Testing]
    #[DataProvider('classFactoryProvider')]
    public function getFolder(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($path, $this->classFactory->getFolder());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getGetterProvider')]
    public function getGetter(string $name, string $type, stdClass $return): void
    {
        $getter = $this->getPrivateMethod('getGetter', [$name, $type]);

        $this->assertIsObject($getter);
        $this->assertInstanceOf(stdClass::class, $getter);
        $this->assertSame($return->name, $getter->name);
        $this->assertSame($return->method, $getter->method);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getSetterProvider')]
    public function getSetter(string $name, string $type, string $capsule, stdClass $return): void
    {
        $setter = $this->getPrivateMethod('getSetter', [$name, $type, $capsule]);

        $this->assertIsObject($setter);
        $this->assertInstanceOf(stdClass::class, $setter);
        $this->assertSame($return->name, $setter->name);
        $this->assertSame($return->method, $setter->method);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getAbstractCapsuleMethodProvider')]
    public function getAbstractCapsuleMethod(string $column, stdClass $return): void
    {
        $method = $this->getPrivateMethod('getAbstractCapsuleMethod', [
            'column' => $column,
        ]);

        $this->assertIsObject($method);
        $this->assertInstanceOf(stdClass::class, $method);
        $this->assertSame($return->name, $method->name);
        $this->assertSame($return->method, $method->method);
    }

    /**
     * @param string $name Method name
     * @param array{
     *      type: string,
     *      annotation: string
     *  }|string $type Method type
     * @param string $params Method parameters
     * @param string $content Method content
     * @param string $visibility Scope of the method
     * @param int $lineBreak Number of line breaks after the method
     */
    #[Testing]
    #[DataProvider('getCustomMethodProvider')]
    public function getCustomMethod(
        string $name,
        array|string $type,
        string $params,
        string $content,
        string $visibility,
        int $lineBreak,
        string $return
    ): void {
        $customMethod = $this->classFactory->getCustomMethod($name, $type, $params, $content, $visibility, $lineBreak);

        $this->assertSame($return, $customMethod);
    }

    #[Testing]
    #[DataProvider('getClassFormatProvider')]
    public function getClassFormat(string $className, string $return): void
    {
        $this->assertSame($return, $this->classFactory->getClassFormat($className));
    }

    #[Testing]
    #[DataProvider('getDBTypeProvider')]
    public function getDBType(string $type, string $return): void
    {
        $this->assertSame($return, $this->classFactory->getDBType($type));
    }

    #[Testing]
    #[TestWith(['extension' => ClassFactory::PHP_EXTENSION], 'case-0')]
    #[TestWith(['extension' => ClassFactory::SH_EXTENSION], 'case-1')]
    #[TestWith(['extension' => ClassFactory::JSON_EXTENSION], 'case-2')]
    #[TestWith(['extension' => ClassFactory::LOG_EXTENSION], 'case-3')]
    public function omit(string $extension): void
    {
        $this->store->folder('app/');

        $response = $this->store->create('app/Example' . ".{$extension}", "");

        $this->assertTrue(isSuccess($response));

        $this->classFactory->classFactory('app/', self::FILE_NAME);

        $this->assertTrue($this->classFactory->omit($extension));

        $this->rmdirRecursively('app/');

        $this->assertDirectoryDoesNotExist('app/');
    }
}
