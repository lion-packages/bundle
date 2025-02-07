<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use stdClass;

class ComposerFactoryTest extends Test
{
    private const string COMPOSER_JSON = './composer.json';
    private const array EXTENSIONS = [
        'php',
        'ext-ctype',
        'ext-filter',
        'ext-hash',
        'ext-mbstring',
        'ext-openssl',
        'ext-session',
        'ext-tokenizer',
    ];

    private ComposerFactory $composerFactory;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var ComposerFactory $composerFactory */
        $composerFactory = new Container()->resolve(ComposerFactory::class);

        $this->composerFactory = $composerFactory;

        $this->initReflection($this->composerFactory);
    }

    private function getComposerJson(): stdClass
    {
        /** @var string $file */
        $file = new Store()->get(self::COMPOSER_JSON);

        /** @var stdClass $json */
        $json = json_decode($file);

        return $json;
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setArr(): void
    {
        $this->assertInstanceOf(ComposerFactory::class, $this->composerFactory->setArr(new Arr()));
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getLibrariesWithCommand(): void
    {
        $json = $this->getPrivateMethod('getLibrariesWithCommand', [
            'library' => 'lion/test',
        ]);

        $this->assertInstanceOf(stdClass::class, $json);
        $this->assertObjectHasProperty('name', $json);
        $this->assertObjectHasProperty('description', $json);
        $this->assertIsString($json->name);
        $this->assertIsString($json->description);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function validateLibrary(): void
    {
        $json = $this->getPrivateMethod('getLibrariesWithCommand', [
            'library' => 'lion/test',
        ]);

        $this->assertInstanceOf(stdClass::class, $json);
        $this->assertObjectHasProperty('name', $json);
        $this->assertObjectHasProperty('description', $json);
        $this->assertIsString($json->name);
        $this->assertIsString($json->description);

        $validateLibrary = $this->getPrivateMethod('validateLibrary', [
            'json' => $json,
        ]);

        $this->assertTrue($validateLibrary);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function validateLibraryWithoutProperties(): void
    {
        $validateLibrary = $this->getPrivateMethod('validateLibrary', [
            'json' => (object) [],
        ]);

        $this->assertFalse($validateLibrary);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function validateLibraryWithVersionIsNotArray(): void
    {
        $validateLibrary = $this->getPrivateMethod('validateLibrary', [
            'json' => (object) [
                'description' => 'test',
                'versions' => 'test',
                'licenses' => 'test',
            ],
        ]);

        $this->assertFalse($validateLibrary);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function validateLibraryWithInvalidIndexes(): void
    {
        $validateLibrary = $this->getPrivateMethod('validateLibrary', [
            'json' => (object) [
                'description' => 'test',
                'versions' => [],
                'licenses' => [],
            ],
        ]);

        $this->assertFalse($validateLibrary);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function libraries(): void
    {
        $this->composerFactory->libraries($this->getComposerJson(), self::EXTENSIONS);

        $libraries = $this->getPrivateProperty('libraries');

        $this->assertIsArray($libraries);
        $this->assertNotEmpty($libraries);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function librariesDev(): void
    {
        $this->composerFactory->librariesDev($this->getComposerJson(), self::EXTENSIONS);

        $libraries = $this->getPrivateProperty('libraries');

        $this->assertIsArray($libraries);
        $this->assertNotEmpty($libraries);
    }

    #[Testing]
    public function getLibraries(): void
    {
        $libraries = $this->composerFactory
            ->libraries($this->getComposerJson(), self::EXTENSIONS)
            ->librariesDev($this->getComposerJson(), self::EXTENSIONS)
            ->getLibraries();

        $this->assertNotEmpty($libraries);
    }
}
