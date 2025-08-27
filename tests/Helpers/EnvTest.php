<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\Env;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Tests\Providers\Helpers\EnvProviderTrait;

class EnvTest extends Test
{
    use EnvProviderTrait;

    private Env $env;

    protected function setUp(): void
    {
        $this->env = new Env();

        $this->initReflection($this->env);
    }

    #[Testing]
    #[DataProvider('getProvider')]
    public function get(string $envKey, mixed $envValue, mixed $return): void
    {
        $this->assertSame($return, $this->env->get($envKey, $envValue));
    }

    #[Testing]
    #[DataProvider('getKeyProvider')]
    public function getKey(string $value, string|int|false $return): void
    {
        $this->assertSame($return, $this->env->getKey($value));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[RunInSeparateProcess]
    #[DataProvider('getOptionProvider')]
    public function getOption(
        string $envKey,
        string|int|float|bool|null $envValue,
        string|int|float|bool|null $return
    ): void {
        $_ENV['key'] = '"value"';

        $_ENV['key2'] = '\'value\'';

        $this->assertSame($return, $this->getPrivateMethod('getOption', [
            'key' => $envKey,
            'default' => $envValue,
        ]));

        unset($_ENV['key'], $_ENV['key2']);

        $this->assertArrayNotHasKey('key', $_ENV);
        $this->assertArrayNotHasKey('key2', $_ENV);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[RunInSeparateProcess]
    public function getOptionEnvDoesNotExist(): void
    {
        unset($_ENV);

        $this->assertNull($this->getPrivateMethod('getOption', [
            'key' => 'key',
            'default' => 'value',
        ]));
    }
}
