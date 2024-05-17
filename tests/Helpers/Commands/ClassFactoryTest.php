<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Providers\Helpers\ClassFactoryProviderTrait;

class ClassFactoryTest extends Test
{
    use ClassFactoryProviderTrait;

    const FILE_NAME = 'example';
    const PATH_FILE = './storage-test/helpers/';

    private ClassFactory $classFactory;

    protected function setUp(): void
    {
        $this->classFactory = (new Container())
            ->injectDependencies(new ClassFactory());

        $this->initReflection($this->classFactory);

        $this->createDirectory(self::PATH_FILE);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./storage-test/');
    }

    #[DataProvider('createProvider')]
    public function testCreateAndClose(string $extension): void
    {
        $this->assertInstanceOf(
            ClassFactory::class,
            $this->classFactory
                ->create(self::FILE_NAME, $extension, self::PATH_FILE)
                ->close()
        );

        $this->assertFileExists(self::PATH_FILE . self::FILE_NAME . '.' . $extension);
    }

    #[DataProvider('addProvider')]
    public function testAdd(string $extension, string $content): void
    {
        $this->assertInstanceOf(
            ClassFactory::class,
            $this->classFactory
                ->create(self::FILE_NAME, $extension, self::PATH_FILE)
                ->add($content)
                ->close()
        );

        $this->assertFileExists(self::PATH_FILE . self::FILE_NAME . '.' . $extension);

        $fileContent = (new Store())->get(self::PATH_FILE . self::FILE_NAME . '.' . $extension);

        $this->assertSame($content, $fileContent);
    }

    #[DataProvider('classFactoryProvider')]
    public function testClassFactory(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($namespace, $this->getPrivateProperty('namespace'));
        $this->assertSame($class, $this->getPrivateProperty('class'));
    }

    #[DataProvider('getPropertyProvider')]
    public function testGetProperty(
        string $propertyName,
        string $className,
        string $type,
        ?string $visibility,
        object $return
    ): void {
        $property = $this->classFactory->getProperty($propertyName, $className, $type, $visibility);

        $this->assertIsObject($property);
        $this->assertSame($return->format->camel, $property->format->camel);
        $this->assertSame($return->format->snake, $property->format->snake);
        $this->assertSame($return->getter->name, $property->getter->name);
        $this->assertSame($return->getter->method, $property->getter->method);
        $this->assertSame($return->setter->name, $property->setter->name);
        $this->assertSame($return->setter->method, $property->setter->method);
        $this->assertSame($return->variable->annotations->class, $property->variable->annotations->class);
        $this->assertSame($return->variable->reference, $property->variable->reference);
        $this->assertSame($return->variable->name->camel, $property->variable->name->camel);
        $this->assertSame($return->variable->name->snake, $property->variable->name->snake);
        $this->assertSame($return->variable->type->camel, $property->variable->type->camel);
        $this->assertSame($return->variable->type->snake, $property->variable->type->snake);
        $this->assertSame($return->variable->initialize->camel, $property->variable->initialize->camel);
        $this->assertSame($return->variable->initialize->snake, $property->variable->initialize->snake);
    }

    #[DataProvider('classFactoryProvider')]
    public function testGetClass(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($namespace, $this->classFactory->getNamespace());
        $this->assertSame($class, $this->classFactory->getClass());
    }

    #[DataProvider('classFactoryProvider')]
    public function testGetNamespace(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($namespace, $this->classFactory->getNamespace());
    }

    #[DataProvider('classFactoryProvider')]
    public function testGetFolder(string $path, string $fileName, string $namespace, string $class): void
    {
        $this->assertInstanceOf(ClassFactory::class, $this->classFactory->classFactory($path, $fileName));
        $this->assertSame($path, $this->classFactory->getFolder());
    }

    #[DataProvider('getGetterProvider')]
    public function testGetGetter(string $name, string $type, object $return): void
    {
        $getter = $this->getPrivateMethod('getGetter', [$name, $type]);

        $this->assertIsObject($getter);
        $this->assertSame($return->name, $getter->name);
        $this->assertSame($return->method, $getter->method);
    }

    #[DataProvider('getSetterProvider')]
    public function testGetSetter(string $name, string $type, string $capsule, object $return): void
    {
        $setter = $this->getPrivateMethod('getSetter', [$name, $type, $capsule]);

        $this->assertIsObject($setter);
        $this->assertSame($return->name, $setter->name);
        $this->assertSame($return->method, $setter->method);
    }

    #[DataProvider('getCustomMethodProvider')]
    public function testGetCustomMethod(
        string $name,
        string $type,
        string $params,
        string $content,
        string $visibility,
        int $lineBreak,
        string $return
    ): void {
        $customMethod = $this->classFactory->getCustomMethod($name, $type, $params, $content, $visibility, $lineBreak);

        $this->assertSame($return, $customMethod);
    }

    #[DataProvider('getClassFormatProvider')]
    public function testGetClassFormat(string $className, string $return): void
    {
        $this->assertSame($return, $this->classFactory->getClassFormat($className));
    }

    #[DataProvider('getDBTypeProvider')]
    public function testGetDBType(string $type, string $return): void
    {
        $this->assertSame($return, $this->classFactory->getDBType($type));
    }
}
