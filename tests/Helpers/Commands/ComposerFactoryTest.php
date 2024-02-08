<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Helpers\Arr;
use Lion\Test\Test;
use stdClass;

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

    private function getComposerFactory(): ComposerFactory
    {
        return new ComposerFactory(json_decode(file_get_contents(self::COMPOSER_JSON)), self::EXTENSIONS);
    }

    public function testConstruct(): void
    {
        $composerFactory = $this->getComposerFactory();
        $this->initReflection($composerFactory);

        $this->assertInstanceOf(ComposerFactory::class, $composerFactory);
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
        $this->assertInstanceOf(stdClass::class, $this->getPrivateProperty('composerJson'));
        $this->assertIsArray($this->getPrivateProperty('libraries'));
    }
}
