<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\Env;
use Lion\Request\Http;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use RuntimeException;
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

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    #[TestWith(['activeContext' => 'code-1', 'return' => true], 'case-0')]
    #[TestWith(['activeContext' => 'code-2', 'return' => true], 'case-1')]
    #[TestWith(['activeContext' => 'code-3', 'return' => true], 'case-2')]
    #[TestWith(['activeContext' => 'code-4', 'return' => true], 'case-3')]
    #[TestWith(['activeContext' => null, 'return' => false], 'case-4')]
    public function isSandboxActive(?string $activeContext, bool $return): void
    {
        $this->setPrivateProperty('activeContext', $activeContext);

        $this->assertSame($return, $this->env->isSandboxActive());
    }

    #[Testing]
    #[DataProvider('getProvider')]
    public function get(string $envKey, string|int|float|bool|null $envValue, string|int|float|bool|null $return): void
    {
        $this->assertSame($return, $this->env->get($envKey, $envValue));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    #[TestWith(['activeContext' => 'code-0', 'key' => 'TESTING_1', 'value' => 'testing_1'], 'case-0')]
    #[TestWith(['activeContext' => 'code-1', 'key' => 'TESTING_2', 'value' => 'testing_2'], 'case-1')]
    public function set(string $activeContext, string $key, string $value): void
    {
        $this->setPrivateProperty('activeContext', $activeContext);

        $this->env->set($key, $value);

        $this->assertSame($value, $this->env->get($key));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function enableSandbox(): void
    {
        $code = uniqid('code-', true);

        $this->env->enableSandbox($code);

        $sandboxes = $this->getPrivateProperty('sandboxes');

        $this->assertIsArray($sandboxes);
        $this->assertNotEmpty($sandboxes);
        $this->assertArrayHasKey($code, $sandboxes);
        $this->assertIsArray($sandboxes[$code]);
        $this->assertNotEmpty($sandboxes[$code]);
        $this->assertSame($code, $this->getPrivateProperty('activeContext'));
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function enableSandboxExists(): void
    {
        $contextId = uniqid('context-', true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Sandbox '{$contextId}' already exists.");
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->env->enableSandbox($contextId);

        $sandboxes = $this->getPrivateProperty('sandboxes');

        $this->assertIsArray($sandboxes);
        $this->assertNotEmpty($sandboxes);
        $this->assertArrayHasKey($contextId, $sandboxes);
        $this->assertIsArray($sandboxes[$contextId]);
        $this->assertNotEmpty($sandboxes[$contextId]);
        $this->assertSame($contextId, $this->getPrivateProperty('activeContext'));

        $this->env->enableSandbox($contextId);
    }

    /**
     * @throws ReflectionException If the property does not exist in the reflected
     * class.
     */
    #[Testing]
    public function disableSandbox(): void
    {
        $code = uniqid('code-', true);

        $this->env->enableSandbox($code);

        $sandboxes = $this->getPrivateProperty('sandboxes');

        $this->assertIsArray($sandboxes);
        $this->assertNotEmpty($sandboxes);
        $this->assertArrayHasKey($code, $sandboxes);
        $this->assertIsArray($sandboxes[$code]);
        $this->assertNotEmpty($sandboxes[$code]);
        $this->assertSame($code, $this->getPrivateProperty('activeContext'));

        $this->env->disableSandbox($code);

        $sandboxes = $this->getPrivateProperty('sandboxes');

        $this->assertIsArray($sandboxes);
        $this->assertArrayNotHasKey($code, $sandboxes);
        $this->assertNull($this->getPrivateProperty('activeContext'));
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
}
