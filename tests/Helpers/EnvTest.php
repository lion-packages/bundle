<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\Env;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Providers\EnviromentProviderTrait;
use Tests\Providers\Helpers\EnvProviderTrait;
use Tests\Providers\Helpers\HelpersProviderTrait;

class EnvTest extends Test
{
    use EnviromentProviderTrait;
    use EnvProviderTrait;
    use HelpersProviderTrait;

    private Env $env;

    protected function setUp(): void
    {
        $this->loadEnviroment();

        $this->env = new Env();

        $this->initReflection($this->env);
    }

    #[DataProvider('envProvider')]
    public function testGet(string $envKey, mixed $envValue, mixed $return): void
    {
        $this->assertSame($return, $this->env->get($envKey, $envValue));
    }

    #[DataProvider('getKeyProvider')]
    public function testGetKey(string $value, string|int|false $return): void
    {
        $this->assertSame($return, $this->env->getKey($value));
    }

    #[DataProvider('envProvider')]
    public function testGetOption(string $envKey, mixed $envValue, mixed $return): void
    {
        $this->assertSame($return, $this->getPrivateMethod('getOption', [$envKey, $envValue]));
    }
}
