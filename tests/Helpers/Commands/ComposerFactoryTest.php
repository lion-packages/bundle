<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\DependencyInjection\Container;
use Lion\Helpers\Arr;
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

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Arr::class, $this->getPrivateProperty('arr'));
        $this->assertIsArray($this->getPrivateProperty('libraries'));
    }
}
