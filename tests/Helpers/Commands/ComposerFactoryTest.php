<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\DependencyInjection\Container;
use Lion\Files\Store;
use Lion\Test\Test;

class ComposerFactoryTest extends Test
{
    const COMPOSER_JSON = './composer.json';
    const EXTENSIONS = [
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

    protected function setUp(): void
    {
        $this->composerFactory = (new Container())->injectDependencies(new ComposerFactory());

        $this->initReflection($this->composerFactory);
    }

    private function getComposerJson(): object
    {
        return json_decode((new Store())->get(self::COMPOSER_JSON));
    }

    public function testLibraries(): void
    {
        $this->composerFactory->libraries($this->getComposerJson(), self::EXTENSIONS);

        $libraries = $this->getPrivateProperty('libraries');

        $this->assertIsArray($libraries);
        $this->assertNotEmpty($libraries);
    }

    public function testLibrariesDev(): void
    {
        $this->composerFactory->librariesDev($this->getComposerJson(), self::EXTENSIONS);

        $libraries = $this->getPrivateProperty('libraries');

        $this->assertIsArray($libraries);
        $this->assertNotEmpty($libraries);
    }

    public function testGetLibraries(): void
    {
        $libraries = $this->composerFactory
            ->libraries($this->getComposerJson(), self::EXTENSIONS)
            ->librariesDev($this->getComposerJson(), self::EXTENSIONS)
            ->getLibraries();

        $this->assertIsArray($libraries);
        $this->assertNotEmpty($libraries);
    }
}
