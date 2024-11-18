<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
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
        'ext-tokenizer'
    ];

    private ComposerFactory $composerFactory;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->composerFactory = (new Container())->resolve(ComposerFactory::class);

        $this->initReflection($this->composerFactory);
    }

    private function getComposerJson(): stdClass
    {
        return json_decode((new Store())->get(self::COMPOSER_JSON));
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

        $this->assertIsArray($libraries);
        $this->assertNotEmpty($libraries);
    }
}
